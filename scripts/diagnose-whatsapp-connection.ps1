# Script de Diagnóstico de Conexão WhatsApp/WuzAPI
# Execute: .\scripts\diagnose-whatsapp-connection.ps1

Write-Host "=== Diagnóstico de Conexão WhatsApp/WuzAPI ===" -ForegroundColor Cyan
Write-Host ""

# 1. Verificar containers
Write-Host "1. Verificando containers..." -ForegroundColor Yellow
$wuzapi = docker ps --filter "name=tms_saas_wuzapi" --format "{{.Names}}"
$app = docker ps --filter "name=tms_saas_app" --format "{{.Names}}"
$nginx = docker ps --filter "name=tms_saas_nginx" --format "{{.Names}}"

if ($wuzapi -eq "tms_saas_wuzapi") {
    Write-Host "   ✅ Container WuzAPI está rodando" -ForegroundColor Green
} else {
    Write-Host "   ❌ Container WuzAPI NÃO está rodando" -ForegroundColor Red
    Write-Host "   Execute: docker-compose up -d wuzapi" -ForegroundColor Yellow
    exit 1
}

if ($app -eq "tms_saas_app") {
    Write-Host "   ✅ Container App está rodando" -ForegroundColor Green
} else {
    Write-Host "   ❌ Container App NÃO está rodando" -ForegroundColor Red
}

if ($nginx -eq "tms_saas_nginx") {
    Write-Host "   ✅ Container Nginx está rodando" -ForegroundColor Green
} else {
    Write-Host "   ❌ Container Nginx NÃO está rodando" -ForegroundColor Red
}

Write-Host ""

# 2. Verificar conectividade do WuzAPI com internet
Write-Host "2. Testando conectividade do WuzAPI com internet..." -ForegroundColor Yellow
$pingResult = docker exec tms_saas_wuzapi ping -c 2 8.8.8.8 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✅ WuzAPI consegue acessar a internet" -ForegroundColor Green
} else {
    Write-Host "   ❌ WuzAPI NÃO consegue acessar a internet" -ForegroundColor Red
    Write-Host "   Verifique firewall e configurações de rede do Docker" -ForegroundColor Yellow
}

Write-Host ""

# 3. Verificar acesso do App ao WuzAPI
Write-Host "3. Testando acesso do App ao WuzAPI..." -ForegroundColor Yellow
$wuzapiTest = docker exec tms_saas_app curl -s -o $null -w "%{http_code}" http://wuzapi:8080/admin/users -H "Authorization: admin_token_123" 2>&1
if ($wuzapiTest -eq "200" -or $wuzapiTest -eq "401") {
    Write-Host "   ✅ App consegue acessar WuzAPI (status: $wuzapiTest)" -ForegroundColor Green
    if ($wuzapiTest -eq "401") {
        Write-Host "   ⚠️  Token admin pode estar incorreto" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ❌ App NÃO consegue acessar WuzAPI (status: $wuzapiTest)" -ForegroundColor Red
}

Write-Host ""

# 4. Verificar acesso do WuzAPI ao webhook
Write-Host "4. Testando acesso do WuzAPI ao webhook..." -ForegroundColor Yellow
Write-Host "   Testando http://nginx:80/api/webhooks/whatsapp..." -ForegroundColor Gray
$webhookTest1 = docker exec tms_saas_wuzapi wget -O- --timeout=3 http://nginx:80/api/webhooks/whatsapp 2>&1
if ($LASTEXITCODE -eq 0 -or $webhookTest1 -match "404|405|422") {
    Write-Host "   ✅ WuzAPI consegue acessar webhook via nginx:80" -ForegroundColor Green
} else {
    Write-Host "   ❌ WuzAPI NÃO consegue acessar webhook via nginx:80" -ForegroundColor Red
    Write-Host "   Erro: $webhookTest1" -ForegroundColor Gray
}

Write-Host "   Testando http://app:9000/api/webhooks/whatsapp..." -ForegroundColor Gray
$webhookTest2 = docker exec tms_saas_wuzapi wget -O- --timeout=3 http://app:9000/api/webhooks/whatsapp 2>&1
if ($LASTEXITCODE -eq 0 -or $webhookTest2 -match "404|405|422") {
    Write-Host "   ✅ WuzAPI consegue acessar webhook via app:9000" -ForegroundColor Green
} else {
    Write-Host "   ❌ WuzAPI NÃO consegue acessar webhook via app:9000" -ForegroundColor Red
    Write-Host "   ⚠️  Esta é a URL padrão configurada, mas pode estar incorreta" -ForegroundColor Yellow
    Write-Host "   Erro: $webhookTest2" -ForegroundColor Gray
}

Write-Host ""

# 5. Verificar logs recentes do WuzAPI
Write-Host "5. Verificando logs recentes do WuzAPI..." -ForegroundColor Yellow
$logs = docker logs tms_saas_wuzapi --tail 30 2>&1
$errorCount = ($logs | Select-String -Pattern "error|ERROR|failed|Failed|401|timeout" -CaseSensitive:$false).Count
if ($errorCount -gt 0) {
    Write-Host "   ⚠️  Encontrados $errorCount erros/avisos nos logs recentes" -ForegroundColor Yellow
    Write-Host "   Últimos erros:" -ForegroundColor Gray
    $logs | Select-String -Pattern "error|ERROR|failed|Failed|401" -CaseSensitive:$false | Select-Object -Last 5 | ForEach-Object {
        Write-Host "   - $($_.Line)" -ForegroundColor Gray
    }
} else {
    Write-Host "   ✅ Nenhum erro recente encontrado nos logs" -ForegroundColor Green
}

Write-Host ""

# 6. Verificar variáveis de ambiente
Write-Host "6. Verificando configurações..." -ForegroundColor Yellow
$envFile = ".env"
if (Test-Path $envFile) {
    $wuzapiBaseUrl = (Select-String -Path $envFile -Pattern "WUZAPI_BASE_URL" | ForEach-Object { $_.Line })
    $wuzapiAdminToken = (Select-String -Path $envFile -Pattern "WUZAPI_ADMIN_TOKEN" | ForEach-Object { $_.Line })
    $wuzapiWebhook = (Select-String -Path $envFile -Pattern "WUZAPI_WEBHOOK_URL" | ForEach-Object { $_.Line })
    
    if ($wuzapiBaseUrl) {
        Write-Host "   ✅ WUZAPI_BASE_URL configurado" -ForegroundColor Green
        Write-Host "      $wuzapiBaseUrl" -ForegroundColor Gray
    } else {
        Write-Host "   ⚠️  WUZAPI_BASE_URL não encontrado no .env" -ForegroundColor Yellow
    }
    
    if ($wuzapiAdminToken) {
        Write-Host "   ✅ WUZAPI_ADMIN_TOKEN configurado" -ForegroundColor Green
    } else {
        Write-Host "   ⚠️  WUZAPI_ADMIN_TOKEN não encontrado no .env" -ForegroundColor Yellow
    }
    
    if ($wuzapiWebhook) {
        Write-Host "   ✅ WUZAPI_WEBHOOK_URL configurado" -ForegroundColor Green
        Write-Host "      $wuzapiWebhook" -ForegroundColor Gray
        if ($wuzapiWebhook -match "app:9000") {
            Write-Host "   ⚠️  Webhook está usando app:9000 (pode não funcionar)" -ForegroundColor Yellow
            Write-Host "   💡 Considere usar http://nginx:80/api/webhooks/whatsapp" -ForegroundColor Cyan
        }
    } else {
        Write-Host "   ⚠️  WUZAPI_WEBHOOK_URL não encontrado no .env" -ForegroundColor Yellow
    }
} else {
    Write-Host "   ❌ Arquivo .env não encontrado" -ForegroundColor Red
}

Write-Host ""

# 7. Verificar status da sessão WhatsApp
Write-Host "7. Verificando status da sessão WhatsApp..." -ForegroundColor Yellow
$recentLogout = $logs | Select-String -Pattern "401.*logged out|Logged out" -CaseSensitive:$false
if ($recentLogout) {
    Write-Host "   ⚠️  WhatsApp foi desconectado recentemente (erro 401)" -ForegroundColor Yellow
    Write-Host "   💡 Solução: Limpe as sessões e reconecte" -ForegroundColor Cyan
    Write-Host "      Execute: .\scripts\clear-wuzapi-sessions.ps1" -ForegroundColor Cyan
} else {
    Write-Host "   ✅ Nenhum logout recente detectado" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== Diagnóstico Completo ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Próximos passos:" -ForegroundColor Yellow
Write-Host "1. Se o webhook falhar, altere WUZAPI_WEBHOOK_URL para http://nginx:80/api/webhooks/whatsapp" -ForegroundColor White
Write-Host "2. Se houver erro 401, limpe as sessões: .\scripts\clear-wuzapi-sessions.ps1" -ForegroundColor White
Write-Host "3. Verifique os logs em tempo real: docker logs -f tms_saas_wuzapi" -ForegroundColor White
Write-Host ""















