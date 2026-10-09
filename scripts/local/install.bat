@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

echo ============================================
echo    TASYIIR - installation
echo ============================================
echo.

set "PHP=%~dp0php\php.exe"
if not exist "%PHP%" (
    echo [X] PHP introuvable : %PHP%
    echo     The php\ folder is missing from this package.
    pause
    exit /b 1
)

cd /d "%~dp0app"

if not exist ".env" (
    echo [1/6] .env
    copy /Y ".env.local.example" ".env" >nul
) else (
    echo [1/6] .env deja present - conserve
)

rem A stale compiled config makes key:generate search for the wrong key.
"%PHP%" artisan config:clear >nul 2>&1

findstr /C:"APP_KEY=base64:" ".env" >nul
if errorlevel 1 (
    echo [2/6] Cle d'application
    "%PHP%" artisan key:generate --force --no-ansi
    findstr /C:"APP_KEY=base64:" ".env" >nul
    if errorlevel 1 (
        echo [X] La cle d'application n'a pas pu etre generee. Installation interrompue.
        pause
        exit /b 1
    )
) else (
    echo [2/6] Cle d'application deja generee
)

if not exist "database\database.sqlite" (
    echo [3/6] Base de donnees
    type nul > "database\database.sqlite"
) else (
    echo [3/6] Base de donnees deja presente - conservee
)

echo [4/6] Migrations
"%PHP%" artisan migrate --force --no-ansi
if errorlevel 1 (
    echo [X] Migrations failed. Nothing was installed.
    pause
    exit /b 1
)

echo [5/6] Optimisation
"%PHP%" artisan optimize --no-ansi >nul

echo [6/6] Raccourcis
powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ws = New-Object -ComObject WScript.Shell;" ^
  "$target = Join-Path '%~dp0' 'start.bat';" ^
  "$icon = Join-Path '%~dp0' 'app\public\favicon.ico';" ^
  "foreach ($dir in @([Environment]::GetFolderPath('Desktop'), [Environment]::GetFolderPath('Startup'))) {" ^
  "  $s = $ws.CreateShortcut((Join-Path $dir 'TASYIIR.lnk'));" ^
  "  $s.TargetPath = $target; $s.WorkingDirectory = '%~dp0'; $s.IconLocation = $icon; $s.WindowStyle = 7; $s.Save() }"

echo.
echo ============================================
echo    Installation terminee
echo ============================================
echo.
echo Le navigateur va s'ouvrir sur la page de creation du centre.
echo.
ping -n 4 127.0.0.1 >nul
call "%~dp0start.bat"
