@echo off
chcp 65001 > nul
echo ============================================================
echo   INICIANDO CLIENTE PAGOS (C#)
echo ============================================================
cd /d %~dp01_ClientePagos
dotnet run
pause
