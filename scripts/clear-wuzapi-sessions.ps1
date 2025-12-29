# Script para limpar sessões do WuzAPI
# Uso: .\scripts\clear-wuzapi-sessions.ps1

Write-Host "=== Limpando sessões do WuzAPI ===" -ForegroundColor Yellow
Write-Host ""

# Verificar se o container está rodando
$containerRunning = docker ps --filter "name=tms_saas_wuzapi" --format "{{.Names}}"

if (-not $containerRunning) {
    Write-Host "ERRO: Container tms_saas_wuzapi não está rodando!" -ForegroundColor Red
    Write-Host "Inicie o container primeiro com: docker start tms_saas_wuzapi" -ForegroundColor Yellow
    exit 1
}

Write-Host "Container encontrado. Limpando sessões..." -ForegroundColor Green

# Parar o container temporariamente
Write-Host "Parando container..." -ForegroundColor Yellow
docker stop tms_saas_wuzapi

# Limpar volume de sessões
Write-Host "Limpando volume de sessões..." -ForegroundColor Yellow
docker volume rm tms_saas_wuzapi_sessions 2>&1 | Out-Null

# Recriar volume vazio
Write-Host "Recriando volume..." -ForegroundColor Yellow
docker volume create tms_saas_wuzapi_sessions | Out-Null

# Reiniciar container
Write-Host "Reiniciando container..." -ForegroundColor Yellow
docker start tms_saas_wuzapi

# Aguardar container iniciar
Write-Host "Aguardando container iniciar..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Verificar se está rodando
$containerRunning = docker ps --filter "name=tms_saas_wuzapi" --format "{{.Names}}"
if ($containerRunning) {
    Write-Host ""
    Write-Host "✓ Sessões limpas com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANTE:" -ForegroundColor Yellow
    Write-Host "1. Aguarde pelo menos 15 minutos antes de tentar conectar novamente" -ForegroundColor White
    Write-Host "2. Verifique se o número não está conectado em outros dispositivos" -ForegroundColor White
    Write-Host "3. No WhatsApp, vá em Configurações > Dispositivos Conectados e remova dispositivos desnecessários" -ForegroundColor White
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "ERRO: Container não iniciou corretamente!" -ForegroundColor Red
    Write-Host "Verifique os logs com: docker logs tms_saas_wuzapi" -ForegroundColor Yellow
    exit 1
}



















