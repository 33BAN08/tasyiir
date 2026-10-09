# Builds the portable Windows package of the TASYIIR local edition:
#
#   release\tasyiir-local-vX.Y.Z.zip
#     php\          portable PHP runtime (+ generated php.ini)
#     app\          the application, production dependencies only
#     install.bat   first install on a client PC
#     start.bat / stop.bat / update.bat
#     دليل-التثبيت.txt / LISEZ-MOI.txt
#
# Run from anywhere:  powershell -ExecutionPolicy Bypass -File scripts\build-local-release.ps1
# Options:            -Version 1.2.0   -SkipAssets   -SkipComposer

[CmdletBinding()]
param(
    [string]$Version = '',
    [switch]$SkipAssets,
    [switch]$SkipComposer
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$runtime = Join-Path $PSScriptRoot 'php-runtime'
$releaseDir = Join-Path $root 'release'

function Step($text) { Write-Host "==> $text" -ForegroundColor Cyan }
function Fail($text) { Write-Host "[X] $text" -ForegroundColor Red; exit 1 }

# --- version ---------------------------------------------------------------
if (-not $Version) {
    $line = Select-String -Path (Join-Path $root 'config\tasyiir.php') -Pattern "'version' => env\('TASYIIR_VERSION', '([^']+)'\)"
    $Version = if ($line) { $line.Matches[0].Groups[1].Value } else { '1.0.0' }
}
Step "Building TASYIIR local edition v$Version"

# --- portable PHP ----------------------------------------------------------
if (-not (Test-Path (Join-Path $runtime 'php.exe'))) {
    Write-Host ''
    Write-Host 'The portable PHP runtime is missing.' -ForegroundColor Yellow
    Write-Host "Expected: $runtime\php.exe"
    Write-Host ''
    Write-Host '  1. Open https://windows.php.net/download/'
    Write-Host '  2. Download PHP 8.3 (or newer) "VS16 x64 Non Thread Safe" ZIP'
    Write-Host '     e.g. php-8.3.x-nts-Win32-vs16-x64.zip'
    Write-Host "  3. Extract its contents into: $runtime"
    Write-Host '     (php.exe must sit directly in that folder, with ext\ beside it)'
    Write-Host ''
    Fail 'Nothing was built.'
}

# --- assets ----------------------------------------------------------------
Push-Location $root
try {
    if (-not $SkipAssets) {
        Step 'Building front-end assets (npm run build)'
        & npm run build
        if ($LASTEXITCODE -ne 0) { Fail 'npm run build failed.' }
    }
} finally { Pop-Location }

# --- staging ---------------------------------------------------------------
$staging = Join-Path $env:TEMP ("tasyiir-build-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
$stagingApp = Join-Path $staging 'app'
New-Item -ItemType Directory -Force -Path $stagingApp | Out-Null

Step 'Copying application files'
$excludeDirs = @('.git', '.claude', '.github', 'node_modules', 'tests', 'release', 'marketing',
                 'tools', 'scripts\php-runtime', 'scripts\local',
                 'storage\logs', 'storage\backups',
                 'storage\framework\sessions', 'storage\framework\views', 'storage\framework\cache')
$excludeFiles = @('.env', '.env.example', 'database.sqlite', 'NOTES.md', '*.log', '*.zip',
                  'phpunit.xml', 'tasyiir-build-*.zip',
                  'config.php', 'routes-v7.php', 'events.php', 'packages.php', 'services.php')

$robocopyArgs = @($root, $stagingApp, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP')
$robocopyArgs += '/XD'; $robocopyArgs += ($excludeDirs | ForEach-Object { Join-Path $root $_ })
$robocopyArgs += '/XF'; $robocopyArgs += $excludeFiles
& robocopy @robocopyArgs | Out-Null
if ($LASTEXITCODE -ge 8) { Fail 'Copying the application failed.' }

# Keep the empty runtime folders Laravel expects (robocopy skipped their contents).
foreach ($dir in @('storage\logs', 'storage\backups', 'storage\framework\sessions',
                   'storage\framework\views', 'storage\framework\cache\data')) {
    New-Item -ItemType Directory -Force -Path (Join-Path $stagingApp $dir) | Out-Null
    Set-Content -Path (Join-Path $stagingApp "$dir\.gitignore") -Value "*`n!.gitignore" -Encoding ascii
}
Remove-Item (Join-Path $stagingApp 'database\database.sqlite') -ErrorAction SilentlyContinue
# Belt and braces: compiled caches must never travel with the package — a
# bootstrap/cache/config.php would carry the developer's .env into every
# client install and break key:generate on first run.
Get-ChildItem (Join-Path $stagingApp 'bootstrap\cache') -Filter '*.php' -ErrorAction SilentlyContinue | Remove-Item -Force
New-Item -ItemType Directory -Force -Path (Join-Path $stagingApp 'bootstrap\cache') | Out-Null

if (-not (Test-Path (Join-Path $stagingApp 'public\build\manifest.json'))) {
    Fail 'public\build\manifest.json is missing — run npm run build (do not pass -SkipAssets on a clean checkout).'
}

# --- production dependencies ----------------------------------------------
if (-not $SkipComposer) {
    Step 'Installing production dependencies (composer install --no-dev)'
    Push-Location $stagingApp
    try {
        & composer install --no-dev --optimize-autoloader --no-interaction --no-progress
        if ($LASTEXITCODE -ne 0) { Fail 'composer install failed.' }
    } finally { Pop-Location }
}

# --- portable PHP + php.ini ------------------------------------------------
Step 'Adding the PHP runtime'
$stagingPhp = Join-Path $staging 'php'
& robocopy $runtime $stagingPhp '/E' '/NFL' '/NDL' '/NJH' '/NJS' '/NP' | Out-Null
if ($LASTEXITCODE -ge 8) { Fail 'Copying the PHP runtime failed.' }

$ini = @'
; php.ini generated by scripts\build-local-release.ps1 for the TASYIIR local edition
extension_dir = "ext"

extension=pdo_sqlite
extension=sqlite3
extension=gd
extension=zip
extension=mbstring
extension=fileinfo
extension=openssl
extension=sodium
extension=curl
; intl is optional: PHP skips it with a warning if the DLL is absent
extension=intl

; GPCS, not the compiled-in EGPCS: with $_ENV populated, `artisan serve`
; filters the child process environment and drops SYSTEMROOT, and the Windows
; built-in server then fails to bind with "Failed to listen (reason: ?)".
variables_order = "GPCS"

memory_limit = 512M
max_execution_time = 120
upload_max_filesize = 64M
post_max_size = 64M

date.timezone = Africa/Casablanca
display_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
opcache.enable = 1
opcache.enable_cli = 0
'@
Set-Content -Path (Join-Path $stagingPhp 'php.ini') -Value $ini -Encoding ascii

if (-not (Test-Path (Join-Path $stagingPhp 'ext\php_sodium.dll'))) {
    Write-Host '[!] php_sodium.dll not found in the runtime: licence activation will not work.' -ForegroundColor Yellow
}

# --- launcher scripts + client documentation -------------------------------
Step 'Adding launchers and documentation'
foreach ($bat in @('install.bat', 'start.bat', 'stop.bat', 'update.bat')) {
    Copy-Item (Join-Path $PSScriptRoot "local\$bat") (Join-Path $staging $bat) -Force
}
# Globbed rather than listed by name: the Arabic file name must survive
# whatever encoding this script itself is read with.
Get-ChildItem (Join-Path $PSScriptRoot 'local') -Filter '*.txt' | ForEach-Object {
    Copy-Item $_.FullName (Join-Path $staging $_.Name) -Force
}

# --- zip -------------------------------------------------------------------
New-Item -ItemType Directory -Force -Path $releaseDir | Out-Null
$zip = Join-Path $releaseDir "tasyiir-local-v$Version.zip"
Remove-Item $zip -ErrorAction SilentlyContinue

Step "Packing $zip"
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zip -CompressionLevel Optimal

Remove-Item $staging -Recurse -Force -ErrorAction SilentlyContinue

$size = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host ''
Write-Host "Done: $zip ($size MB)" -ForegroundColor Green
Write-Host 'Unzip it on the client PC and run install.bat.'
