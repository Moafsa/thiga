# Build Instructions

## Build do Projeto Laravel

Este documento contém as instruções para fazer o build do projeto após as alterações.

### Opção 1: Build via Docker (Recomendado)

Se você estiver usando Docker, execute os comandos dentro do container:

```bash
# Entrar no container
docker exec -it tms_saas_app bash

# Dentro do container, executar:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Ou execute tudo de uma vez:

```bash
docker exec -it tms_saas_app bash -c "php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan optimize"
```

### Opção 2: Build Local (Windows)

Se você tiver PHP instalado localmente:

```powershell
# Executar o script de build
.\build.bat
```

Ou manualmente:

```powershell
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Opção 3: Build Local (Linux/Mac)

```bash
# Executar o script de build
chmod +x build.sh
./build.sh
```

Ou manualmente:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## Comandos de Build Explicados

1. **config:clear** - Limpa o cache de configuração
2. **cache:clear** - Limpa todo o cache da aplicação
3. **route:clear** - Limpa o cache de rotas
4. **view:clear** - Limpa o cache de views compiladas
5. **config:cache** - Cria cache de configuração (otimização)
6. **route:cache** - Cria cache de rotas (otimização)
7. **view:cache** - Compila e cacheia as views (otimização)
8. **optimize** - Otimiza a aplicação (combina vários comandos)

## Após o Build

Após executar o build, as seguintes otimizações estarão ativas:

- ✅ Configurações em cache
- ✅ Rotas em cache
- ✅ Views compiladas
- ✅ Autoloader otimizado

## Notas Importantes

- O build deve ser executado após alterações em:
  - Configurações (`config/`)
  - Rotas (`routes/`)
  - Views (`resources/views/`)
  - Service Providers

- Em ambiente de desenvolvimento, você pode pular o cache para ver mudanças imediatamente:
  ```bash
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```

- Em produção, sempre execute o build completo para melhor performance.

















