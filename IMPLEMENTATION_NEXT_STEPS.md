# ✅ Próximos Passos - Implementação Asaas Multi-Tenant

**Status:** Código Base Criado  
**Data:** May 22, 2026  
**O que foi feito:** 4 Migrations + 5 Models + 3 Services + 2 Controllers + 1 Command + 1 Job

---

## 🎯 O QUE FOI CRIADO ATÉ AGORA

✅ **Migrations (4 arquivos)**
```
database/migrations/
├── 2024_05_22_000001_modify_plans_table_add_split_percentage.php
├── 2024_05_22_000002_modify_tenants_table_add_asaas_config.php
├── 2024_05_22_000003_create_tenant_invoices_table.php
└── 2024_05_22_000004_create_split_billings_table.php
```

✅ **Models (5 arquivos)**
```
app/Models/
├── TenantInvoice.php          [NOVO]
├── SplitBilling.php           [NOVO]
├── Plan.php                   [MODIFICADO]
├── Tenant.php                 [MODIFICADO]
└── (Subscription, User, etc)  [SEM MUDANÇAS]
```

✅ **Services (1 arquivo)**
```
app/Services/
└── TenantInvoiceService.php   [NOVO]
```

✅ **Controllers (2 arquivos)**
```
app/Http/Controllers/
├── Admin/TenantInvoiceController.php    [NOVO]
└── Tenant/AsaasConfigController.php     [NOVO]
```

✅ **Command (1 arquivo)**
```
app/Console/Commands/
└── GenerateTenantMonthlyInvoices.php    [NOVO]
```

✅ **Job (1 arquivo)**
```
app/Jobs/
└── SyncTenantInvoicePayments.php        [NOVO]
```

---

## 🔄 PRÓXIMOS PASSOS (VOCÊ DEVE FAZER)

### PASSO 1: Executar Migrations (5 min)

```bash
# No seu terminal local, no diretório do projeto:
cd /caminho/para/Thiga

# Executar migrations
php artisan migrate

# Verificar que foi criado
php artisan migrate:status
```

**Verificar no banco:**
```bash
# No seu SQL client (pgAdmin, DBeaver, etc):

# 1. Verificar coluna em plans
SELECT * FROM plans LIMIT 1;
-- Deve ter coluna: split_percentage

# 2. Verificar colunas em tenants
SELECT * FROM tenants LIMIT 1;
-- Deve ter colunas: asaas_api_key, asaas_webhook_token, uses_own_asaas

# 3. Verificar tabelas criadas
SELECT * FROM tenant_invoices;      -- Deve estar vazia
SELECT * FROM split_billings;       -- Deve estar vazia
```

---

### PASSO 2: Adicionar Routes (10 min)

**Arquivo:** `routes/web.php` (ou `routes/admin.php` se existir)

**Adicione no final do arquivo:**

```php
// ========== ADMIN ROUTES ==========
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Tenant Invoices
    Route::prefix('tenant-invoices')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TenantInvoiceController::class, 'index'])
            ->name('admin.tenant-invoices.index');
        Route::get('{tenantInvoice}', [\App\Http\Controllers\Admin\TenantInvoiceController::class, 'show'])
            ->name('admin.tenant-invoices.show');
        Route::post('{tenantInvoice}/send', [\App\Http\Controllers\Admin\TenantInvoiceController::class, 'send'])
            ->name('admin.tenant-invoices.send');
        Route::post('{tenantInvoice}/cancel', [\App\Http\Controllers\Admin\TenantInvoiceController::class, 'cancel'])
            ->name('admin.tenant-invoices.cancel');
        Route::get('generate', [\App\Http\Controllers\Admin\TenantInvoiceController::class, 'generate'])
            ->name('admin.tenant-invoices.generate');
    });
});

// ========== TENANT ROUTES ==========
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::prefix('settings')->group(function () {
        Route::get('asaas', [\App\Http\Controllers\Tenant\AsaasConfigController::class, 'edit'])
            ->name('tenant.settings.asaas.edit');
        Route::post('asaas', [\App\Http\Controllers\Tenant\AsaasConfigController::class, 'update'])
            ->name('tenant.settings.asaas.update');
        Route::post('asaas/disconnect', [\App\Http\Controllers\Tenant\AsaasConfigController::class, 'disconnect'])
            ->name('tenant.settings.asaas.disconnect');
    });
});
```

**Verificar:**
```bash
php artisan route:list | grep tenant-invoices
php artisan route:list | grep asaas
```

---

### PASSO 3: Registrar no Scheduler (5 min)

**Arquivo:** `app/Console/Kernel.php`

**Procure o método `schedule()` e adicione:**

```php
protected function schedule(Schedule $schedule)
{
    // ... outras tarefas ...

    // Gerar faturas no primeiro dia de cada mês às 9:00 AM
    $schedule->command('tenant-invoices:generate')
        ->monthlyOn(1, '09:00')
        ->withoutOverlapping();

    // Sincronizar pagamentos do Asaas a cada 4 horas
    $schedule->job(new \App\Jobs\SyncTenantInvoicePayments())
        ->everyFourHours()
        ->withoutOverlapping();
}
```

**Verificar:**
```bash
php artisan schedule:list
```

---

### PASSO 4: Criar as Views (30 min)

Copie os 4 templates do arquivo:
```
ASAAS_VIEWS_TEMPLATES.md
```

E crie os arquivos:

1. **`resources/views/admin/plans/edit.blade.php`**
   - Template #1 do ASAAS_VIEWS_TEMPLATES.md
   - Adiciona split_percentage ao formulário de edição de plano

2. **`resources/views/tenant/settings/asaas.blade.php`**
   - Template #2 do ASAAS_VIEWS_TEMPLATES.md
   - Formulário para tenant configurar seu Asaas

3. **`resources/views/admin/dashboard/revenue.blade.php`**
   - Template #3 do ASAAS_VIEWS_TEMPLATES.md
   - Dashboard com gráficos de receita

4. **`resources/views/admin/invoices/tenant-invoices.blade.php`**
   - Template #4 do ASAAS_VIEWS_TEMPLATES.md
   - Listagem de faturas do tenant

---

### PASSO 5: Modificar Controller de Plans (opcional, 10 min)

Se quiser adicionar validação do split_percentage, modifique:

**Arquivo:** `app/Http/Controllers/Admin/PlanController.php` (ou onde estiver)

**No método `store()` e `update()`, adicione validação:**

```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
    'price' => 'required|numeric|min:0',
    'split_percentage' => 'required|numeric|min:0|max:100',  // ← ADICIONE ESTA LINHA
    'billing_cycle' => 'required|in:monthly,yearly',
    'features' => 'nullable|array',
    'limits' => 'nullable|array',
    'is_active' => 'boolean',
    'is_popular' => 'boolean',
]);
```

---

## 🧪 TESTES BÁSICOS (20 min)

### Teste 1: Verificar Models

```bash
php artisan tinker

# Teste Plan
$plan = \App\Models\Plan::first();
$plan->calculateSplitAmount(1000);  # Deve retornar valor com split
echo $plan->split_percentage;       # Deve retornar número

# Teste Tenant
$tenant = \App\Models\Tenant::first();
$tenant->hasAsaasConfigured();      # Deve retornar boolean

# Teste TenantInvoice
$invoice = \App\Models\TenantInvoice::generateInvoiceNumber();
echo $invoice;  # Deve gerar algo como: TI-2024-05-000001

# Teste SplitBilling
\App\Models\SplitBilling::count();  # Deve retornar 0
```

### Teste 2: Gerar Fatura (Dry Run)

```bash
php artisan tenant-invoices:generate --dry-run --verbose
```

**Esperado:** Mostra quais faturas seriam criadas, mas não cria nada.

### Teste 3: Gerar Fatura Real

```bash
# Se tiver dados de teste
php artisan tenant-invoices:generate --tenant-id=1

# Verificar que foi criado
php artisan tinker
>>> \App\Models\TenantInvoice::latest()->first();
```

### Teste 4: Testar Routes

```bash
# Verificar routes registradas
php artisan route:list | grep tenant

# Acessar manualmente (você precisa estar autenticado):
http://localhost:8000/admin/tenant-invoices
http://localhost:8000/settings/asaas
```

---

## 📝 CHECKLIST DE IMPLEMENTAÇÃO

- [ ] **Passo 1:** Executar migrations com sucesso
- [ ] **Passo 2:** Adicionar routes ao routes/web.php
- [ ] **Passo 3:** Registrar command e job no scheduler
- [ ] **Passo 4:** Criar as 4 views Blade
- [ ] **Passo 5:** (Opcional) Modificar PlanController
- [ ] **Teste 1:** Models funcionam em tinker
- [ ] **Teste 2:** Comando funciona em dry-run
- [ ] **Teste 3:** Comando cria dados reais
- [ ] **Teste 4:** Routes são acessíveis

---

## 🚀 EXECUTAR COMPLETO

```bash
# 1. Migrations
php artisan migrate

# 2. Teste do command
php artisan tenant-invoices:generate --dry-run

# 3. Teste do job (manual)
php artisan tinker
>>> \App\Jobs\SyncTenantInvoicePayments::dispatch();

# 4. Rotas registradas
php artisan route:list | grep tenant

# 5. Scheduler
php artisan schedule:list
```

---

## ⚠️ POSSÍVEIS ERROS & SOLUÇÕES

### Erro: "Class not found"
```bash
# Solução: regenerate autoloader
composer dump-autoload
```

### Erro: "SQLSTATE[42703]: Undefined column"
```bash
# Solução: migrations não executadas
php artisan migrate:status  # Verificar
php artisan migrate        # Executar
```

### Erro: "View not found"
```bash
# Solução: caminhos de views errados
# Verificar se pasta resources/views/admin/invoices existe
mkdir -p resources/views/admin/invoices
mkdir -p resources/views/tenant/settings
```

### Erro: "Route not found"
```bash
# Solução: cache de rotas
php artisan route:clear
php artisan cache:clear
```

---

## 📊 ESTRUTURA FINAL ESPERADA

```
Thiga/
├── database/migrations/
│   ├── 2024_05_22_000001_modify_plans_table_add_split_percentage.php       ✅
│   ├── 2024_05_22_000002_modify_tenants_table_add_asaas_config.php         ✅
│   ├── 2024_05_22_000003_create_tenant_invoices_table.php                  ✅
│   └── 2024_05_22_000004_create_split_billings_table.php                   ✅
│
├── app/Models/
│   ├── TenantInvoice.php                                                    ✅
│   ├── SplitBilling.php                                                     ✅
│   ├── Plan.php                                                             ✅ (modificado)
│   └── Tenant.php                                                           ✅ (modificado)
│
├── app/Services/
│   └── TenantInvoiceService.php                                             ✅
│
├── app/Http/Controllers/
│   ├── Admin/TenantInvoiceController.php                                    ✅
│   └── Tenant/AsaasConfigController.php                                     ✅
│
├── app/Console/Commands/
│   └── GenerateTenantMonthlyInvoices.php                                    ✅
│
├── app/Jobs/
│   └── SyncTenantInvoicePayments.php                                        ✅
│
└── resources/views/
    ├── admin/
    │   ├── plans/
    │   │   └── edit.blade.php                                               ⏳ (copiar)
    │   ├── invoices/
    │   │   └── tenant-invoices.blade.php                                    ⏳ (copiar)
    │   └── dashboard/
    │       └── revenue.blade.php                                            ⏳ (copiar)
    └── tenant/
        └── settings/
            └── asaas.blade.php                                              ⏳ (copiar)
```

---

## 🎓 PRÓXIMOS PASSO APÓS IMPLEMENTAÇÃO

1. **Testes Unitários**: Criar testes para TenantInvoiceService
2. **Testes de Feature**: Testar geração de faturas end-to-end
3. **Webhooks**: Implementar endpoint de webhook para Asaas
4. **Documentação**: Criar guia de usuário para admins e tenants
5. **Monitoring**: Adicionar alertas para faturas overdue

---

**Data:** May 22, 2026  
**Status:** ✅ Código Pronto - Próximo: Integração Local

Quando terminar os próximos passos, poste uma mensagem e faço testes finais com você!
