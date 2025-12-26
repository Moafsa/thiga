# Guia de Deploy e Atualização - TMS SaaS

Este documento contém instruções para fazer deploy e atualizar o sistema TMS SaaS em diferentes ambientes.

## 📋 Índice

1. [Inicialização Inicial](#inicialização-inicial)
2. [Atualização do Sistema](#atualização-do-sistema)
3. [Deploy em Produção](#deploy-em-produção)
4. [Configuração de Jobs Agendados](#configuração-de-jobs-agendados)
5. [Verificação Pós-Deploy](#verificação-pós-deploy)

## 🚀 Inicialização Inicial

### Windows

```batch
start-servers.bat
```

Este script:
- Constrói os containers Docker
- Inicia os serviços (PostgreSQL, Redis, App, Nginx, Queue)
- Executa migrações do banco de dados
- Configura cache e otimizações
- Cria diretórios necessários
- Verifica tarefas agendadas

### Linux/Mac

```bash
chmod +x scripts/init-wuzapi.sh
./scripts/init-wuzapi.sh
```

Ou use Docker Compose diretamente:

```bash
docker-compose up -d
docker exec tms_saas_app php artisan migrate --force
docker exec tms_saas_app php artisan optimize
```

## 🔄 Atualização do Sistema

Quando você atualizar o código (git pull, etc.), execute o script de atualização:

### Windows

```batch
update-system.bat
```

### Linux/Mac

```bash
chmod +x update-system.sh
./update-system.sh
```

O script de atualização:
1. Instala/atualiza dependências do Composer
2. Executa novas migrações
3. Limpa todos os caches
4. Otimiza a aplicação
5. Cria diretórios necessários
6. Verifica tarefas agendadas
7. Testa novos comandos

## 🏭 Deploy em Produção

### Pré-requisitos

1. Configure o arquivo `.env` para produção
2. Certifique-se de que `docker-compose.prod.yml` está configurado
3. Tenha acesso SSH ao servidor de produção

### Processo de Deploy

```bash
chmod +x deploy-production.sh
./deploy-production.sh
```

O script de deploy:
1. Puxa o código mais recente do Git
2. Constrói os containers de produção
3. Instala dependências (sem dev)
4. Executa migrações
5. Limpa e otimiza caches
6. Cria diretórios de armazenamento
7. Reinicia serviços
8. Verifica tarefas agendadas

### Deploy Manual

Se preferir fazer deploy manualmente:

```bash
# 1. Pull do código
git pull origin main

# 2. Build dos containers
docker-compose -f docker-compose.prod.yml build

# 3. Instalar dependências
docker exec tms_saas_app_prod composer install --no-dev --optimize-autoloader

# 4. Migrações
docker exec tms_saas_app_prod php artisan migrate --force

# 5. Otimização
docker exec tms_saas_app_prod php artisan config:cache
docker exec tms_saas_app_prod php artisan route:cache
docker exec tms_saas_app_prod php artisan view:cache
docker exec tms_saas_app_prod php artisan optimize

# 6. Reiniciar serviços
docker-compose -f docker-compose.prod.yml restart app queue
```

## ⏰ Configuração de Jobs Agendados

O sistema possui dois jobs agendados que precisam ser executados:

1. **Limpeza de Cache** - Diariamente às 02:00
2. **Verificação de CNH Expirando** - Diariamente às 08:00

### Opção 1: Cron (Recomendado para Produção)

Adicione ao crontab do servidor:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Opção 2: Supervisor (Recomendado para Produção)

Crie o arquivo `/etc/supervisor/conf.d/tms-schedule.conf`:

```ini
[program:tms-schedule]
process_name=%(program_name)s
command=php /path-to-project/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/schedule.log
```

Depois execute:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start tms-schedule
```

### Opção 3: Systemd (Linux)

Crie o arquivo `/etc/systemd/system/tms-schedule.service`:

```ini
[Unit]
Description=TMS SaaS Schedule Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path-to-project
ExecStart=/usr/bin/php artisan schedule:work
Restart=always

[Install]
WantedBy=multi-user.target
```

Depois execute:

```bash
sudo systemctl daemon-reload
sudo systemctl enable tms-schedule
sudo systemctl start tms-schedule
```

### Opção 4: Docker (Desenvolvimento)

Para desenvolvimento, você pode executar manualmente:

```bash
docker exec tms_saas_app php artisan schedule:work
```

Ou adicione ao `docker-compose.yml`:

```yaml
schedule:
  build:
    context: .
    dockerfile: Dockerfile
  container_name: tms_saas_schedule
  command: php artisan schedule:work
  volumes:
    - ./:/var/www
  networks:
    - tms_network
  depends_on:
    - app
```

## ✅ Verificação Pós-Deploy

Após o deploy, verifique se tudo está funcionando:

### 1. Verificar Tarefas Agendadas

```bash
docker exec tms_saas_app_prod php artisan schedule:list
```

Você deve ver:
- `cache:clean-old` - Daily at 02:00
- `App\Jobs\CheckExpiringCnh` - Daily at 08:00

### 2. Testar Limpeza de Cache

```bash
docker exec tms_saas_app_prod php artisan cache:clean-old --days=7
```

### 3. Verificar Logs

```bash
docker-compose -f docker-compose.prod.yml logs -f app
```

### 4. Verificar Status dos Serviços

```bash
docker-compose -f docker-compose.prod.yml ps
```

### 5. Testar Funcionalidades

- Acesse a aplicação e verifique se está funcionando
- Teste upload de foto de motorista
- Verifique se as notificações de CNH estão sendo criadas
- Teste o mapa de monitoramento com lazy loading

## 🔧 Comandos Úteis

### Limpar Cache Manualmente

```bash
docker exec tms_saas_app_prod php artisan cache:clean-old --days=7 --force
```

### Verificar Migrações Pendentes

```bash
docker exec tms_saas_app_prod php artisan migrate:status
```

### Executar Seeders (se necessário)

```bash
docker exec tms_saas_app_prod php artisan db:seed --force
```

### Ver Logs de Jobs

```bash
docker exec tms_saas_app_prod tail -f storage/logs/laravel.log
```

### Reiniciar Queue Worker

```bash
docker-compose -f docker-compose.prod.yml restart queue
```

## 🐛 Troubleshooting

### Problema: Jobs agendados não executam

**Solução:**
1. Verifique se o cron/supervisor está configurado
2. Verifique os logs: `docker exec tms_saas_app_prod tail -f storage/logs/laravel.log`
3. Teste manualmente: `docker exec tms_saas_app_prod php artisan schedule:run`

### Problema: Cache não está sendo limpo

**Solução:**
1. Verifique permissões: `docker exec tms_saas_app_prod ls -la storage/app/public/cache`
2. Execute manualmente: `docker exec tms_saas_app_prod php artisan cache:clean-old --days=7 --force`
3. Verifique se o Redis está funcionando: `docker exec tms_saas_redis_prod redis-cli ping`

### Problema: Notificações de CNH não aparecem

**Solução:**
1. Verifique se o job está agendado: `docker exec tms_saas_app_prod php artisan schedule:list`
2. Execute manualmente: `docker exec tms_saas_app_prod php artisan queue:work --once`
3. Verifique se há motoristas com CNH expirando: `docker exec tms_saas_app_prod php artisan tinker` e execute:
   ```php
   \App\Models\Driver::whereNotNull('cnh_expiry_date')
       ->where('cnh_expiry_date', '<=', now()->addDays(30))
       ->get();
   ```

## 📝 Notas Importantes

1. **Sempre faça backup do banco de dados antes de executar migrações em produção**
2. **Teste em ambiente de staging antes de fazer deploy em produção**
3. **Monitore os logs após o deploy**
4. **Configure alertas para falhas nos jobs agendados**
5. **Mantenha o `.env` seguro e não commite-o no Git**

## 🔐 Segurança

- Use variáveis de ambiente para credenciais
- Configure firewall adequadamente
- Use HTTPS em produção
- Mantenha dependências atualizadas
- Monitore logs regularmente




