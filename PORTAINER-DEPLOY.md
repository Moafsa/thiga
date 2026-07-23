# TMS SaaS (Thiga) — Deploy Portainer / Traefik

## URLs (DNS → IP do servidor)

| Serviço | URL |
|---------|-----|
| App TMS | https://tms.conext.click |
| WuzAPI | https://tms.wuzapi.conext.click |
| MinIO API (S3) | https://s3tms.conext.click |
| MinIO Console | https://mtms.conext.click |

> Se preferir `mtms.conex.click` (sem **t**), ajuste o label Traefik em `docker-compose.deploy.yml` e o DNS.

## Portainer

1. **Stacks** → **Add stack**
2. Nome: `tms-thiga`
3. Cole o conteúdo de `docker-compose.deploy.yml` ou use **Git repository** apontando para este diretório
4. Variáveis: copie de `.env.deploy` (ou use `.env.production` já montado no serviço `app`)
5. A rede externa `realindependente_nexts-network` deve existir (Traefik do stack Nexts)

## CLI (servidor)

```bash
cd /root/thiga
docker compose --env-file .env.deploy -f docker-compose.deploy.yml up -d --build
```

## Credenciais iniciais (seed)

### Painel Super Admin (gestão de tenants/planos)

- URL: **https://tms.conext.click/superadmin/login**
- E-mail: `superadmin@conext.click`
- Senha: `SuperAdmin@2026!`

> Não use `/login` nem `/admin` para o super admin. O login em `/login` é para usuários do tenant.

### App do tenant (transportadora)

- URL: **https://tms.conext.click/login**
- Admin demo: `admin@thiga.transportes.com` / `password`

> O usuário `admin@thiga.com.br` (role Super Admin na tabela `users`) não tem tenant vinculado e o dashboard redireciona com erro — use o painel `/superadmin` acima.

## Arquivos importantes

- `docker-compose.deploy.yml` — stack produção
- `.env.production` — variáveis Laravel (montado em `/var/www/.env`)
- `.env.deploy` — segredos para `docker compose` (APP_KEY, senhas DB/MinIO)

## Comandos úteis

```bash
docker exec -u www tms-app php artisan migrate --force
docker exec -u www tms-app php artisan db:seed --force
docker compose -f docker-compose.deploy.yml logs -f app
```
