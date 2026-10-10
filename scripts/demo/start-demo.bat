@echo off
rem TASYIIR - demo en ligne (essai gratuit) via Cloudflare Tunnel.
rem Double-cliquez sur ce fichier. Laissez la fenetre ouverte pendant la demo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0demo.ps1" %*
if errorlevel 1 pause
