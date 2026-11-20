Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   INICIANDO TODOS LOS SERVICIOS DE LA CENTRAL DE PAGOS     " -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan

$baseDir = $PSScriptRoot

Write-Host "[1/4] Iniciando SEGIP GraphQL (Node.js en puerto 5000)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$baseDir\3_SEGIP_GraphQL'; node server.js"

Start-Sleep -Seconds 2

Write-Host "[2/4] Iniciando CESSA REST (PHP en puerto 8001)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$baseDir\4_CESSA_Rest'; php -S localhost:8001 server.php"

Start-Sleep -Seconds 2

Write-Host "[3/4] Iniciando ELAPAS REST (PHP en puerto 8002)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$baseDir\5_ELAPAS_Rest'; php -S localhost:8002 server.php"

Start-Sleep -Seconds 2

Write-Host "[4/4] Iniciando Servidor Pagos SOAP (C# en puerto 8000)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$baseDir\2_ServidorPagosSOAP'; dotnet run"

Write-Host "`nServicios iniciados en segundo plano correctamente." -ForegroundColor Green
Write-Host "Para usar el cliente, ejecuta: .\iniciar_cliente.ps1" -ForegroundColor Cyan
