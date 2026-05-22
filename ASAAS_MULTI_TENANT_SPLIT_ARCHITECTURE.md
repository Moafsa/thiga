# 🏗️ Arquitetura Multi-Tenant Asaas com Split de Faturamento

**Status:** Análise & Arquitetura Proposta  
**Data:** May 22, 2026  
**Escopo:** Sistema de faturamento em 2 níveis com split automático

---

## 📊 DIAGNÓSTICO DA ESTRUTURA ATUAL

### ✅ O que Existe

```
Current Models:
├── Plan
│   ├── name, description
│   ├── price, billing_cycle
│   ├── features[], limits[]
│   └── is_active, is_popular
│
├── Tenant
│   ├── name, cnpj, domain
│   ├── asaas_customer_id (SÓ UM, do superadmin)
│   ├── plan_id
│   ├── subscription_status
│   └── email_config[]
│
├── Subscription
│   ├── tenant_id, plan_id
│   ├── asaas_subscription_id
│   ├── asaas_customer_id
│   ├── amount, billing_cycle
│   ├── status (active|trial|cancelled)
│   └── features[], limits[]
│
├── Invoice (para clientes do tenant)
│   ├── tenant_id, client_id
│   ├── invoice_number, issue_date, due_date
│   ├── subtotal, tax_amount, total_amount
│   ├── status (open|paid|overdue)
│   └── metadata[]
│
└── Payment
    ├── subscription_id, invoice_id
    ├── asaas_payment_id
    ├── amount, status
    ├── due_date, paid_at
    └── asaas_response[]

Current Services:
└── AsaasService
    ├── createCustomer()
    ├── createSubscription()
    ├── getSubscription()
    ├── getPayment()
    └── processWebhook() ← Funciona com uma única API key
```

### 🔴 O que FALTA

```
❌ Split percentage em planos
❌ Configuração de Asaas por tenant (cada tenant com sua API key)
❌ Modelo para faturas do superadmin aos tenants
❌ Modelo para rastreamento de split/comissão
❌ Sistema de auto-geração de faturas
❌ Suporte a múltiplas contas Asaas (AsaasService acoplado)
❌ Webhooks para múltiplas contas Asaas
❌ Dashboard de receita do superadmin
❌ Sistema de relatórios de split
```

---

## 🎯 ARQUITETURA PROPOSTA

### Visão Geral: Dois Níveis de Faturamento

```
┌─────────────────────────────────────────────────────────────────┐
│                       SUPER ADMIN                               │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Asaas Account (API Key do SuperAdmin)                         │
│  └─ Cobra tenants pelos planos que eles contratam              │
│                                                                 │
│  Plans com Split Percentage:                                   │
│  ├─ Plan: Básico     → R$ 99/mês  → Split: 0% (não cobra)     │
│  ├─ Plan: Profissional → R$ 299/mês → Split: 10%              │
│  └─ Plan: Enterprise → R$ 999/mês → Split: 15%                │
│                                                                 │
│  Faturas geradas automaticamente:                              │
│  Invoice#1: Tenant A - R$ 299 (Profissional) ← +10% split     │
│  Invoice#2: Tenant B - R$ 999 (Enterprise)   ← +15% split     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
                      [SuperAdmin receita]
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                     TENANT (Transportadora)                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Asaas Account Próprio (API Key do Tenant - OPCIONAL)          │
│  ├─ asaas_api_key: "key_xyz..."                                │
│  ├─ asaas_webhook_token: "webhook_xyz..."                      │
│  └─ asaas_account_id: "acc_xyz..."                             │
│                                                                 │
│  Cobra seus clientes (se usar Asaas):                          │
│  ├─ Invoice: Cliente A - R$ 500 (frete)                        │
│  ├─ Invoice: Cliente B - R$ 750 (serviço)                      │
│  └─ [Asaas processa pagamentos do tenant]                      │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🗄️ MIGRATIONS & MODELS NOVOS/MODIFICADOS

### 1. Modificar `Plan` - Adicionar Split Percentage

```php
// Migration: modify_plans_table_add_split_percentage
Schema::table('plans', function (Blueprint $table) {
    $table->decimal('split_percentage', 5, 2)->default(0)->after('price');
    // 0 = sem split, 10.5 = 10,5% de comissão para o superadmin
    // sobre cada fatura cobrada do tenant
});

// Model: Plan.php
class Plan extends Model {
    protected $fillable = [
        'name', 'description', 'price', 'billing_cycle',
        'split_percentage',  // ← NOVO
        'features', 'limits', 'is_active', 'is_popular'
    ];
    
    protected $casts = [
        'split_percentage' => 'decimal:2',  // ← Novo cast
    ];
    
    /**
     * Calcular comissão do superadmin baseada no valor
     */
    public function calculateSplitAmount($amount): float
    {
        return $amount * ($this->split_percentage / 100);
    }
}
```

---

### 2. Modificar `Tenant` - Adicionar Asaas do Tenant

```php
// Migration: modify_tenants_table_add_asaas_config
Schema::table('tenants', function (Blueprint $table) {
    // Asaas do Tenant (opcional, para cobrar seus clientes)
    $table->string('asaas_api_key')->nullable()->after('asaas_customer_id');
    $table->string('asaas_webhook_token')->nullable()->after('asaas_api_key');
    $table->string('asaas_account_id')->nullable()->after('asaas_webhook_token');
    
    // Flags para saber qual Asaas usar
    $table->boolean('uses_own_asaas')->default(false);
    
    // Informações bancárias para recebimento (se usando Asaas)
    $table->json('bank_account_config')->nullable();
});

// Model: Tenant.php
class Tenant extends Model {
    protected $fillable = [
        'name', 'cnpj', 'domain', 'api_token',
        'asaas_customer_id',   // ← Do superadmin (para cobrança de planos)
        'asaas_api_key',       // ← NOVO (do tenant, para cobrar seus clientes)
        'asaas_webhook_token', // ← NOVO
        'asaas_account_id',    // ← NOVO
        'uses_own_asaas',      // ← NOVO
        'bank_account_config', // ← NOVO
        'plan_id', 'is_active', 'trial_ends_at',
        'subscription_status', 'email_provider', 'email_config',
        'send_proposal_by_email', 'send_proposal_by_whatsapp'
    ];
    
    protected $casts = [
        'uses_own_asaas' => 'boolean',
        'bank_account_config' => 'array',
    ];
    
    /**
     * Verificar se o tenant configurou seu próprio Asaas
     */
    public function hasAsaasConfigured(): bool
    {
        return $this->uses_own_asaas && 
               $this->asaas_api_key && 
               $this->asaas_webhook_token;
    }
    
    /**
     * Obter o serviço Asaas apropriado (superadmin ou tenant)
     */
    public function getAsaasService(): AsaasService
    {
        if ($this->hasAsaasConfigured()) {
            // Usa o Asaas do tenant
            return new AsaasService(
                apiKey: $this->asaas_api_key,
                webhookToken: $this->asaas_webhook_token
            );
        }
        
        // Usa o Asaas do superadmin
        return app(AsaasService::class);
    }
}
```

---

### 3. Novo Model: `TenantInvoice` - Faturas do SuperAdmin aos Tenants

```php
// Migration: create_tenant_invoices_table
Schema::create('tenant_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();
    $table->foreignId('subscription_id')->nullable()->constrained();
    $table->string('invoice_number')->unique();
    $table->enum('type', ['plan_subscription', 'usage_overage'])->default('plan_subscription');
    
    // Valores
    $table->decimal('base_amount', 12, 2);       // Valor do plano
    $table->decimal('split_percentage', 5, 2);   // Percentual do split
    $table->decimal('split_amount', 12, 2);      // Comissão do superadmin
    $table->decimal('total_amount', 12, 2);      // base_amount
    
    // Datas
    $table->date('period_start');  // Período cobrado (ex: 2024-05-01)
    $table->date('period_end');    // até 2024-05-31
    $table->date('issue_date');
    $table->date('due_date');
    
    // Status
    $table->enum('status', ['draft', 'issued', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
    
    // Referências Asaas
    $table->string('asaas_invoice_id')->nullable();
    $table->string('asaas_payment_id')->nullable();
    
    // Metadata
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['status', 'due_date']);
});

// Model: TenantInvoice.php
class TenantInvoice extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'tenant_id', 'subscription_id', 'invoice_number', 'type',
        'base_amount', 'split_percentage', 'split_amount', 'total_amount',
        'period_start', 'period_end', 'issue_date', 'due_date',
        'status', 'asaas_invoice_id', 'asaas_payment_id', 'metadata'
    ];
    
    protected $casts = [
        'base_amount' => 'decimal:2',
        'split_percentage' => 'decimal:2',
        'split_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'issue_date' => 'date',
        'due_date' => 'date',
        'metadata' => 'array',
    ];
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
    
    /**
     * Gerar número único para a nota fiscal
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $lastInvoice = self::where('invoice_number', 'like', "TI-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('TI-%s-%06d', $year, $newNumber);
    }
    
    /**
     * Marcar como paga
     */
    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }
    
    /**
     * Verificar se é overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'open' && $this->due_date->isPast();
    }
}
```

---

### 4. Novo Model: `SplitBilling` - Rastreamento de Comissões

```php
// Migration: create_split_billings_table
Schema::create('split_billings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained();
    $table->foreignId('tenant_invoice_id')->nullable()->constrained('tenant_invoices');
    
    // Referências
    $table->string('reference_type');  // 'plan_subscription', 'invoice_payment', 'usage'
    $table->unsignedBigInteger('reference_id');
    
    // Valores
    $table->decimal('base_amount', 12, 2);
    $table->decimal('split_percentage', 5, 2);
    $table->decimal('commission_amount', 12, 2);  // Comissão do superadmin
    
    // Status
    $table->enum('status', ['pending', 'calculated', 'invoiced', 'paid'])->default('pending');
    $table->date('calculation_date');
    $table->date('invoice_date')->nullable();
    $table->date('payment_date')->nullable();
    
    // Metadata
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['tenant_id', 'status']);
    $table->index(['reference_type', 'reference_id']);
});

// Model: SplitBilling.php
class SplitBilling extends Model
{
    protected $fillable = [
        'tenant_id', 'tenant_invoice_id', 'reference_type', 'reference_id',
        'base_amount', 'split_percentage', 'commission_amount',
        'status', 'calculation_date', 'invoice_date', 'payment_date', 'metadata'
    ];
    
    protected $casts = [
        'base_amount' => 'decimal:2',
        'split_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'calculation_date' => 'date',
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'metadata' => 'array',
    ];
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function tenantInvoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class);
    }
}
```

---

## 🔧 SERVIÇOS A CRIAR/MODIFICAR

### 1. Refatorar `AsaasService` - Suportar Múltiplas Contas

```php
// app/Services/AsaasService.php
class AsaasService
{
    private string $baseUrl;
    private string $apiKey;
    private string $webhookToken;
    private ?string $accountType;  // 'superadmin' ou 'tenant'
    
    /**
     * Construtor flexível - pode ser instanciado com credenciais específicas
     */
    public function __construct(
        ?string $apiKey = null,
        ?string $webhookToken = null,
        ?string $baseUrl = null,
        ?string $accountType = null
    ) {
        // Se credenciais não fornecidas, usa as do config (superadmin)
        $this->baseUrl = $baseUrl ?? config('services.asaas.api_url');
        $this->apiKey = $apiKey ?? config('services.asaas.api_key');
        $this->webhookToken = $webhookToken ?? config('services.asaas.webhook_token');
        $this->accountType = $accountType ?? 'superadmin';
        
        $this->validateConfig();
    }
    
    private function validateConfig(): void
    {
        if (!$this->apiKey || !$this->baseUrl) {
            throw new \Exception("Asaas API Key or Base URL not configured");
        }
    }
    
    /**
     * Criar cliente no Asaas
     * Usado pelo superadmin para criar cliente (tenant)
     * Ou pelo tenant para criar cliente próprio
     */
    public function createCustomer(array $customerData): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/customers', $customerData);
        
        if ($response->successful()) {
            Log::info("Asaas customer created", [
                'account_type' => $this->accountType,
                'customer_id' => $response->json()['id'] ?? null
            ]);
            return $response->json();
        }
        
        Log::error("Asaas customer creation failed", [
            'account_type' => $this->accountType,
            'data' => $customerData,
            'response' => $response->body()
        ]);
        
        throw new \Exception('Failed to create customer in Asaas');
    }
    
    /**
     * Criar invoice no Asaas
     * Usado para cobrar tanto o tenant (superadmin) quanto clientes (tenant)
     */
    public function createInvoice(array $invoiceData): array
    {
        $response = Http::withHeaders([
            'access_token' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/invoices', $invoiceData);
        
        if ($response->successful()) {
            Log::info("Asaas invoice created", [
                'account_type' => $this->accountType,
                'invoice_id' => $response->json()['id'] ?? null
            ]);
            return $response->json();
        }
        
        Log::error("Asaas invoice creation failed", [
            'account_type' => $this->accountType,
            'data' => $invoiceData,
            'response' => $response->body()
        ]);
        
        throw new \Exception('Failed to create invoice in Asaas');
    }
    
    // ... outros métodos (getSubscription, cancelSubscription, etc)
    
    /**
     * Verificar se webhook é do superadmin ou tenant
     */
    public function verifyWebhookSignature(string $signature, string $payload): bool
    {
        if (!$this->webhookToken) {
            Log::warning("Webhook token not configured", [
                'account_type' => $this->accountType
            ]);
            return false;
        }
        
        $expectedSignature = hash_hmac('sha256', $payload, $this->webhookToken);
        return hash_equals($expectedSignature, $signature);
    }
}
```

---

### 2. Novo Serviço: `TenantInvoiceService` - Gerar Faturas Automaticamente

```php
// app/Services/TenantInvoiceService.php
class TenantInvoiceService
{
    /**
     * Gerar fatura do superadmin para o tenant baseado no plano
     */
    public function generatePlanSubscriptionInvoice(Subscription $subscription): TenantInvoice
    {
        $tenant = $subscription->tenant;
        $plan = $subscription->plan;
        
        // Criar fatura
        $invoice = TenantInvoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => TenantInvoice::generateInvoiceNumber(),
            'type' => 'plan_subscription',
            
            // Valores
            'base_amount' => $plan->price,
            'split_percentage' => $plan->split_percentage,
            'split_amount' => $plan->calculateSplitAmount($plan->price),
            'total_amount' => $plan->price,
            
            // Período (próximo mês)
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'issue_date' => now(),
            'due_date' => now()->addDays(10),
            
            'status' => 'drafted',
            'metadata' => [
                'plan_name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
            ]
        ]);
        
        // Registrar split billing
        SplitBilling::create([
            'tenant_id' => $tenant->id,
            'tenant_invoice_id' => $invoice->id,
            'reference_type' => 'plan_subscription',
            'reference_id' => $subscription->id,
            'base_amount' => $plan->price,
            'split_percentage' => $plan->split_percentage,
            'commission_amount' => $plan->calculateSplitAmount($plan->price),
            'status' => 'calculated',
            'calculation_date' => now()->toDateString(),
        ]);
        
        return $invoice;
    }
    
    /**
     * Enviar fatura para Asaas (criar payment no Asaas)
     */
    public function sendToAsaas(TenantInvoice $tenantInvoice): array
    {
        $asaasService = new AsaasService();  // Superadmin
        
        $paymentData = [
            'customer' => $tenantInvoice->tenant->asaas_customer_id,
            'billingType' => 'BOLETO',  // ou outro tipo
            'value' => (float) $tenantInvoice->total_amount,
            'dueDate' => $tenantInvoice->due_date->format('Y-m-d'),
            'description' => "Plan: {$tenantInvoice->metadata['plan_name']}",
            'externalReference' => $tenantInvoice->invoice_number,
        ];
        
        $response = $asaasService->createPayment($paymentData);
        
        // Atualizar referência
        $tenantInvoice->update([
            'asaas_payment_id' => $response['id'] ?? null,
            'status' => 'issued',
        ]);
        
        Log::info("Tenant invoice sent to Asaas", [
            'tenant_id' => $tenantInvoice->tenant_id,
            'invoice_number' => $tenantInvoice->invoice_number,
            'asaas_payment_id' => $response['id'] ?? null,
        ]);
        
        return $response;
    }
    
    /**
     * Processar webhook de pagamento do tenant
     * Quando superadmin recebe o pagamento
     */
    public function processPaymentWebhook(array $webhookData): void
    {
        $paymentId = $webhookData['payment']['id'] ?? null;
        if (!$paymentId) return;
        
        $tenantInvoice = TenantInvoice::where('asaas_payment_id', $paymentId)->first();
        if (!$tenantInvoice) return;
        
        $event = $webhookData['event'] ?? null;
        
        switch ($event) {
            case 'PAYMENT_CONFIRMED':
            case 'PAYMENT_RECEIVED':
                $tenantInvoice->markAsPaid();
                
                // Atualizar split billing
                SplitBilling::where('tenant_invoice_id', $tenantInvoice->id)
                    ->update(['status' => 'paid', 'payment_date' => now()]);
                
                Log::info("Tenant invoice paid", [
                    'invoice_number' => $tenantInvoice->invoice_number,
                ]);
                break;
                
            case 'PAYMENT_OVERDUE':
                $tenantInvoice->update(['status' => 'overdue']);
                break;
        }
    }
}
```

---

### 3. Novo Serviço: `MultiAccountAsaasService` - Gerenciar Múltiplas Contas

```php
// app/Services/MultiAccountAsaasService.php
class MultiAccountAsaasService
{
    /**
     * Gerar fatura de cliente para tenant (se tenant usa seu Asaas)
     */
    public function createClientInvoice(Invoice $invoice): ?array
    {
        $tenant = $invoice->tenant;
        
        // Se tenant não tem Asaas configurado, usa sistema interno
        if (!$tenant->hasAsaasConfigured()) {
            Log::info("Tenant does not have Asaas configured, using internal invoicing", [
                'invoice_id' => $invoice->id
            ]);
            return null;
        }
        
        // Obter serviço Asaas do tenant
        $asaasService = $tenant->getAsaasService();
        
        // Preparar dados
        $clientAsaasId = $invoice->client->asaas_customer_id;
        if (!$clientAsaasId) {
            Log::warning("Client does not have Asaas ID", [
                'client_id' => $invoice->client_id
            ]);
            return null;
        }
        
        $paymentData = [
            'customer' => $clientAsaasId,
            'billingType' => 'BOLETO',
            'value' => (float) $invoice->total_amount,
            'dueDate' => $invoice->due_date->format('Y-m-d'),
            'description' => "Invoice {$invoice->invoice_number}",
            'externalReference' => $invoice->invoice_number,
        ];
        
        try {
            $response = $asaasService->createPayment($paymentData);
            
            // Atualizar invoice
            $invoice->update([
                'metadata' => array_merge($invoice->metadata ?? [], [
                    'asaas_payment_id' => $response['id'] ?? null,
                ])
            ]);
            
            Log::info("Client invoice created in Asaas via tenant account", [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenant->id,
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error("Failed to create client invoice in Asaas", [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
```

---

## 📋 MIGRATIONS

### Migrations Resumidas

```php
// 1. Modificar planos
php artisan make:migration modify_plans_table_add_split_percentage

// 2. Modificar tenants
php artisan make:migration modify_tenants_table_add_asaas_config

// 3. Criar faturas do superadmin aos tenants
php artisan make:migration create_tenant_invoices_table

// 4. Criar rastreamento de split
php artisan make:migration create_split_billings_table

// Executar
php artisan migrate
```

---

## 🎮 CONTROLLERS & ROTAS

### 1. SuperAdmin: Configurar Planos com Split

```php
// app/Http/Controllers/SuperAdmin/PlanController.php
public function update(Request $request, Plan $plan)
{
    $validated = $request->validate([
        'name' => 'required|string',
        'price' => 'required|numeric|min:0',
        'split_percentage' => 'required|numeric|min:0|max:100',  // ← NOVO
        'billing_cycle' => 'required|in:monthly,yearly',
        'features' => 'array',
        'limits' => 'array',
    ]);
    
    $plan->update($validated);
    
    return redirect('/admin/plans')
        ->with('success', "Plan updated. Split: {$plan->split_percentage}%");
}

// routes/admin.php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('plans', PlanController::class);  // Inclui split_percentage
});
```

### 2. Tenant: Configurar Asaas Próprio

```php
// app/Http/Controllers/Tenant/AsaasConfigController.php
class AsaasConfigController extends Controller
{
    public function edit()
    {
        $tenant = auth()->user()->tenant;
        return view('tenant.asaas.config', ['tenant' => $tenant]);
    }
    
    public function update(Request $request)
    {
        $tenant = auth()->user()->tenant;
        
        $validated = $request->validate([
            'use_own_asaas' => 'boolean',
            'asaas_api_key' => 'nullable|string|min:10',
            'asaas_webhook_token' => 'nullable|string|min:10',
            'asaas_account_id' => 'nullable|string',
            'bank_account_config' => 'nullable|array',
        ]);
        
        // Se quer usar seu próprio Asaas, validar credenciais
        if ($validated['use_own_asaas']) {
            $asaasService = new AsaasService(
                apiKey: $validated['asaas_api_key'],
                webhookToken: $validated['asaas_webhook_token']
            );
            
            try {
                // Testar credenciais
                $asaasService->getAccount();
            } catch (\Exception $e) {
                return back()->withErrors('Credenciais Asaas inválidas');
            }
        }
        
        $tenant->update($validated);
        
        return back()->with('success', 'Configuração Asaas atualizada');
    }
}

// routes/tenant.php
Route::prefix('settings')->group(function () {
    Route::get('/asaas', [AsaasConfigController::class, 'edit']);
    Route::post('/asaas', [AsaasConfigController::class, 'update']);
});
```

---

## ⏰ COMMANDS/JOBS

### 1. Gerar Faturas Automaticamente (Mensal)

```php
// app/Console/Commands/GenerateTenantMonthlyInvoices.php
class GenerateTenantMonthlyInvoices extends Command
{
    protected $signature = 'tenant:generate-monthly-invoices {--tenant-id=}';
    protected $description = 'Generate monthly invoices for all tenants based on their subscriptions';
    
    public function handle()
    {
        $query = Subscription::where('status', 'active');
        
        if ($this->option('tenant-id')) {
            $query->where('tenant_id', $this->option('tenant-id'));
        }
        
        $subscriptions = $query->get();
        $service = new TenantInvoiceService();
        
        foreach ($subscriptions as $subscription) {
            try {
                $invoice = $service->generatePlanSubscriptionInvoice($subscription);
                $service->sendToAsaas($invoice);
                
                $this->info("✓ Invoice generated for {$subscription->tenant->name}");
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$subscription->tenant->name}: {$e->getMessage()}");
            }
        }
    }
}

// Schedule em app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Rodar no primeiro dia de cada mês, 9:00 AM
    $schedule->command('tenant:generate-monthly-invoices')
        ->monthlyOn(1, '09:00');
}
```

### 2. Sincronizar Pagamentos do Asaas

```php
// app/Jobs/SyncTenantInvoicePayments.php
class SyncTenantInvoicePayments implements ShouldQueue
{
    public function handle()
    {
        $asaasService = new AsaasService();
        
        // Buscar faturas não pagas
        $invoices = TenantInvoice::where('status', '!=', 'paid')
            ->whereNotNull('asaas_payment_id')
            ->get();
        
        foreach ($invoices as $invoice) {
            try {
                $payment = $asaasService->getPayment($invoice->asaas_payment_id);
                
                if ($payment['status'] === 'RECEIVED') {
                    $invoice->markAsPaid();
                    
                    Log::info("Payment synchronized", [
                        'invoice_number' => $invoice->invoice_number
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to sync payment", [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
```

---

## 🎯 FLUXOS DE EXEMPLO

### Fluxo 1: SuperAdmin Cria Plano com Split

```
1. SuperAdmin acessa /admin/plans
2. Clica em "Create Plan"
3. Preenche:
   - Name: "Professional"
   - Price: R$ 299.00
   - Split Percentage: 10%  ← NOVO
   - Billing Cycle: monthly
   - Features: [...]
4. Salva em database com split_percentage = 10
```

### Fluxo 2: Tenant Contrata Plano

```
1. Novo Tenant faz signup
2. Seleciona plano "Professional" (R$ 299/mês, 10% split)
3. Fatura gerada automaticamente:
   ├─ Base Amount: R$ 299.00
   ├─ Split Percentage: 10%
   ├─ Split Amount (comissão): R$ 29.90  ← Superadmin recebe
   └─ Total: R$ 299.00  ← Tenant paga
4. Webhook Asaas processa pagamento
5. TenantInvoice status → 'paid'
6. SplitBilling status → 'paid'
```

### Fluxo 3: Tenant Configurar Seu Asaas

```
1. Tenant acessa /settings/asaas
2. Ativa "Usar meu próprio Asaas"
3. Insere:
   - API Key: "key_xyz..."
   - Webhook Token: "webhook_xyz..."
   - Account ID: "acc_xyz..."
4. Sistema valida credenciais
5. Salva em Tenant.asaas_api_key (encriptado)
6. Tenant pode agora cobrar seus clientes via seu Asaas
```

### Fluxo 4: Tenant Cobra Cliente (Com seu Asaas)

```
1. Tenant cria Invoice para Cliente A: R$ 500
2. Sistema detecta que tenant tem Asaas configurado
3. Cria payment no Asaas do TENANT (não do superadmin)
4. Cliente A recebe boleto do Asaas do Tenant
5. Tenant recebe o dinheiro em sua conta Asaas
6. SuperAdmin não interfere nessa cobrança
   (Mas já cobra do tenant pelo plano no início do mês)
```

---

## 🔐 SEGURANÇA

### Proteções Necessárias

```php
// 1. Encriptar credenciais Asaas do tenant
protected $encrypted = ['asaas_api_key', 'asaas_webhook_token'];

// 2. Validar webhook apenas para conta correta
public function handleAsaasWebhook(Request $request)
{
    $webhook = $request->getContent();
    $signature = $request->header('asaas-signature');
    
    // Verificar com qual token validar
    $tenant = Tenant::where('asaas_account_id', $request->input('account_id'))->first();
    if (!$tenant) {
        abort(404);  // Webhook para account desconhecido
    }
    
    $asaasService = $tenant->getAsaasService();
    if (!$asaasService->verifyWebhookSignature($signature, $webhook)) {
        abort(401);  // Assinatura inválida
    }
    
    // Processar webhook
}

// 3. Rate limiting em endpoints de config
Route::middleware(['throttle:10,1'])->group(function () {
    Route::post('/settings/asaas', [AsaasConfigController::class, 'update']);
});
```

---

## 📊 RELATÓRIOS & DASHBOARDS

### Dashboard SuperAdmin: Receita com Split

```php
// Mostrar:
- Total cobrado de todos os tenants no mês
- Total de split/comissão recebido
- Breakdown por plano
- Gráfico de tendência

Query:
SELECT 
    p.name,
    COUNT(s.id) as tenant_count,
    SUM(ti.base_amount) as total_billed,
    SUM(ti.split_amount) as total_commission
FROM plans p
JOIN subscriptions s ON p.id = s.plan_id
LEFT JOIN tenant_invoices ti ON s.id = ti.subscription_id
WHERE MONTH(ti.issue_date) = MONTH(NOW())
GROUP BY p.id;
```

### Dashboard Tenant: Suas Cobranças

```php
// Mostrar:
- Quanto você paga para superadmin (sua fatura)
- Quanto você cobra dos clientes (suas invoices)
- Saldo líquido se usar Asaas
```

---

## 📋 CHECKLIST DE IMPLEMENTAÇÃO

- [ ] Migration: add split_percentage to plans
- [ ] Migration: add asaas_config to tenants
- [ ] Migration: create tenant_invoices table
- [ ] Migration: create split_billings table
- [ ] Modify Plan.php: add split_percentage, calculateSplitAmount()
- [ ] Modify Tenant.php: add asaas fields, hasAsaasConfigured(), getAsaasService()
- [ ] Create TenantInvoice model
- [ ] Create SplitBilling model
- [ ] Refactor AsaasService to support multiple accounts
- [ ] Create TenantInvoiceService
- [ ] Create MultiAccountAsaasService
- [ ] Create PlanController update with split validation
- [ ] Create AsaasConfigController
- [ ] Create GenerateTenantMonthlyInvoices command
- [ ] Create SyncTenantInvoicePayments job
- [ ] Create tenant invoices view
- [ ] Create asaas config view
- [ ] Add routes for tenant asaas config
- [ ] Add scheduler for monthly invoices
- [ ] Add webhook for superadmin tenant payments
- [ ] Add webhook for tenant customer payments
- [ ] Create dashboard showing split/commission
- [ ] Test with real Asaas API

---

## 🚀 PRÓXIMOS PASSOS

1. **Implementação Fase 1:** Migrations + Models base
2. **Implementação Fase 2:** Serviços e Controllers
3. **Implementação Fase 3:** Commands, Jobs, Webhooks
4. **Testes:** Unit + Integration + E2E
5. **UI:** Views para admin configurar split, tenant configurar Asaas
6. **Deploy:** Staging → Production

---

**Documento criado:** May 22, 2026  
**Versão:** 1.0 - Arquitetura Completa  
**Status:** 🟢 Pronto para Implementação
