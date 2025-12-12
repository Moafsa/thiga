# Troubleshooting: Problemas de Conexão WhatsApp/WuzAPI

## Problemas Identificados

### 1. Webhook URL Incorreta ❌

**Problema:** O webhook está configurado como `http://app:9000/api/webhooks/whatsapp`, mas a porta 9000 é o PHP-FPM, não um servidor HTTP completo. O PHP-FPM não aceita requisições HTTP diretas.

**Sintomas:**
- Logs do WuzAPI mostram: `connection reset by peer`
- Webhooks não são recebidos pelo Laravel
- Eventos do WhatsApp não são processados

**Solução:**

Altere a variável `WUZAPI_WEBHOOK_URL` no arquivo `.env`:

```env
# ❌ INCORRETO (não funciona)
WUZAPI_WEBHOOK_URL=http://app:9000/api/webhooks/whatsapp

# ✅ CORRETO (use uma das opções abaixo)
# Opção 1: Via Nginx (recomendado para ambiente Docker)
WUZAPI_WEBHOOK_URL=http://nginx:80/api/webhooks/whatsapp

# Opção 2: URL externa (se o app estiver acessível externamente)
WUZAPI_WEBHOOK_URL=http://localhost:8082/api/webhooks/whatsapp
# ou
WUZAPI_WEBHOOK_URL=https://seu-dominio.com/api/webhooks/whatsapp
```

**Após alterar:**
1. Recrie a integração ou atualize o webhook manualmente
2. Reinicie o container do WuzAPI: `docker restart tms_saas_wuzapi`

---

### 2. Token Admin Incorreto ⚠️

**Problema:** O token admin configurado no `.env` pode não corresponder ao token configurado no `docker-compose.yml`.

**Sintomas:**
- Erro 401 ao tentar criar usuários no WuzAPI
- Mensagem: "Token inválido"

**Solução:**

Verifique se o token está consistente:

1. **No `.env`:**
```env
WUZAPI_ADMIN_TOKEN=admin_token_123
```

2. **No `docker-compose.yml`:**
```yaml
wuzapi:
  environment:
    - WUZAPI_ADMIN_TOKEN=${WUZAPI_ADMIN_TOKEN:-admin_token_123}
```

3. **Se alterar o token:**
   - Atualize ambos os arquivos
   - Reinicie o container: `docker-compose restart wuzapi`

---

### 3. Erro 401: "logged out from another device" 🔐

**Problema:** O WhatsApp foi desconectado de outro dispositivo ou houve múltiplas tentativas de conexão.

**Sintomas:**
- Logs mostram: `401: logged out from another device`
- WhatsApp não conecta mesmo após escanear QR Code
- Status fica em "pending" ou "disconnected"

**Solução:**

1. **Limpar sessões do WuzAPI:**
```powershell
.\scripts\clear-wuzapi-sessions.ps1
```

2. **Verificar dispositivos conectados no WhatsApp:**
   - Abra o WhatsApp no celular
   - Vá em **Configurações > Dispositivos Conectados**
   - Remova dispositivos desnecessários (máximo 4 dispositivos)

3. **Limpar sessões manualmente:**
```bash
# Parar container
docker stop tms_saas_wuzapi

# Remover volume de sessões
docker volume rm tms_saas_wuzapi_sessions

# Recriar volume
docker volume create tms_saas_wuzapi_sessions

# Reiniciar container
docker start tms_saas_wuzapi
```

4. **Reconectar:**
   - Acesse a interface de integrações
   - Clique em "Desconectar" (se houver)
   - Gere um novo QR Code
   - Escaneie com o WhatsApp

---

### 4. Container Sem Acesso à Internet 🌐

**Problema:** O container do WuzAPI não consegue acessar os servidores do WhatsApp.

**Sintomas:**
- QR Code não é gerado
- Erros de timeout nos logs
- Mensagem: "Não foi possível conectar-se"

**Solução:**

1. **Testar conectividade:**
```bash
docker exec tms_saas_wuzapi ping -c 2 8.8.8.8
docker exec tms_saas_wuzapi wget -O- --timeout=5 https://web.whatsapp.com
```

2. **Se falhar, verificar:**
   - Firewall do Windows não está bloqueando Docker
   - Proxy corporativo (se aplicável)
   - Configurações de rede do Docker Desktop

3. **Configurar proxy (se necessário):**
```yaml
# docker-compose.yml
wuzapi:
  environment:
    - HTTP_PROXY=http://proxy.empresa.com:8080
    - HTTPS_PROXY=http://proxy.empresa.com:8080
    - NO_PROXY=localhost,127.0.0.1,pgsql
```

---

## Script de Diagnóstico

Execute o script de diagnóstico para identificar problemas automaticamente:

```powershell
.\scripts\diagnose-whatsapp-connection.ps1
```

O script verifica:
- ✅ Status dos containers
- ✅ Conectividade com internet
- ✅ Acesso entre containers
- ✅ Configurações do webhook
- ✅ Logs recentes
- ✅ Status da sessão WhatsApp

---

## Checklist de Verificação

Antes de reportar um problema, verifique:

- [ ] Container WuzAPI está rodando: `docker ps | grep wuzapi`
- [ ] Container App está rodando: `docker ps | grep app`
- [ ] Container Nginx está rodando: `docker ps | grep nginx`
- [ ] WuzAPI consegue acessar internet: `docker exec tms_saas_wuzapi ping 8.8.8.8`
- [ ] Webhook URL está correta no `.env`
- [ ] Token admin está correto e consistente
- [ ] WhatsApp não está conectado em mais de 4 dispositivos
- [ ] Sessões antigas foram limpas
- [ ] Logs não mostram erros críticos: `docker logs tms_saas_wuzapi --tail 50`

---

## Próximos Passos Após Correção

1. **Atualizar webhook nas integrações existentes:**
   - Acesse a interface de integrações
   - Clique em "Sincronizar" para cada integração
   - Ou recrie as integrações

2. **Testar conexão:**
   - Gere um novo QR Code
   - Escaneie com o WhatsApp
   - Verifique se o status muda para "Conectado"

3. **Monitorar logs:**
```bash
docker logs -f tms_saas_wuzapi
```

---

## Referências

- [Troubleshooting QR Connection](./TROUBLESHOOTING_QR_CONNECTION.md)
- [Troubleshooting WuzAPI 401](./TROUBLESHOOTING_WUZAPI_401.md)
- [Documentação WuzAPI](./integracoes/wuzapi.md)



