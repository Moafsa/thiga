# Troubleshooting: Erro de Conexão no QR Code (Ambiente Local)

## Problema
Ao tentar escanear o QR code no WhatsApp, aparece o erro:
```
Não foi possível conectar-se. Verifique a conexão do seu celular com a internet e leia o QR code novamente.
```

## Diagnóstico

O WuzAPI funciona perfeitamente online e no GitHub, mas falha no ambiente local. Isso indica um problema de **conectividade de rede** do container Docker, não um bug no código.

### 1. Verificar Conectividade do Container

Teste se o container consegue acessar a internet e os servidores do WhatsApp:

```bash
# Entrar no container
docker exec -it tms_saas_wuzapi sh

# Testar conectividade básica
ping -c 3 8.8.8.8

# Testar DNS
nslookup web.whatsapp.com

# Testar conexão HTTPS com servidores WhatsApp
wget -O- https://web.whatsapp.com --timeout=10
```

**Se falhar:** O container não tem acesso à internet. Verifique:
- Firewall do Windows bloqueando Docker
- Proxy corporativo necessário
- Configurações de rede do Docker Desktop

### 2. Verificar Configuração de Rede do Docker

O WuzAPI precisa de acesso à internet para conectar aos servidores do WhatsApp. Verifique:

```bash
# Verificar rede do container
docker network inspect tms_saas_tms_network

# Verificar se o container tem gateway configurado
docker exec tms_saas_wuzapi ip route
```

### 3. Verificar Logs do WuzAPI

Os logs mostrarão o erro real de conexão:

```bash
# Ver logs em tempo real
docker logs -f tms_saas_wuzapi

# Procurar por erros de conexão
docker logs tms_saas_wuzapi 2>&1 | grep -i "connect\|error\|failed\|timeout"
```

### 4. Problemas Comuns e Soluções

#### A. Firewall do Windows Bloqueando Docker

**Sintoma:** Container não consegue fazer requisições HTTPS externas.

**Solução:**
1. Abra o **Firewall do Windows**
2. Vá em **Configurações Avançadas**
3. Adicione regra de saída para Docker:
   - Programa: `C:\Program Files\Docker\Docker\resources\dockerd.exe`
   - Ação: Permitir
   - Protocolo: TCP
   - Porta: Todas

#### B. Proxy Corporativo

**Sintoma:** Container funciona em casa mas não no trabalho.

**Solução:**
Adicione configuração de proxy no `docker-compose.yml`:

```yaml
wuzapi:
  # ... outras configurações ...
  environment:
    - HTTP_PROXY=http://proxy.empresa.com:8080
    - HTTPS_PROXY=http://proxy.empresa.com:8080
    - NO_PROXY=localhost,127.0.0.1,pgsql
```

#### C. DNS Não Resolvendo

**Sintoma:** Container não consegue resolver nomes de domínio.

**Solução:**
Adicione DNS customizado no `docker-compose.yml`:

```yaml
wuzapi:
  # ... outras configurações ...
  dns:
    - 8.8.8.8
    - 8.8.4.4
```

#### D. Rede do Docker Desktop

**Sintoma:** Container isolado sem acesso à internet.

**Solução:**
1. Abra **Docker Desktop**
2. Vá em **Settings** > **Resources** > **Network**
3. Verifique se há configurações bloqueando acesso externo
4. Tente resetar a rede: **Settings** > **Troubleshoot** > **Reset to factory defaults**

### 5. Teste de Conectividade Completo

Execute este script para diagnosticar:

```bash
# Criar script de teste
cat > test-wuzapi-connectivity.sh << 'EOF'
#!/bin/bash

echo "=== Testando Conectividade do WuzAPI ==="

# 1. Verificar se container está rodando
echo "1. Verificando container..."
docker ps | grep wuzapi || { echo "❌ Container não está rodando"; exit 1; }
echo "✅ Container está rodando"

# 2. Testar ping
echo "2. Testando ping..."
docker exec tms_saas_wuzapi ping -c 2 8.8.8.8 > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ Ping funcionando"
else
    echo "❌ Ping falhou - container sem acesso à internet"
fi

# 3. Testar DNS
echo "3. Testando DNS..."
docker exec tms_saas_wuzapi nslookup web.whatsapp.com > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ DNS funcionando"
else
    echo "❌ DNS falhou - problema de resolução de nomes"
fi

# 4. Testar HTTPS
echo "4. Testando conexão HTTPS..."
docker exec tms_saas_wuzapi wget -O- --timeout=5 https://web.whatsapp.com > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✅ HTTPS funcionando"
else
    echo "❌ HTTPS falhou - não consegue conectar aos servidores WhatsApp"
fi

# 5. Verificar logs recentes
echo "5. Últimos erros nos logs:"
docker logs tms_saas_wuzapi --tail 20 | grep -i "error\|failed\|timeout" || echo "Nenhum erro recente"

echo ""
echo "=== Diagnóstico Completo ==="
EOF

chmod +x test-wuzapi-connectivity.sh
./test-wuzapi-connectivity.sh
```

### 6. Solução Rápida: Usar Rede Host (Linux/Mac)

Se estiver em Linux ou Mac, pode usar rede host para bypass de problemas de rede do Docker:

```yaml
wuzapi:
  # ... outras configurações ...
  network_mode: host  # Apenas Linux/Mac
```

**⚠️ Atenção:** Isso expõe as portas diretamente no host. Não use em produção.

### 7. Verificar Portas e Acessibilidade

O WuzAPI precisa estar acessível para receber conexões:

```bash
# Verificar se portas estão abertas
netstat -an | grep "8081\|8083"

# Testar acesso local
curl http://localhost:8083/session/status -H "Token: seu_token_aqui"
```

### 8. Comparar com Ambiente de Produção

Se funciona em produção mas não localmente, compare:

1. **Versão do Docker:** `docker --version`
2. **Configuração de rede:** `docker network ls`
3. **Variáveis de ambiente:** Compare `.env` local vs produção
4. **Firewall:** Verifique regras diferentes

## Checklist de Diagnóstico

- [ ] Container consegue fazer ping (8.8.8.8)
- [ ] DNS resolve web.whatsapp.com
- [ ] Container consegue fazer HTTPS para web.whatsapp.com
- [ ] Logs do WuzAPI não mostram erros de rede
- [ ] Firewall do Windows não está bloqueando Docker
- [ ] Não há proxy corporativo necessário
- [ ] Docker Desktop tem permissões de rede
- [ ] Portas 8081 e 8083 estão acessíveis

## Próximos Passos

1. Execute o script de diagnóstico acima
2. Verifique os logs do WuzAPI durante tentativa de conexão
3. Compare configurações de rede com ambiente que funciona
4. Se necessário, configure proxy ou DNS customizado

## Aviso

⚠️ **Não modifique o código do WuzAPI** - ele funciona corretamente. O problema é sempre de configuração de ambiente/rede local.















