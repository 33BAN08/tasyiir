@echo off
setlocal enabledelayedexpansion
cd /d "%~dp0"

set "PHP=%~dp0php\php.exe"
set "APPDIR=%~dp0app"

if not exist "%APPDIR%\.env" (
    echo TASYIIR n'est pas encore installe. Lancez install.bat.
    pause
    exit /b 1
)

rem --- host/port: read from .env, default to this PC only ---
set "HOST=127.0.0.1"
set "PORT=8000"
for /f "usebackq tokens=1,* delims==" %%A in ("%APPDIR%\.env") do (
    if /i "%%A"=="TASYIIR_HOST" set "HOST=%%B"
    if /i "%%A"=="TASYIIR_PORT" set "PORT=%%B"
)
set "HOST=%HOST: =%"
set "PORT=%PORT: =%"

rem --- already running? just open the browser ---
call :isup
if "%UP%"=="1" (
    echo TASYIIR est deja demarre.
    start "" "http://localhost:%PORT%"
    exit /b 0
)

echo Sauvegarde de la base de donnees...
pushd "%APPDIR%"
"%PHP%" artisan tasyiir:backup --no-ansi

echo Demarrage de TASYIIR...
rem Started from inside the application folder: artisan resolves its paths
rem from the working directory and fails to bind when launched elsewhere.
start "TASYIIR" /min "%PHP%" artisan serve --host=%HOST% --port=%PORT%
popd

rem --- wait until the server answers (max ~30s) ---
set /a tries=0
:wait
set /a tries+=1
ping -n 2 127.0.0.1 >nul
call :isup
if "%UP%"=="1" goto ready
if %tries% GEQ 30 (
    echo [X] Le serveur n'a pas demarre. Consultez app\storage\logs\laravel.log
    pause
    exit /b 1
)
goto wait

:ready
start "" "http://localhost:%PORT%"

if /i "%HOST%"=="0.0.0.0" (
    echo.
    echo Les autres ordinateurs du centre peuvent ouvrir :
    for /f "tokens=2 delims=:" %%I in ('ipconfig ^| findstr /c:"IPv4"') do (
        for /f "tokens=1" %%J in ("%%I") do echo     http://%%J:%PORT%
    )
    echo.
    echo Si cela ne fonctionne pas, autorisez le port %PORT% dans le pare-feu Windows :
    echo   netsh advfirewall firewall add rule name="TASYIIR" dir=in action=allow protocol=TCP localport=%PORT%
    echo.
    ping -n 9 127.0.0.1 >nul
)
exit /b 0

rem --- UP=1 when something is listening on the port -------------------------
:isup
set "UP=0"
for /f %%R in ('powershell -NoProfile -Command "try { $c = New-Object Net.Sockets.TcpClient; $c.Connect(\"127.0.0.1\", %PORT%); $c.Close(); 1 } catch { 0 }"') do set "UP=%%R"
exit /b 0
