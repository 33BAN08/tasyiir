@echo off
setlocal
cd /d "%~dp0"

set "PORT=8000"
for /f "usebackq tokens=1,* delims==" %%A in ("%~dp0app\.env") do (
    if /i "%%A"=="TASYIIR_PORT" set "PORT=%%B"
)
set "PORT=%PORT: =%"

echo Arret de TASYIIR (port %PORT%)...

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$ids = Get-NetTCPConnection -LocalPort %PORT% -State Listen -ErrorAction SilentlyContinue | Select-Object -ExpandProperty OwningProcess -Unique;" ^
  "if (-not $ids) { Write-Host 'TASYIIR n''etait pas demarre.'; exit 0 };" ^
  "foreach ($id in $ids) {" ^
  "  $p = Get-Process -Id $id -ErrorAction SilentlyContinue;" ^
  "  if ($p -and $p.ProcessName -like 'php*') { Stop-Process -Id $id -Force; Write-Host ('Arrete : PID ' + $id) }" ^
  "  else { Write-Host ('Le port %PORT% est utilise par ' + $p.ProcessName + ' (PID ' + $id + ') - non arrete.') } }"

ping -n 3 127.0.0.1 >nul
