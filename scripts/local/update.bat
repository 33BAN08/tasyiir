@echo off
setlocal
cd /d "%~dp0"

rem Updates the application files from a newer package while keeping every
rem piece of client data. Run it from the NEW package folder, passing the
rem path of the installed one:
rem
rem   update.bat "C:\TASYIIR"

set "TARGET=%~1"
if "%TARGET%"=="" (
    echo Usage: update.bat "chemin\vers\installation"
    echo Exemple : update.bat "C:\TASYIIR"
    pause
    exit /b 1
)
if not exist "%TARGET%\app\artisan" (
    echo [X] "%TARGET%" ne contient pas une installation TASYIIR.
    pause
    exit /b 1
)

set "PHP=%TARGET%\php\php.exe"

echo [1/5] Arret du serveur
call "%TARGET%\stop.bat"

echo [2/5] Sauvegarde de la base de donnees
pushd "%TARGET%\app"
"%PHP%" artisan tasyiir:backup --force --no-ansi
popd

echo [3/5] Remplacement des fichiers de l'application
rem /E, never /MIR: mirroring deletes everything in the destination that is not
rem in the package, which wipes the client's backups and uploaded files. The
rem excluded folders are given as full paths because robocopy does not match
rem relative ones.
robocopy "%~dp0app" "%TARGET%\app" /E /NFL /NDL /NJH /NJS /NP ^
  /XD "%TARGET%\app\storage\app" "%TARGET%\app\storage\backups" "%TARGET%\app\storage\logs" ^
      "%TARGET%\app\storage\framework\sessions" "%TARGET%\app\storage\framework\cache" ^
      "%TARGET%\app\storage\framework\views" "%TARGET%\app\database" ^
  /XF ".env" "licence.key" "database.sqlite"
if errorlevel 8 (
    echo [X] La copie des fichiers a echoue.
    pause
    exit /b 1
)

echo [4/5] Migrations
pushd "%TARGET%\app"
"%PHP%" artisan migrate --force --no-ansi
if errorlevel 1 (
    echo [X] Migrations failed - l'ancienne base est intacte, une sauvegarde a ete prise.
    popd
    pause
    exit /b 1
)
"%PHP%" artisan optimize --no-ansi >nul
popd

echo [5/5] Redemarrage
call "%TARGET%\start.bat"

echo.
echo Mise a jour terminee
pause
