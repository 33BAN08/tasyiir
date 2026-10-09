# Builds the hosted (SaaS) edition as a deployable archive:
#
#   release\TASYIIR-main.zip
#     the application at the root of the zip (artisan, public\, vendor\, ...)
#     production dependencies only, front-end assets already built
#     .env.example carrying the SaaS defaults (TASYIIR_MODE=saas)
#
# Upload it to the server, unzip, copy .env.example to .env, set APP_KEY,
# APP_URL and the database, then run migrate --force and optimize. There is no
# PHP runtime and no .bat launcher in here: the hosting platform provides those.
#
# Run from anywhere:  powershell -ExecutionPolicy Bypass -File scripts\build-saas-release.ps1
# Options:            -Name TASYIIR-main   -SkipAssets   -SkipComposer

[CmdletBinding()]
param(
    [string]$Name = 'TASYIIR-main',
    [switch]$SkipAssets,
    [switch]$SkipComposer
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$releaseDir = Join-Path $root 'release'

function Step($text) { Write-Host "==> $text" -ForegroundColor Cyan }
function Fail($text) { Write-Host "[X] $text" -ForegroundColor Red; exit 1 }

Step 'Building the TASYIIR hosted edition'

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
$staging = Join-Path $env:TEMP ("tasyiir-saas-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
New-Item -ItemType Directory -Force -Path $staging | Out-Null

Step 'Copying application files'
# tools\ holds the licence private key and never ships; scripts\local and the
# PHP runtime belong to the Windows package only.
$excludeDirs = @('.git', '.claude', '.github', 'node_modules', 'tests', 'release', 'marketing',
                 'tools', 'scripts\php-runtime', 'scripts\local',
                 'storage\logs', 'storage\backups',
                 'storage\framework\sessions', 'storage\framework\views', 'storage\framework\cache')
# Same caution as the local build: robocopy /XF matches a bare filename in every
# folder it walks, so never name a file that also exists inside vendor\.
$excludeFiles = @('.env', 'database.sqlite', 'NOTES.md', '*.log', '*.zip', 'phpunit.xml')

$robocopyArgs = @($root, $staging, '/E', '/NFL', '/NDL', '/NJH', '/NJS', '/NP')
$robocopyArgs += '/XD'; $robocopyArgs += ($excludeDirs | ForEach-Object { Join-Path $root $_ })
$robocopyArgs += '/XF'; $robocopyArgs += $excludeFiles
& robocopy @robocopyArgs | Out-Null
if ($LASTEXITCODE -ge 8) { Fail 'Copying the application failed.' }

foreach ($dir in @('storage\logs', 'storage\framework\sessions',
                   'storage\framework\views', 'storage\framework\cache\data')) {
    New-Item -ItemType Directory -Force -Path (Join-Path $staging $dir) | Out-Null
    Set-Content -Path (Join-Path $staging "$dir\.gitignore") -Value "*`n!.gitignore" -Encoding ascii
}
Remove-Item (Join-Path $staging 'database\database.sqlite') -ErrorAction SilentlyContinue
Remove-Item (Join-Path $staging '.env.local.example') -ErrorAction SilentlyContinue

if (-not (Test-Path (Join-Path $staging 'public\build\manifest.json'))) {
    Fail 'public\build\manifest.json is missing - run npm run build.'
}
if (-not (Select-String -Path (Join-Path $staging '.env.example') -Pattern '^TASYIIR_MODE=saas' -Quiet)) {
    Fail '.env.example does not set TASYIIR_MODE=saas.'
}

# --- production dependencies ----------------------------------------------
if (-not $SkipComposer) {
    Step 'Installing production dependencies (composer install --no-dev)'
    Push-Location $staging
    try {
        & composer install --no-dev --optimize-autoloader --no-interaction --no-progress
        if ($LASTEXITCODE -ne 0) { Fail 'composer install failed.' }
    } finally { Pop-Location }
}

# After composer: its post-autoload-dump rewrites bootstrap\cache. A compiled
# config.php would carry this machine's .env to the server.
Get-ChildItem (Join-Path $staging 'bootstrap\cache') -Filter '*.php' -ErrorAction SilentlyContinue | Remove-Item -Force
New-Item -ItemType Directory -Force -Path (Join-Path $staging 'bootstrap\cache') | Out-Null

# --- zip -------------------------------------------------------------------
New-Item -ItemType Directory -Force -Path $releaseDir | Out-Null
$zip = Join-Path $releaseDir "$Name.zip"
Remove-Item $zip -ErrorAction SilentlyContinue

Step "Packing $zip"
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zip -CompressionLevel Optimal

Remove-Item $staging -Recurse -Force -ErrorAction SilentlyContinue

$size = [math]::Round((Get-Item $zip).Length / 1MB, 1)
Write-Host ''
Write-Host "Done: $zip ($size MB)" -ForegroundColor Green
Write-Host 'Unzip on the server, copy .env.example to .env, then: key:generate, migrate --force, optimize.'
