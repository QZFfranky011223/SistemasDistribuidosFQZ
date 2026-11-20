@echo off
chcp 65001 >nul
title Sistema Distribuido de Gestión de Multas de Tránsito en Línea (MySQL)
echo ============================================================================
echo   SISTEMA DISTRIBUIDO DE GESTION DE MULTAS DE TRANSITO EN LINEA (MySQL)
echo ============================================================================
echo.
echo [0/6] Verificando servicio MySQL (XAMPP 127.0.0.1:3306, usuario: root, password: vacio)...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        echo   -> Iniciando MySQL de XAMPP en segundo plano...
        start "MySQL Server (XAMPP)" /MIN "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 4 /nobreak >nul
    )
)
echo.
echo [1/6] Inicializando Base de Datos MySQL (bd_multas_transito en 127.0.0.1:3306)...
php "%~dp0central-rest-laravel\database\init_mysql.php"
echo.
echo [2/6] Compilando Servidor y Cliente de Sockets TCP en Java...
javac -encoding UTF-8 "%~dp0central-sockets-java\ServidorSocketPago.java" "%~dp0central-sockets-java\ClienteSocketPago.java"
echo.
echo [3/6] Iniciando Servicio SOAP del SEGIP (Puerto 8001)...
start "1. SEGIP - Servicio SOAP (:8001)" cmd /k "cd /d "%~dp0segip-soap" && php -S localhost:8001 server.php"

echo [4/6] Iniciando Central de Registro - Servicio REST Laravel + MySQL (Puerto 8000)...
start "2. Central de Registro - REST Laravel MySQL (:8000)" cmd /k "cd /d "%~dp0central-rest-laravel" && php artisan serve --host=127.0.0.1 --port=8000"

echo [5/6] Iniciando Central de Registro - Servicio GraphQL Node.js + MySQL (Puerto 4000)...
start "3. Central de Registro - GraphQL Node.js MySQL (:4000)" cmd /k "cd /d "%~dp0central-graphql-node" && node server.js"

echo [6/6] Iniciando Central de Registro - Servidor Sockets TCP Java (Puerto 5000)...
start "4. Central de Registro - Java TCP Socket Server (:5000)" cmd /k "cd /d "%~dp0central-sockets-java" && java ServidorSocketPago"

echo [Extra] Iniciando Modulo Entidad Recaudadora (GraphQL + Socket TCP) en Puerto 3001...
start "5. Entidad Recaudadora - Cajero (:3001)" cmd /k "cd /d "%~dp0entidad-recaudadora" && node server.js"

echo [Extra] Abriendo Modulo de Escritorio Policia de Transito (C# Windows Forms)...
if exist "%~dp0policia-forms-csharp\bin\Debug\net10.0-windows\PoliciaTransitoForms.exe" (
    start "" "%~dp0policia-forms-csharp\bin\Debug\net10.0-windows\PoliciaTransitoForms.exe"
) else (
    start "6. Policia de Transito - Forms C#" cmd /k "cd /d "%~dp0policia-forms-csharp" && dotnet run"
)

echo.
echo ============================================================================
echo   TODOS LOS SERVICIOS HAN SIDO INICIADOS CON MYSQL (bd_multas_transito):
echo   - Base de Datos MySQL:              127.0.0.1:3306 / bd_multas_transito (root / sin password)
echo   - SEGIP SOAP (verificarCI):         http://localhost:8001/server.php?wsdl
echo   - Central REST Laravel (/api/multa): http://localhost:8000/api/multa
echo   - Modulo Cliente Web (Ciudadano):   http://localhost:8000
echo   - Central GraphQL (Node.js):        http://localhost:4000/graphql
echo   - Entidad Recaudadora (Cajero):     http://localhost:3001
echo   - Central Sockets TCP (Java):       localhost:5000
echo ============================================================================
pause
