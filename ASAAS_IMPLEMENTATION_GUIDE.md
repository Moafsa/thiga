# 🚀 Guia Completo de Implementação - Asaas Multi-Tenant com Split

**Status:** Guia Passo-a-Passo Completo  
**Data:** May 22, 2026  
**Tempo Estimado:** 2-3 dias de desenvolvimento

---

## 📋 PRÉ-REQUISITOS

Antes de começar, certifique-se que você tem:

- [ ] Laravel 10+ instalado
- [ ] PostgreSQL configurado
- [ ] Composer atualizado
- [ ] Git configurado
- [ ] Conhecimento de Laravel migrations, models, controllers
- [ ] Conta Asaas com API access (para testes)

---

## 🎯 ROTEIRO DE IMPLEMENTAÇÃO

### **FASE 1: Preparação (30 min)**

#### 1.1 - Revisar Arquitetura
```bash
# Leia os 3 documentos de arquitetura
- ASAAS_MULTI_TENANT_SPLIT_ARCHITECTURE.md
- ASAAS_IMPLEMENTATION_CODE_SAMPLES.md
- ASAAS_VIEWS_TEMPLATES.md
```

#### 1.2 - Criar Branch para Trabalho
```bash
git checkout -b feat/asaas-multi-tenant-split
```

#### 1.3 - Verificar Banco de Dados Atual
```bash
php artisan tinker

# Verificar planos
Plan::all();

# Verificar tenants
Tenant::all();

# Verificar subscriptions
Subscription::all();
```

---

### **FASE 2: Migrations (45 min)**

#### 2.1 - Criar Migrations

```bash
# 1. Split em Plans
php artisan make:migration modify_plans_table_add_split_percentage

# 2. Asaas config em Tenants
php artisan make:migration modify_tenants_table_add_asaas_config

# 3. Faturas do SuperAdmin
php artisan make:migration create_tenant_invoices_table

# 4. Tracking de Splits
php artisan make:migration create_split_billings_table
```

#### 2.2 - Copiar Código das Migrations

Copie o código de cada migration do arquivo:
`ASAAS_IMPLEMENTATION_CODE_SAMPLES.md`

Para cada arquivo criado, cole o código correspondente.

#### 2.3 - Executar Migrations

```bash
php artisan migrate

# Verificar que foi criado
php artisan migrate:status
```

#### 2.4 - Verificar Tabelas

```bash
php tinker

# Listar colunas da tabela plans
Schema::getColumnListing('plans');
// Deve incluir: 'split_percentage'

# Listar colunas da tabela tenants
Schema::getColumnListing('tenants');
// Deve incluir: 'asaas_api_key', 'asaas_webhook_token', etc

# Verificar que novas tabelas existem
Schema::hasTable('tenant_invoices');     // true
Schema::hasTable('split_billings');      // true
```

---

### **FASE 3: Models (30 min)**

#### 3.1 - Criar Novos Models

```bash
php artisan make:model TenantInvoice
php artisan make:model SplitBilling
```

#### 3.2 - Copiar Código dos Models

Copie o código de:
- `TenantInvoice` → `app/Models/TenantInvoice.php`
- `SplitBilling` → `app/Models/SplitBilling.php`

De: `ASAAS_IMPLEMENTATION_CODE_SAMPLES.md`

#### 3.3 - Modificar Models Existentes

**Modificar: `app/Models/Plan.php`**

Adicione ao final da classe:
```php
public function calculateSplitAmount($amount): float
{
    return round($amount * ($this->split_percentage / 100), 2);
}

public function getSplitPercentageAttribute(): float
{
    return (float) $this->split_percentage;
}
```

**Modificar: `app/Models/Tenant.php`**

Adicione ao final da classe:
```php
use App\Services\AsaasService;

public function hasAsaasConfigured(): bool
{
    return $this->uses_own_asaas && 
           !empty($this->asaas_api_key) && 
           !empty($this->asaas_webhook_token);
}

public function getAsaasService(): AsaasService
{
    if ($this->hasAsaasConfigured()) {
        return new AsaasService(
            apiKey: $this->asaas_api_key,
            webhookToken: $this->asaas_webhook_token,
            baseUrl: config('services.asaas.api_url'),
            accountType: 'tenant'
        );
    }
    return app(AsaasService::class);
}

public function tenantInvoices()
{
    return $this->hasMany(TenantInvoice::class);
}

public function splitBillings()
{
    return $this->hasMany(SplitBilling::class);
}
```

#### 3.4 - Testar Models

```bash
php tinker

# Testar Plan
$plan = Plan::first();
$plan->calculateSplitAmount(1000);  // Deve retornar valor com split

# Testar Tenant
$tenant = Tenant::first();
$tenant->hasAsaasConfigured();      // Deve retornar boolean

# Testar TenantInvoice
TenantInvoice::all();               // Deve estar vazio inicialmente

# Testar SplitBilling
SplitBilling::all();                // Deve estar vazio inicialmente
```

---

### **FASE 4: Serviços (45 min)**

#### 4.1 - Atualizar AsaasService

**Arquivo:** `app/Services/AsaasService.php`

Modifique o construtor para aceitar parâmetros opcionais:

```php
public function __construct(
    ?string $apiKey = null,
    ?string $webhookToken = null,
    ?string $baseUrl = null,
    ?string $accountType = null
) {
    $this->baseUrl = $baseUrl ?? config('services.asaas.api_url');
    $this->apiKey = $apiKey ?? config('services.asaas.api_key');
    $this->webhookToken = $webhookToken ?? config('services.asaas.webhook_token');
    $this->accountType = $accountType ?? 'superadmin';
    
    $this->validateConfig();
}
```

#### 4.2 - Criar TenantInvoiceService

```bash
php artisan make:service TenantInvoiceService
```

Copie o código completo de `ASAAS_IMPLEMENTATION_CODE_SAMPLES.md`

#### 4.3 - Testar Services

```bash
php tinker

# Testar AsaasService instanciação
$service = new \App\Services\AsaasService();
$service;  // Deve retornar objeto sem erro

# Testar com credenciais customizadas
$service2 = new \App\Services\AsaasService(
    apiKey: 'test_key',
    webhookToken: 'test_webhook'
);
$service2;  // Deve retornar objeto
```

---

### **FASE 5: Controllers & Routes (45 min)**

#### 5.1 - Criar/Modificar Controllers

```bash
# Modificar plano controller
php artisan make:controller Admin/PlanController --model=Plan

# Criar config Asaas para tenant
php artisan make:controller Tenant/AsaasConfigController

# Criar admin tenant invoices controller
php artisan make:controller Admin/TenantInvoiceController
```

#### 5.2 - Adicionar Rotas

**Em: `routes/admin.php` (se não existir, criar em `routes/web.php`)**

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Plans com split
    Route::resource('plans', PlanController::class);
    
    // Tenant invoices
    Route::prefix('tenant-invoices')->group(function () {
        Route::get('/', [TenantInvoiceController::class, 'index'])->name('tenant-invoices.index');
        Route::get('{tenantInvoice}', [TenantInvoiceController::class, 'show'])->name('tenant-invoices.show');
        Route::post('{tenantInvoice}/send', [TenantInvoiceController::class, 'send'])->name('tenant-invoices.send');
        Route::post('{tenantInvoice}/cancel', [TenantInvoiceController::class, 'cancel'])->name('tenant-invoices.cancel');
        Route::post('generate', [TenantInvoiceController::class, 'generate'])->name('tenant-invoices.generate');
    });
    
    // Dashboard com revenue
    Route::get('dashboard/revenue', [DashboardController::class, 'revenue'])->name('dashboard.revenue');
});

// Rotas do Tenant
Route::middleware(['auth', 'tenant'])->prefix('tenant')->group(function () {
    Route::prefix('settings')->group(function () {
        Route::get('asaas', [AsaasConfigController::class, 'edit'])->name('tenant.settings.asaas.edit');
        Route::post('asaas', [AsaasConfigController::class, 'update'])->name('tenant.settings.asaas.update');
    });
});
```

#### 5.3 - Implementar Controllers

**Arquivo: `app/Http/Controllers/Admin/PlanController.php`**

Adicione o método `update`:

```php
public function update(Request $request, Plan $plan)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'split_percentage' => 'required|numeric|min:0|max:100',  // NOVO
        'billing_cycle' => 'required|in:monthly,yearly',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'features' => 'nullable|array',
        'limits' => 'nullable|array',
    ]);
    
    $plan->update($validated);
    
    return redirect()->route('admin.plans.index')
        ->with('success', "Plan updated. Split: {$plan->split_percentage}%");
}
```

**Arquivo: `app/Http/Controllers/Tenant/AsaasConfigController.php`**

```php
<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\AsaasService;
use Illuminate\Http\Request;

class AsaasConfigController extends Controller
{
    public function edit()
    {
        $tenant = auth()->user()->tenant;
        return view('tenant.settings.asaas', ['tenant' => $tenant]);
    }

    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        $validated = $request->validate([
            'uses_own_asaas' => 'boolean',
            'asaas_api_key' => 'nullable|string|min:10',
            'asaas_webhook_token' => 'nullable|string|min:10',
            'asaas_account_id' => 'nullable|string',
            'bank_account_config' => 'nullable|array',
        ]);
        
        // Se quer usar seu próprio Asaas, validar credenciais
        if ($validated['uses_own_asaas'] ?? false) {
            try {
                $asaasService = new AsaasService(
                    apiKey: $validated['asaas_api_key'],
                    webhookToken: $validated['asaas_webhook_token']
                );
                // Testar conexão - se houver um método getAccount
                // $asaasService->validateConnection();
            } catch (\Exception $e) {
                return back()->withErrors(['asaas_api_key' => 'Credenciais Asaas inválidas: ' . $e->getMessage()]);
            }
        }
        
        $tenant->update($validated);
        
        return back()->with('success', 'Configuração Asaas atualizada com sucesso');
    }
}
```

---

### **FASE 6: Commands & Jobs (30 min)**

#### 6.1 - Criar Command

```bash
php artisan make:command GenerateTenantMonthlyInvoices
```

Copie o código completo de `ASAAS_IMPLEMENTATION_CODE_SAMPLES.md`

#### 6.2 - Criar Job

```bash
php artisan make:job SyncTenantInvoicePayments
```

Copie o código completo de `ASAAS_IMPLEMENTATION_CODE_SAMPLES.md`

#### 6.3 - Registrar Scheduler

**Em: `app/Console/Kernel.php`**

```php
protected function schedule(Schedule $schedule)
{
    // ... outras tarefas
    
    // Gerar faturas no primeiro dia de cada mês às 9:00
    $schedule->command('tenant-invoices:generate')
        ->monthlyOn(1, '09:00');
    
    // Sincronizar pagamentos a cada 4 horas
    $schedule->job(new \App\Jobs\SyncTenantInvoicePayments())
        ->everyFourHours();
}
```

#### 6.4 - Testar Commands

```bash
# Teste em modo dry-run (não cria dados)
php artisan tenant-invoices:generate --dry-run

# Teste para um tenant específico
php artisan tenant-invoices:generate --tenant-id=1

# Teste com output
php artisan tenant-invoices:generate --verbose
```

---

### **FASE 7: Views (30 min)**

#### 7.1 - Criar Estrutura de Views

```bash
mkdir -p resources/views/admin/invoices
mkdir -p resources/views/admin/dashboard
mkdir -p resources/views/tenant/settings
```

#### 7.2 - Copiar Views

Crie os arquivos e copie o código de `ASAAS_VIEWS_TEMPLATES.md`:

1. `resources/views/admin/plans/edit.blade.php` (template #1)
2. `resources/views/tenant/settings/asaas.blade.php` (template #2)
3. `resources/views/admin/dashboard/revenue.blade.php` (template #3)
4. `resources/views/admin/invoices/tenant-invoices.blade.php` (template #4)

#### 7.3 - Ajustar Imports CSS/JS

Se usar Tailwind, Bootstrap ou outro framework, ajuste os nomes de classes.

---

### **FASE 8: Testes (1 hora)**

#### 8.1 - Testes Manuais

```bash
# 1. Verificar dados no banco
php tinker

>>> Plan::first()->split_percentage
// Deve retornar decimal

>>> Tenant::first()->hasAsaasConfigured()
// Deve retornar false inicialmente

# 2. Testar geração de fatura
>>> use App\Services\TenantInvoiceService;
>>> use App\Models\Subscription;
>>> 
>>> $subscription = Subscription::first();
>>> $service = new TenantInvoiceService();
>>> $invoice = $service->generateMonthlyInvoice($subscription);
>>> $invoice;

# 3. Verificar que SplitBilling foi criado
>>> SplitBilling::where('tenant_invoice_id', $invoice->id)->first();
```

#### 8.2 - Testes de Interface

Acesse as rotas manualmente e verifique:

```bash
# Admin - Edit Plan (com split)
http://localhost:8000/admin/plans/1/edit

# Tenant - Asaas Config
http://localhost:8000/tenant/settings/asaas

# Admin - Revenue Dashboard
http://localhost:8000/admin/dashboard/revenue

# Admin - Tenant Invoices
http://localhost:8000/admin/tenant-invoices
```

#### 8.3 - Criar Testes Automatizados

```bash
php artisan make:test TenantInvoiceServiceTest
php artisan make:test AsaasConfigControllerTest
```

---

### **FASE 9: Documentação & Deploy (30 min)**

#### 9.1 - Documentação de Usuário

Crie um guia para os admins:
- Como configurar planos com split
- Como gerar faturas manualmente
- Como acompanhar receitas

#### 9.2 - Preparar para Deploy

```bash
# Limpar caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Otimizar para produção
php artisan optimize

# Commit das mudanças
git add .
git commit -m "feat: implement asaas multi-tenant with split billing

- Add split_percentage to plans
- Add asaas config to tenants
- Create TenantInvoice and SplitBilling models
- Implement TenantInvoiceService
- Add monthly invoice generation command
- Add revenue dashboard
- Add tenant asaas configuration UI"

git push origin feat/asaas-multi-tenant-split
```

#### 9.3 - Criar Pull Request

```bash
# No GitHub, criar PR com descrição completa
# Link para esta documentação
# Listar todos os arquivos modificados
# Descrever testes realizados
```

---

## 📊 DIAGRAMA DE FLUXOS

### Fluxo 1: Tenant Se Inscreve em Plano

```
┌─────────────────────────────────────────────────────┐
│ 1. Tenant seleciona plano (ex: Professional)        │
│    ├─ Price: R$ 299                                 │
│    └─ Split: 10%                                    │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 2. Sistema cria Subscription                        │
│    ├─ tenant_id: 1                                  │
│    ├─ plan_id: 1                                    │
│    ├─ status: active                                │
│    └─ amount: 299.00                                │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 3. Webhook: subscription.created                    │
│    └─ Scheduler irá gerar fatura em 1 mês           │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 4. No 1º dia do mês:                                │
│    Comando artisan gera TenantInvoice               │
│    ├─ base_amount: 299.00                           │
│    ├─ split_amount: 29.90 (10%)                     │
│    ├─ total_amount: 299.00                          │
│    └─ status: draft                                 │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 5. Fatura enviada para Asaas                        │
│    ├─ Cria payment no Asaas                         │
│    ├─ customer_id: tenant.asaas_customer_id         │
│    ├─ amount: 299.00                                │
│    └─ status: issued                                │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 6. Tenant recebe boleto via Asaas                   │
│    └─ Due date: 10 dias                             │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 7. Webhook: payment.received                        │
│    ├─ TenantInvoice status: paid                    │
│    ├─ SplitBilling status: paid                     │
│    └─ SuperAdmin recebe R$ 29.90 de comissão       │
└─────────────────────────────────────────────────────┘
```

### Fluxo 2: Tenant Configura Seu Asaas

```
┌─────────────────────────────────────────────────────┐
│ 1. Tenant acessa Settings → Asaas                   │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 2. Preenche credenciais:                            │
│    ├─ API Key: sk_live_...                          │
│    ├─ Webhook Token: token_...                      │
│    └─ Account ID: acc_...                           │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 3. Sistema valida credenciais                       │
│    └─ Se inválido: erro, não salva                  │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 4. Salva em Tenant:                                 │
│    ├─ asaas_api_key: encrypted                      │
│    ├─ asaas_webhook_token: encrypted                │
│    └─ uses_own_asaas: true                          │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 5. De agora em diante:                              │
│    └─ Invoices de clientes usam Asaas do Tenant     │
│       (não do SuperAdmin)                           │
└─────────────────────────────────────────────────────┘
```

### Fluxo 3: Tenant Cobra Cliente (Com Seu Asaas)

```
┌─────────────────────────────────────────────────────┐
│ 1. Tenant cria Invoice para Cliente                 │
│    ├─ amount: R$ 500                                │
│    └─ status: draft                                 │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 2. Sistema detecta:                                 │
│    └─ Tenant.hasAsaasConfigured() = true            │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 3. Cria payment via Asaas do TENANT                 │
│    ├─ Usa tenant.asaas_api_key                      │
│    ├─ Usa tenant.getAsaasService()                  │
│    └─ customer: client.asaas_customer_id            │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 4. Cliente recebe boleto via Asaas do Tenant        │
│    └─ Empresa de cobrança: Tenant's Account         │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 5. Webhook (do Tenant) recebe payment               │
│    ├─ Invoice status: paid                          │
│    └─ Tenant recebeRR$ 500 em sua conta Asaas       │
└──────────────────┬──────────────────────────────────┘
                   ↓
┌─────────────────────────────────────────────────────┐
│ 6. SuperAdmin já cobra o Tenant pelo plano          │
│    └─ Não interfere nessa cobrança do cliente       │
└─────────────────────────────────────────────────────┘
```

---

## 🧪 CHECKLIST DE TESTE FINAL

Antes de fazer merge e deploy:

- [ ] **Migrations** - Todas executadas sem erro
- [ ] **Models** - Todos os models criam/salvam corretamente
- [ ] **Services** - TenantInvoiceService funciona
- [ ] **Commands** - Gera faturas corretamente
- [ ] **Controllers** - Salvam configurações
- [ ] **Views** - Layouts renderizam sem erro
- [ ] **Dados** - Split é calculado corretamente
- [ ] **Segurança** - Credenciais são encriptadas
- [ ] **Webhooks** - Recebem e processam pagamentos
- [ ] **Isolamento** - Tenant A não vê dados de Tenant B
- [ ] **UI** - Forms validam inputs
- [ ] **Performance** - Sem query N+1

---

## 🚨 TROUBLESHOOTING

### Erro: "SQLSTATE[42703]: Undefined column"

**Solução:** Migrations não foram executadas
```bash
php artisan migrate:reset
php artisan migrate
```

### Erro: "Class not found"

**Solução:** Use o autoloader do Composer
```bash
composer dump-autoload
```

### Credenciais Asaas Inválidas

**Solução:** Verifique a conta Asaas
```bash
# No Asaas Dashboard:
# 1. Vá em API Integration
# 2. Copie o token exato (sem espaços)
# 3. Teste em Tinker
```

### Split Não é Calculado

**Solução:** Verifique migration
```bash
php artisan tinker
>>> \Schema::getColumnListing('plans');
// Deve incluir 'split_percentage'
```

---

## 📞 SUPORTE & REFERÊNCIAS

- Documentação Asaas: https://docs.asaas.com
- Laravel Migrations: https://laravel.com/docs/10.x/migrations
- Laravel Services: https://laravel.com/docs/10.x/service-container

---

**Guia completo criado:** May 22, 2026  
**Status:** ✅ Pronto para Implementação Imediata
