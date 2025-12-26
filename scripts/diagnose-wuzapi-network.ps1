# Script de Diagnóstico de Rede para WuzAPI
# Execute: .\scripts\diagnose-wuzapi-network.ps1

Write-Host "=== Diagnóstico de Conectividade WuzAPI ===" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar se container está rodando
Write-Host "1. Verificando container..." -ForegroundColor Yellow
$container = docker ps --filter "name=tms_saas_wuzapi" --format "{{.Names}}"
if ($container -eq "tms_saas_wuzapi") {
    Write-Host "   ✅ Container está rodando" -ForegroundColor Green
} else {
    Write-Host "   ❌ Container não está rodando" -ForegroundColor Red
    Write-Host "   Execute: docker-compose up -d wuzapi" -ForegroundColor Yellow
    exit 1
}

# 2. Testar ping
Write-Host "2. Testando conectividade básica (ping)..." -ForegroundColor Yellow
$pingResult = docker exec tms_saas_wuzapi ping -c 2 8.8.8.8 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Ping funcionando - container tem acesso à internet" -ForegroundColor Green
} else {
    Write-Host "   ❌ Ping falhou - container SEM acesso à internet" -ForegroundColor Red
    Write-Host "   Possíveis causas:" -ForegroundColor Yellow
    Write-Host "   - Firewall do Windows bloqueando Docker" -ForegroundColor Yellow
    Write-Host "   - Rede do Docker Desktop mal configurada" -ForegroundColor Yellow
    Write-Host "   - Proxy corporativo necessário" -ForegroundColor Yellow
}

# 3. Testar DNS
Write-Host "3. Testando resolução DNS..." -ForegroundColor Yellow
$dnsResult = docker exec tms_saas_wuzapi nslookup web.whatsapp.com 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ DNS funcionando" -ForegroundColor Green
} else {
    Write-Host "   ❌ DNS falhou - não consegue resolver web.whatsapp.com" -ForegroundColor Red
    Write-Host "   Solução: Adicione DNS customizado no docker-compose.yml" -ForegroundColor Yellow
}

# 4. Testar HTTPS para WhatsApp
Write-Host "4. Testando conexão HTTPS com servidores WhatsApp..." -ForegroundColor Yellow
$httpsResult = docker exec tms_saas_wuzapi wget -O- --timeout=5 https://web.whatsapp.com 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ HTTPS funcionando - pode conectar aos servidores WhatsApp" -ForegroundColor Green
} else {
    Write-Host "   ❌ HTTPS falhou - NÃO consegue conectar aos servidores WhatsApp" -ForegroundColor Red
    Write-Host "   Este é provavelmente o problema!" -ForegroundColor Yellow
    Write-Host "   Verifique:" -ForegroundColor Yellow
    Write-Host "   - Firewall bloqueando conexões HTTPS" -ForegroundColor Yellow
    Write-Host "   - Proxy corporativo necessário" -ForegroundColor Yellow
    Write-Host "   - Regras de rede do Docker Desktop" -ForegroundColor Yellow
}

# 5. Verificar logs recentes
Write-Host "5. Verificando logs recentes do WuzAPI..." -ForegroundColor Yellow
$logs = docker logs tms_saas_wuzapi --tail 30 2>&1
$errors = $logs | Select-String -Pattern "error|failed|timeout|connect" -CaseSensitive:$false
if ($errors) {
    Write-Host "   ⚠️  Erros encontrados nos logs:" -ForegroundColor Yellow
    $errors | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
} else {
    Write-Host "   ✅ Nenhum erro recente nos logs" -ForegroundColor Green
}

# 6. Verificar configuração de rede
Write-Host "6. Verificando configuração de rede do container..." -ForegroundColor Yellow
$network = docker network inspect tms_saas_tms_network 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ Rede configurada" -ForegroundColor Green
    $gateway = $network | ConvertFrom-Json | Select-Object -ExpandProperty 0 | Select-Object -ExpandProperty IPAM | Select-Object -ExpandProperty Config | Select-Object -ExpandProperty Gateway
    if ($gateway) {
        Write-Host "   Gateway: $gateway" -ForegroundColor Cyan
    }
} else {
    Write-Host "   ⚠️  Não foi possível verificar rede" -ForegroundColor Yellow
}

# 7. Verificar portas
Write-Host "7. Verificando portas expostas..." -ForegroundColor Yellow
$ports = docker port tms_saas_wuzapi 2>&1
if ($ports) {
    Write-Host "   Portas expostas:" -ForegroundColor Cyan
    $ports | ForEach-Object { Write-Host "   $_" -ForegroundColor Cyan }
} else {
    Write-Host "   ⚠️  Nenhuma porta exposta" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Resumo do Diagnóstico ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Se o teste de HTTPS falhou, o problema é:" -ForegroundColor Yellow
Write-Host "  → Container não consegue conectar aos servidores do WhatsApp" -ForegroundColor Red
Write-Host ""
Write-Host "Soluções possíveis:" -ForegroundColor Yellow
Write-Host "  1. Verificar Firewall do Windows" -ForegroundColor White
Write-Host "  2. Configurar proxy se necessário (docker-compose.yml)" -ForegroundColor White
Write-Host "  3. Adicionar DNS customizado (docker-compose.yml)" -ForegroundColor White
Write-Host "  4. Resetar rede do Docker Desktop" -ForegroundColor White
Write-Host ""
Write-Host "Para mais detalhes, consulte: docs/TROUBLESHOOTING_QR_CONNECTION.md" -ForegroundColor Cyan















