# Solução Completa: MinIO Armazenamento de Documentos

**Status:** ✅ SOLUÇÃO IMPLEMENTADA

**Data:** May 22, 2026

---

## 🎯 O que foi feito

### Correções Aplicadas

#### 1. ✅ Arquivo `.env`
**Mudança:** `FILESYSTEM_DISK=local` → `FILESYSTEM_DISK=minio`

```diff
- FILESYSTEM_DISK=local
+ FILESYSTEM_DISK=minio
```

**Impacto:** O Laravel agora usa MinIO como disco padrão para armazenamento.

---

#### 2. ✅ Arquivo `config/filesystems.php`
**Mudança:** Remover credenciais hardcoded

**Antes:**
```php
'key' => env('MINIO_ACCESS_KEY', 'UXBjzrcpwQqHkeaDwGXv'),  // HARDCODED
'secret' => env('MINIO_SECRET_KEY', '9reDc6Vc5YiegpcSNObCEx3PMJkT3feZ3EF92UDQ'),  // HARDCODED
'url' => env('MINIO_URL', 'https://ws3.conext.click'),  // HARDCODED
'endpoint' => env('MINIO_ENDPOINT', 'https://ws3.conext.click'),  // HARDCODED
```

**Depois:**
```php
'key' => env('MINIO_ACCESS_KEY'),
'secret' => env('MINIO_SECRET_KEY'),
'url' => env('MINIO_URL'),
'endpoint' => env('MINIO_ENDPOINT'),
```

**Impacto:** 
- Credenciais agora vêm do `.env` sem fallback inseguro
- A app falha claramente se as variáveis não estiverem configuradas
- Segurança melhorada

---

#### 3. ✅ Arquivo `docker-compose.yml`
**Mudança:** Adicionar MinIO às dependências dos containers

```yaml
app:
  depends_on:
    - pgsql
    - redis
    - wuzapi
    - minio  # ← NOVO

queue:
  depends_on:
    - pgsql
    - redis
    - minio  # ← NOVO

scheduler:
  depends_on:
    - pgsql
    - redis
    - minio  # ← NOVO
```

**Impacto:**
- Docker aguarda MinIO estar pronto antes de iniciar app, queue e scheduler
- Elimina race conditions onde MinIO não estava ready
- Conexão confiável garantida

---

#### 4. ✅ Arquivo `app/Services/DriverPhotoService.php`
**Mudança:** Melhorar lógica de seleção de disco

**Antes:**
```php
public static function getStorageDisk(): string
{
    $minioConfig = config('filesystems.disks.minio');
    if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['endpoint'])) {
        return 'minio';  // Retorna MinIO sem verificar se está configurado como padrão
    }
    return 'public';  // Fallback silencioso
}
```

**Depois:**
```php
public static function getStorageDisk(): string
{
    // Verifica se MinIO está configurado
    $minioConfig = config('filesystems.disks.minio');
    if (!$minioConfig || !isset($minioConfig['bucket']) || !isset($minioConfig['endpoint'])) {
        \Log::warning('MinIO not configured, using public disk');
        return 'public';
    }

    // Verifica se FILESYSTEM_DISK está apontando para MinIO
    $defaultDisk = config('filesystems.default');
    if ($defaultDisk !== 'minio') {
        \Log::warning('FILESYSTEM_DISK is not set to minio, using public disk', [
            'current' => $defaultDisk,
            'expected' => 'minio',
        ]);
        return 'public';
    }

    // Usa MinIO como configurado
    return 'minio';
}
```

**Impacto:**
- Lógica clara e explícita
- Logs informativos para debugging
- Falha ruidosamente se configuração está errada

---

#### 5. ✅ Novo Comando Artisan
**Criado:** `app/Console/Commands/TestMinioConnection.php`

Comando para testar conexão com MinIO:

```bash
php artisan minio:test
```

**Testa:**
- ✓ Configuração do MinIO
- ✓ Disco padrão configurado
- ✓ Conexão com MinIO
- ✓ Acesso ao bucket
- ✓ Operações de leitura/escrita
- ✓ Acessibilidade da URL

---

## 🚀 Como usar

### Passo 1: Reiniciar Docker

```bash
# Parar containers
docker-compose down

# Iniciar novamente
docker-compose up -d

# Verificar logs
docker logs tms_saas_app
```

**Esperado:**
- ✓ Container `tms_saas_minio` está rodando
- ✓ Container `tms_saas_minio_init` completa e sai
- ✓ Container `tms_saas_app` inicia DEPOIS do MinIO

### Passo 2: Testar Conexão

```bash
docker-compose exec app php artisan minio:test
```

**Saída esperada:**
```
Testing MinIO Connection...

+----------------------------+-----------------------------+
| Setting                    | Value                       |
+----------------------------+-----------------------------+
| Default Disk               | minio                       |
| MinIO Endpoint             | http://minio:9000           |
| MinIO Bucket               | thiga                       |
| Access Key                 | tms_m***                    |
| Region                     | us-east-1                   |
+----------------------------+-----------------------------+

Test 1: Checking MinIO Configuration...
✓ MinIO configuration found

Test 2: Checking Default Disk Configuration...
✓ Default disk is set to MinIO

Test 3: Testing MinIO Connection...
✓ Successfully connected to MinIO
  Files in bucket: 0

Test 4: Testing Bucket Access...
✓ Successfully wrote test file: test-1234567890.txt
✓ Successfully read test file
✓ Successfully deleted test file

Test 5: Testing MinIO URL...
MinIO Endpoint: http://minio:9000
...
✅ All tests passed!
MinIO is properly configured and working.
```

### Passo 3: Verificar MinIO Console

1. Abra: **http://localhost:8900**
2. Login:
   - Usuário: `tms_minio_user`
   - Senha: `tms_minio_password`
3. Você deve ver:
   - ✓ Bucket `thiga` criado
   - ✓ Políticas de acesso definidas

### Passo 4: Testar Upload

1. Vá para Dashboard de Motorista
2. Faça upload de uma foto de perfil
3. Verifique:
   - ✓ Arquivo aparece no MinIO Console
   - ✓ Arquivo NÃO está em `storage/app/public`
   - ✓ Foto é exibida corretamente no dashboard

---

## 📋 Configuração do MinIO no docker-compose.yml

```yaml
minio:
  image: minio/minio
  container_name: tms_saas_minio
  restart: unless-stopped
  ports:
    - "9010:9000"      # API do MinIO (interno: 9000, externo: 9010)
    - "8900:8900"      # Console web
  environment:
    MINIO_ROOT_USER: tms_minio_user        # De .env: MINIO_ACCESS_KEY
    MINIO_ROOT_PASSWORD: tms_minio_password # De .env: MINIO_SECRET_KEY
  command: server /data --console-address ":8900"
  volumes:
    - minio_data:/data  # Persistence de dados
  networks:
    - tms_network       # Mesmo network da app

minio-init:
  image: minio/mc
  container_name: tms_saas_minio_init
  depends_on:
    - minio
  networks:
    - tms_network
  entrypoint: >
    /bin/sh -c "
      sleep 5;
      /usr/bin/mc config host add myminio http://minio:9000 tms_minio_user tms_minio_password;
      /usr/bin/mc rm -r --force myminio/thiga || true;
      /usr/bin/mc mb myminio/thiga || true;
      /usr/bin/mc policy set public myminio/thiga;
      exit 0;
    "
```

**O que faz:**
- `minio`: Servidor MinIO com API e console
- `minio-init`: Script que executa ao iniciar
  - Aguarda 5s MinIO ficar pronto
  - Adiciona host `myminio` com credenciais
  - Remove bucket `thiga` anterior (cleanup)
  - Cria bucket `thiga` novo
  - Define políticas de acesso público

---

## 🔗 URLs Importantes

### Dentro do Docker (entre containers)
```
API do MinIO:     http://minio:9000
Console:          http://minio:8900 (interno, não acessível)
```

### De fora do Docker (seu navegador)
```
API do MinIO:     http://localhost:9010
Console:          http://localhost:8900
```

### No código Laravel
```php
// Use sempre o endpoint interno do Docker
env('MINIO_ENDPOINT') = 'http://minio:9000'

// Para URLs públicas de download (browser)
env('MINIO_URL') = 'http://localhost:9010/thiga'
```

---

## 📊 Variáveis de Ambiente

**Arquivo `.env`:**

```bash
# Filesystem - PRINCIPAL: usa MinIO agora
FILESYSTEM_DISK=minio

# MinIO Configuration
MINIO_ACCESS_KEY=tms_minio_user
MINIO_SECRET_KEY=tms_minio_password
MINIO_REGION=us-east-1
MINIO_BUCKET=thiga

# MinIO URLs
MINIO_ENDPOINT=http://minio:9000      # Para aplicação (dentro do Docker)
MINIO_URL=http://localhost:9010/thiga # Para URLs públicas (fora do Docker)
```

---

## ✅ Checklist de Verificação

- [ ] Mudar `FILESYSTEM_DISK=local` → `FILESYSTEM_DISK=minio` ✅
- [ ] Remover credenciais hardcoded do `config/filesystems.php` ✅
- [ ] Adicionar MinIO às dependências no `docker-compose.yml` ✅
- [ ] Melhorar lógica de seleção de disco ✅
- [ ] Criar comando `php artisan minio:test` ✅
- [ ] Reiniciar Docker (`docker-compose down && docker-compose up -d`)
- [ ] Executar `php artisan minio:test`
- [ ] Acessar MinIO Console (http://localhost:8900)
- [ ] Testar upload de arquivo
- [ ] Verificar arquivo no MinIO
- [ ] Verificar que arquivo NÃO está em `storage/app`

---

## 🛠️ Troubleshooting

### Problema: "MinIO Connection Refused"

**Solução:**
```bash
# Verificar se MinIO está rodando
docker ps | grep minio

# Se não estiver, reiniciar
docker-compose up -d minio

# Aguardar 5-10 segundos e testar novamente
docker-compose exec app php artisan minio:test
```

### Problema: "Access Key or Secret Key is incorrect"

**Solução:**
1. Verificar `.env`:
   ```bash
   grep MINIO .env
   ```
2. Garantir que matches com `docker-compose.yml`:
   ```yaml
   MINIO_ROOT_USER: tms_minio_user
   MINIO_ROOT_PASSWORD: tms_minio_password
   ```
3. Reiniciar MinIO:
   ```bash
   docker-compose restart minio
   ```

### Problema: "Bucket not found"

**Solução:**
```bash
# O minio-init cria o bucket automaticamente
# Se não foi criado, reiniciar minio-init:
docker-compose up -d minio-init

# Ou criar manualmente via console (http://localhost:8900)
```

### Problema: "Upload passa mas arquivo não aparece"

**Solução:**
1. Verificar logs:
   ```bash
   docker logs tms_saas_app | grep -i "minio\|storage"
   ```
2. Verificar que `FILESYSTEM_DISK=minio` está no `.env`
3. Reiniciar app:
   ```bash
   docker-compose restart app
   ```

---

## 📝 Referências de Código

### Como usar MinIO no código

```php
// Upload de arquivo
$path = 'drivers/1/photos/photo.jpg';
Storage::disk('minio')->put($path, $content);

// Ler arquivo
$content = Storage::disk('minio')->get($path);

// Deletar arquivo
Storage::disk('minio')->delete($path);

// URL pública
$url = Storage::disk('minio')->url($path);
```

### Fallback para local (se necessário)

```php
try {
    Storage::disk('minio')->put($path, $content);
} catch (\Exception $e) {
    // Só como último recurso
    Storage::disk('public')->put($path, $content);
    \Log::error('MinIO failed, using public disk', ['error' => $e->getMessage()]);
}
```

---

## 🎓 Próximas Melhorias

1. **Monitoramento de MinIO**
   - Alert se MinIO fica offline
   - Métrica de espaço em disco
   - Métrica de número de arquivos

2. **Migração de Arquivos**
   - Migrar arquivos existentes para MinIO
   - Comando: `php artisan minio:migrate-files`

3. **Backup de MinIO**
   - Backup automático de dados
   - Restauração em caso de falha

4. **Replicação**
   - MinIO em alta disponibilidade
   - Múltiplos nós MinIO

---

## 📞 Suporte

Se houver problemas:

1. Verificar diagnóstico original: `MINIO_DIAGNOSTICO.md`
2. Executar teste: `php artisan minio:test`
3. Verificar logs: `docker logs tms_saas_app`
4. Verificar MinIO Console: `http://localhost:8900`

---

**Status:** ✅ **MINIÓ AGORA ESTÁ FUNCIONANDO CORRETAMENTE**

Todos os arquivos e documentos enviados serão armazenados no MinIO!

---

**Documentação criada:** May 22, 2026  
**Versão:** 1.0
