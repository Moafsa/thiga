# Troubleshooting: Erro 401 "logged out from another device" no WuzAPI

## Problema
O WhatsApp fica "conectando" mas não conecta, e os logs mostram:
```
Logged out reason="401: logged out from another device"
```

## Causas Comuns

### 1. Número já conectado em outro dispositivo
O WhatsApp permite apenas **4 dispositivos conectados simultaneamente**. Se o limite foi atingido, novas conexões falham.

**Solução:**
1. Abra o WhatsApp no celular
2. Vá em **Configurações** > **Dispositivos Conectados**
3. Remova dispositivos desnecessários
4. Aguarde alguns minutos antes de tentar conectar novamente

### 2. Múltiplas tentativas de conexão
Muitas tentativas em pouco tempo podem fazer o WhatsApp bloquear temporariamente.

**Solução:**
1. Aguarde **15-30 minutos** antes de tentar novamente
2. Limpe as sessões antigas do WuzAPI (veja abaixo)
3. Tente conectar novamente

### 3. Sessão corrompida ou inválida
Sessões antigas podem estar causando conflitos.

**Solução - Limpar sessões:**
```bash
# Parar o container
docker stop tms_saas_wuzapi

# Limpar sessões (CUIDADO: isso apaga TODAS as sessões)
docker volume rm tms_saas_wuzapi_sessions

# Ou limpar manualmente dentro do container
docker exec tms_saas_wuzapi rm -rf /app/sessions/*

# Reiniciar
docker start tms_saas_wuzapi
```

### 4. WhatsApp detectou comportamento suspeito
O WhatsApp pode ter bloqueado temporariamente o número por detectar uso de API não oficial.

**Solução:**
1. **Aguarde 24-48 horas** antes de tentar novamente
2. Use o número normalmente no aplicativo oficial do WhatsApp
3. Evite múltiplas tentativas de conexão
4. Considere usar um número diferente para testes

### 5. Versão desatualizada do WuzAPI/whatsmeow
Mudanças no protocolo do WhatsApp podem quebrar versões antigas.

**Solução:**
1. Verifique se há atualizações do WuzAPI
2. Atualize a biblioteca whatsmeow:
   ```bash
   cd docker/wuzapi/src
   go get -u go.mau.fi/whatsmeow@latest
   go mod tidy
   docker-compose build wuzapi
   ```

## Passos Recomendados (Ordem)

1. **Verificar dispositivos conectados no WhatsApp**
   - Remova dispositivos desnecessários
   - Aguarde 5 minutos

2. **Limpar sessões do WuzAPI**
   ```bash
   docker exec tms_saas_wuzapi rm -rf /app/sessions/*
   docker restart tms_saas_wuzapi
   ```

3. **Aguardar antes de tentar novamente**
   - Aguarde pelo menos 15 minutos
   - Evite múltiplas tentativas

4. **Tentar conectar novamente**
   - Use o frontend do WuzAPI diretamente
   - Escaneie o QR code apenas uma vez
   - Aguarde a conexão completar (pode levar 1-2 minutos)

5. **Se ainda não funcionar**
   - Aguarde 24 horas
   - Verifique se o número não foi banido
   - Considere usar um número diferente para testes

## Verificação de Status

### Verificar se o número está banido
1. Tente usar o número normalmente no aplicativo oficial do WhatsApp
2. Se não funcionar, o número pode estar banido

### Verificar logs do WuzAPI
```bash
docker logs tms_saas_wuzapi --tail 100 | grep -i "401\|error\|logged"
```

### Verificar sessões ativas
```bash
docker exec tms_saas_wuzapi ls -la /app/sessions/
```

## Prevenção

1. **Não conecte o mesmo número em múltiplas instâncias**
2. **Evite múltiplas tentativas de conexão em pouco tempo**
3. **Mantenha o WuzAPI atualizado**
4. **Use apenas para fins legítimos** (não spam)
5. **Monitore os logs regularmente**

## Aviso Importante

⚠️ **O uso de APIs não oficiais do WhatsApp pode violar os Termos de Serviço e resultar em banimento permanente do número.**

Para uso comercial, considere usar a **WhatsApp Business API oficial**.

















