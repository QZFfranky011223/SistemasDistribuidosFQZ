@echo off
chcp 65001 > nul
echo ============================================================
echo   INICIANDO TODOS LOS SERVICIOS DE LA CENTRAL DE PAGOS
echo ============================================================

echo [1/4] Iniciando SEGIP GraphQL (Node.js en puerto 5000)...
start "SEGIP GraphQL (Node.js - 5000)" cmd /k "cd /d %~dp03_SEGIP_GraphQL && node server.js"

timeout /t 2 /nobreak > nul

echo [2/4] Iniciando CESSA REST (PHP en puerto 8001)...
start "CESSA REST (PHP - 8001)" cmd /k "cd /d %~dp04_CESSA_Rest && php -S localhost:8001 server.php"

timeout /t 2 /nobreak > nul

echo [3/4] Iniciando ELAPAS REST (PHP en puerto 8002)...
start "ELAPAS REST (PHP - 8002)" cmd /k "cd /d %~dp05_ELAPAS_Rest && php -S localhost:8002 server.php"

timeout /t 2 /nobreak > nul

echo [4/4] Iniciando Servidor Pagos SOAP (C# .NET en puerto 8000)...
start "Servidor Pagos SOAP (C# - 8000)" cmd /k "cd /d %~dp02_ServidorPagosSOAP && dotnet run"

echo.
echo ============================================================
echo   TODOS LOS SERVICIOS ESTAN EN EJECUCION EN SEGUNDO PLANO
echo ============================================================
echo   - SEGIP GraphQL   : http://localhost:5000/graphql
echo   - CESSA REST      : http://localhost:8001/facturas
echo   - ELAPAS REST     : http://localhost:8002/facturas
echo   - Servidor SOAP   : http://localhost:8000/ws/pagos?wsdl
echo.
echo Ahora puedes ejecutar 'iniciar_cliente.bat' para abrir la aplicacion cliente.
pause
