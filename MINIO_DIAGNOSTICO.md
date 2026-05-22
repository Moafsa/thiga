# Diagnóstico: MinIO não está funcionando corretamente

**Data:** May 22, 2026  
**Status:** ❌ PROBLEMAS IDENTIFICADOS (5 PROBLEMAS CRÍTICOS)

---

## 🔴 PROBLEMAS ENCONTRADOS

### Problema 1: FILESYSTEM_DISK = 'local' (CRÍTICO)

**Localização:** `.env`

```bash
FILESYSTEM_DISK=local
```

**Impacto:** O Laravel está configurado para usar armazenamento **local** em vez de MinIO. Isso significa que TODOS os uploads estão sendo salvos no storage/app local, não no MinIO!

**Evidência:**
- `config/filesystems.php` linha 18: `'default' => env('FILESYSTEM_DISK', 'local')`
- Você vê a variável `FILESYSTEM_DISK=local` no `.env`

---

### Problema 2: URL do MinIO incorreta (CRÍTICO)

**Localização:** `config/filesystems.php` linhas 67-68

```php
'url' => env('MINIO_URL', 'https://ws3.conext.click'),
'endpoint' => env('MINIO_ENDPOINT', 'https://ws3.conext.click'),
```

**Problema:** 
- A configuração padrão aponta para uma URL **externa** (`https://ws3.conext.click`)
- Em vez de apontar para o MinIO **local do Docker** (`http://minio:9000`)
- O arquivo `.env` tem a variável correta, mas a configuração não está usando

**Verificação no .env:**
```bash
MINIO_URL=http://localhost:9010/thiga
MINIO_ENDPOINT=http://minio:9000
```

**O Problema:** 
- `MINIO_URL` deveria ser `http://minio:9000/thiga` (DNS interno do Docker)
- `MINIO_ENDPOINT` está correto (`http://minio:9000`)

---

### Problema 3: Credenciais hardcoded (SEGURANÇA)

**Localização:** `config/filesystems.php` linhas 63-64

```php
'key' => env('MINIO_ACCESS_KEY', 'UXBjzrcpwQqHkeaDwGXv'),  // HARDCODED!
'secret' => env('MINIO_SECRET_KEY', '9reDc6Vc5YiegpcSNObCEx3PMJkT3feZ3EF92UDQ'),  // HARDCODED!
```

**Problema:** 
- Credenciais hardcoded em código público
- Deve usar variáveis de ambiente sem fallback hardcoded
- Isso é uma falha de segurança

---

### Problema 4: Fallback para 'public' disk (CONFIGURAÇÃO ERRADA)

**Localização:** `app/Services/DriverPhotoService.php`

```php
// Linha 18
$disk = self::getStorageDisk();

// Linhas 282-291
public static function getStorageDisk(): string
{
    // Tenta MinIO, mas se falhar usa 'public'
    $minioConfig = config('filesystems.disks.minio');
    if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['endpoint'])) {
        return 'minio';
    }
    return 'public';  // FALLBACK!
}
```

**Impacto:**
- Se MinIO falhar (o que está acontecendo), cai para o disco 'public'
- Os uploads nunca realmente chegam ao MinIO, eles ficam locais
- Os logs mostram warnings de falha no MinIO, depois sucesso no public

---

### Problema 5: docker-compose.yml não liga MinIO aos containers da app

**Localização:** `docker-compose.yml`

```yaml
# Linha 22-25: Dependências da app
depends_on:
  - pgsql
  - redis
  - wuzapi
  # ❌ Falta: MinIO não está aqui!
```

**Problema:**
- O container `app` não tem `depends_on: - minio`
- Isso significa que a app pode iniciar antes do MinIO estar pronto
- A app tenta se conectar ao MinIO que ainda não está rodando
- Falha silenciosa e fallback para 'public'

---

## 🔍 COMPROVAÇÃO DOS PROBLEMAS

### Log de Erro Típico

No código `DriverPhotoService.php`, há tratamento de erro:

```php
catch (\Exception $e) {
    // Fallback to public if MinIO fails
    if ($disk === 'minio') {
        \Log::warning('MinIO upload failed, using public disk fallback', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        try {
            Storage::disk('public')->put($fullPath, $optimizedData);
            $disk = 'public';
```

**Significa:** Os uploads estão falhando no MinIO e caindo para o disco local!

---

## 📋 RESUMO DOS PROBLEMAS

| # | Problema | Severidade | Solução |
|---|----------|-----------|---------|
| 1 | `FILESYSTEM_DISK=local` | 🔴 CRÍTICO | Mudar para `FILESYSTEM_DISK=minio` |
| 2 | URL MinIO incorreta | 🔴 CRÍTICO | Atualizar para `http://minio:9000` no Docker |
| 3 | Credenciais hardcoded | 🟠 ALTO | Usar variáveis de ambiente sem fallback |
| 4 | Fallback para 'public' | 🟠 ALTO | Remover fallback, forçar uso do MinIO |
| 5 | `depends_on` faltando | 🟠 ALTO | Adicionar MinIO às dependências |

---

## 🛠️ COMO CORRIGIR

### Passo 1: Atualizar .env

```bash
# Mudar de:
FILESYSTEM_DISK=local

# Para:
FILESYSTEM_DISK=minio

# Garantir que as URLs estão corretas:
MINIO_ENDPOINT=http://minio:9000
MINIO_URL=http://minio:9000/thiga
MINIO_ACCESS_KEY=tms_minio_user
MINIO_SECRET_KEY=tms_minio_password
MINIO_REGION=us-east-1
MINIO_BUCKET=thiga
```

---

### Passo 2: Atualizar config/filesystems.php

```php
'minio' => [
    'driver' => 's3',
    'key' => env('MINIO_ACCESS_KEY'),  // Remover fallback hardcoded
    'secret' => env('MINIO_SECRET_KEY'),  // Remover fallback hardcoded
    'region' => env('MINIO_REGION', 'us-east-1'),
    'bucket' => env('MINIO_BUCKET', 'thiga'),
    'url' => env('MINIO_URL'),  // Sem fallback hardcoded
    'endpoint' => env('MINIO_ENDPOINT'),  // Sem fallback hardcoded
    'use_path_style_endpoint' => true,
    'throw' => false,
],
```

---

### Passo 3: Atualizar docker-compose.yml

Adicionar `depends_on` para o MinIO:

```yaml
app:
  # ...
  depends_on:
    - pgsql
    - redis
    - wuzapi
    - minio  # ← Adicionar isto
```

---

### Passo 4: Remover fallback em DriverPhotoService.php

**Opção A:** Forçar MinIO (recomendado para produção)

```php
public static function getStorageDisk(): string
{
    // Sempre usar MinIO
    return 'minio';
}
```

**Opção B:** Se MinIO não está ready, usar fallback com warning

```php
public static function getStorageDisk(): string
{
    $minioConfig = config('filesystems.disks.minio');
    if ($minioConfig && isset($minioConfig['bucket']) && isset($minioConfig['endpoint'])) {
        try {
            // Testar conexão
            $endpoint = $minioConfig['endpoint'];
            $bucket = $minioConfig['bucket'];
            
            // Verificar se MinIO está acessível
            $response = @file_get_contents("{$endpoint}/{$bucket}", false, 
                stream_context_create(['http' => ['timeout' => 2]]));
            
            if ($response !== false) {
                return 'minio';
            }
        } catch (\Exception $e) {
            \Log::warning('MinIO not available, using public disk', ['error' => $e->getMessage()]);
        }
    }
    return 'public';
}
```

---

### Passo 5: Remover credenciais hardcoded

**Antes:**
```php
'key' => env('MINIO_ACCESS_KEY', 'UXBjzrcpwQqHkeaDwGXv'),
'secret' => env('MINIO_SECRET_KEY', '9reDc6Vc5YiegpcSNObCEx3PMJkT3feZ3EF92UDQ'),
'url' => env('MINIO_URL', 'https://ws3.conext.click'),
'endpoint' => env('MINIO_ENDPOINT', 'https://ws3.conext.click'),
```

**Depois:**
```php
'key' => env('MINIO_ACCESS_KEY'),
'secret' => env('MINIO_SECRET_KEY'),
'url' => env('MINIO_URL'),
'endpoint' => env('MINIO_ENDPOINT'),
```

---

## ✅ VERIFICAÇÃO PÓS-CORREÇÃO

Depois de aplicar os fixes, testar:

```bash
# 1. Verificar que MinIO está rodando
docker ps | grep minio

# 2. Acessar MinIO Console
# http://localhost:8900
# User: tms_minio_user
# Password: tms_minio_password

# 3. Verificar bucket 'thiga'
# Deve ter bucket 'thiga' criado (feito pelo minio-init)

# 4. Verificar logs da app
docker logs tms_saas_app | grep -i "minio\|storage"

# 5. Testar upload
# Fazer upload de foto de motorista
# Deve aparecer em MinIO, não em storage/app local
```

---

## 🚀 CHECKLIST DE IMPLEMENTAÇÃO

- [ ] Atualizar `.env` com `FILESYSTEM_DISK=minio`
- [ ] Atualizar `config/filesystems.php` - remover hardcoded
- [ ] Atualizar `docker-compose.yml` - adicionar `depends_on`
- [ ] Considerar remover fallback de `DriverPhotoService.php`
- [ ] Testar uploads localmente
- [ ] Verificar MinIO Console
- [ ] Verificar bucket tem arquivos
- [ ] Commit das mudanças
- [ ] Testar em staging
- [ ] Documentar processo

---

**Relatório gerado:** May 22, 2026  
**Próximo passo:** Aplicar as correções acima
