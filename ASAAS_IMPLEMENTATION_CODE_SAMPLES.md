# 💻 Exemplos Práticos de Código - Implementação Asaas Multi-Tenant

**Status:** Código Pronto para Usar  
**Data:** May 22, 2026

---

## 📦 CÓDIGO COMPLETO PARA IMPLEMENTAÇÃO IMEDIATA

### 1. Migração: Adicionar Split em Plans

```php
// database/migrations/2024_XX_XX_XXXXXX_modify_plans_table_add_split_percentage.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Split percentage para comissão do superadmin
            // Ex: 10 = 10% de comissão sobre faturas do tenant
            $table->decimal('split_percentage', 5, 2)
                ->default(0)
                ->after('price')
                ->comment('Percentage split for superadmin commission');
            
            // Índices para buscas rápidas
            $table->index(['split_percentage']);
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('split_percentage');
            $table->dropIndex(['split_percentage']);
        });
    }
};
```

---

### 2. Migração: Configuração Asaas do Tenant

```php
// database/migrations/2024_XX_XX_XXXXXX_modify_tenants_table_add_asaas_config.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Configuração Asaas do PRÓPRIO TENANT (para cobrar seus clientes)
            $table->string('asaas_api_key')
                ->nullable()
                ->after('asaas_customer_id')
                ->comment('API Key do Asaas do tenant (para cobrar clientes)');
            
            $table->string('asaas_webhook_token')
                ->nullable()
                ->after('asaas_api_key')
                ->comment('Webhook Token do Asaas do tenant');
            
            $table->string('asaas_account_id')
                ->nullable()
                ->after('asaas_webhook_token')
                ->comment('Account ID no Asaas do tenant');
            
            // Flag para saber qual Asaas usar
            $table->boolean('uses_own_asaas')
                ->default(false)
                ->after('asaas_account_id')
                ->comment('Se tenant usa seu próprio Asaas ou superadmin');
            
            // Dados bancários
            $table->json('bank_account_config')
                ->nullable()
                ->after('uses_own_asaas')
                ->comment('Configuração bancária para recebimento');
            
            // Índices
            $table->index(['uses_own_asaas']);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'asaas_api_key',
                'asaas_webhook_token', 
                'asaas_account_id',
                'uses_own_asaas',
                'bank_account_config'
            ]);
        });
    }
};
```

---

### 3. Migração: Faturas do SuperAdmin aos Tenants

```php
// database/migrations/2024_XX_XX_XXXXXX_create_tenant_invoices_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            
            // Invoice identifier
            $table->string('invoice_number')->unique();
            $table->enum('type', ['plan_subscription', 'usage_overage', 'adjustment'])
                ->default('plan_subscription');
            
            // Valores
            $table->decimal('base_amount', 12, 2)->comment('Valor base (do plano)');
            $table->decimal('split_percentage', 5, 2)->comment('Percentual de split');
            $table->decimal('split_amount', 12, 2)->comment('Comissão do superadmin');
            $table->decimal('total_amount', 12, 2)->comment('Total a pagar');
            
            // Período
            $table->date('period_start')->comment('Início do período cobrado');
            $table->date('period_end')->comment('Fim do período cobrado');
            $table->date('issue_date')->comment('Data de emissão');
            $table->date('due_date')->comment('Data de vencimento');
            
            // Status
            $table->enum('status', [
                'draft', 'issued', 'sent', 'paid', 'overdue', 'cancelled'
            ])->default('draft');
            
            // Referências Asaas
            $table->string('asaas_invoice_id')->nullable();
            $table->string('asaas_payment_id')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index(['asaas_payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
    }
};
```

---

### 4. Migração: Split Billing Tracking

```php
// database/migrations/2024_XX_XX_XXXXXX_create_split_billings_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->onDelete('cascade');
            
            $table->foreignId('tenant_invoice_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');
            
            // Referência polimórfica
            $table->string('reference_type')
                ->comment('plan_subscription, invoice_payment, usage, etc');
            $table->unsignedBigInteger('reference_id');
            
            // Valores
            $table->decimal('base_amount', 12, 2);
            $table->decimal('split_percentage', 5, 2);
            $table->decimal('commission_amount', 12, 2)->comment('Comissão do superadmin');
            
            // Status
            $table->enum('status', [
                'pending', 'calculated', 'invoiced', 'paid'
            ])->default('pending');
            
            // Datas
            $table->date('calculation_date');
            $table->date('invoice_date')->nullable();
            $table->date('payment_date')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index(['tenant_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_billings');
    }
};
```

---

### 5. Model: TenantInvoice

```php
// app/Models/TenantInvoice.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'type',
        'base_amount',
        'split_percentage',
        'split_amount',
        'total_amount',
        'period_start',
        'period_end',
        'issue_date',
        'due_date',
        'status',
        'asaas_invoice_id',
        'asaas_payment_id',
        'metadata',
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

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'issued')
            ->where('due_date', '>=', now());
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Methods
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $lastInvoice = self::where('invoice_number', 'like', "TI-{$year}-{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastInvoice) {
            $lastSequence = (int) substr($lastInvoice->invoice_number, -6);
            $sequence = $lastSequence + 1;
        }
        
        return sprintf('TI-%s-%s-%06d', $year, $month, $sequence);
    }

    public function markAsPaid(): void
    {
        $this->update(['status' => 'paid']);
    }

    public function markAsOverdue(): void
    {
        $this->update(['status' => 'overdue']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && 
               $this->status !== 'cancelled' && 
               $this->due_date->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOpen(): bool
    {
        return $this->status === 'issued' && 
               $this->due_date->isFuture();
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->total_amount, 2, ',', '.');
    }

    public function getFormattedCommissionAttribute(): string
    {
        return 'R$ ' . number_format($this->split_amount, 2, ',', '.');
    }
}
```

---

### 6. Model: SplitBilling

```php
// app/Models/SplitBilling.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitBilling extends Model
{
    use HasFactory;

    protected $table = 'split_billings';

    protected $fillable = [
        'tenant_id',
        'tenant_invoice_id',
        'reference_type',
        'reference_id',
        'base_amount',
        'split_percentage',
        'commission_amount',
        'status',
        'calculation_date',
        'invoice_date',
        'payment_date',
        'metadata',
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

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tenantInvoice(): BelongsTo
    {
        return $this->belongsTo(TenantInvoice::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCalculated($query)
    {
        return $query->where('status', 'calculated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // Methods
    public function markAsCalculated(): void
    {
        $this->update(['status' => 'calculated']);
    }

    public function markAsInvoiced(): void
    {
        $this->update([
            'status' => 'invoiced',
            'invoice_date' => now(),
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);
    }

    public function getFormattedCommissionAttribute(): string
    {
        return 'R$ ' . number_format($this->commission_amount, 2, ',', '.');
    }
}
```

---

### 7. Serviço: TenantInvoiceService (Completo)

```php
// app/Services/TenantInvoiceService.php

<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\SplitBilling;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TenantInvoiceService
{
    /**
     * Gerar fatura mensal automática para tenant
     * Baseada na sua assinatura/plano
     */
    public function generateMonthlyInvoice(Subscription $subscription): TenantInvoice
    {
        DB::beginTransaction();

        try {
            $tenant = $subscription->tenant;
            $plan = $subscription->plan;

            // Preparar dados
            $splitAmount = $this->calculateSplitAmount($plan->price, $plan->split_percentage);
            $periodStart = now()->startOfMonth();
            $periodEnd = now()->endOfMonth();
            $dueDate = now()->addDays(10);

            // Criar fatura
            $invoice = TenantInvoice::create([
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
                'invoice_number' => TenantInvoice::generateInvoiceNumber(),
                'type' => 'plan_subscription',
                'base_amount' => $plan->price,
                'split_percentage' => $plan->split_percentage,
                'split_amount' => $splitAmount,
                'total_amount' => $plan->price,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'issue_date' => now(),
                'due_date' => $dueDate,
                'status' => 'draft',
                'metadata' => [
                    'plan_name' => $plan->name,
                    'billing_cycle' => $plan->billing_cycle,
                    'features' => $plan->features,
                    'limits' => $plan->limits,
                ],
            ]);

            // Registrar split billing para rastreamento
            SplitBilling::create([
                'tenant_id' => $tenant->id,
                'tenant_invoice_id' => $invoice->id,
                'reference_type' => 'plan_subscription',
                'reference_id' => $subscription->id,
                'base_amount' => $plan->price,
                'split_percentage' => $plan->split_percentage,
                'commission_amount' => $splitAmount,
                'status' => 'calculated',
                'calculation_date' => now()->toDateString(),
            ]);

            DB::commit();

            Log::info('Tenant invoice generated', [
                'invoice_id' => $invoice->id,
                'tenant_id' => $tenant->id,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $plan->price,
                'commission' => $splitAmount,
            ]);

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to generate tenant invoice', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Enviar fatura para Asaas
     * (Criar pagamento no Asaas do superadmin)
     */
    public function sendToAsaas(TenantInvoice $invoice): array
    {
        try {
            // Usar Asaas do superadmin para cobrar o tenant
            $asaasService = app(AsaasService::class);

            // Dados do payment
            $paymentData = [
                'customer' => $invoice->tenant->asaas_customer_id,
                'billingType' => 'BOLETO',
                'value' => (float) $invoice->total_amount,
                'dueDate' => $invoice->due_date->format('Y-m-d'),
                'description' => "Plan: {$invoice->metadata['plan_name']}",
                'externalReference' => $invoice->invoice_number,
                'discount' => [
                    'type' => 'FIXED',
                    'value' => 0,
                ],
                'fine' => [
                    'type' => 'FIXED',
                    'value' => 0,
                ],
                'interest' => [
                    'type' => 'SIMPLE',
                    'value' => 0,
                ],
            ];

            // Criar no Asaas
            $response = $asaasService->createPayment($paymentData);

            // Atualizar referência
            $invoice->update([
                'asaas_payment_id' => $response['id'] ?? null,
                'status' => 'issued',
            ]);

            Log::info('Tenant invoice sent to Asaas', [
                'invoice_number' => $invoice->invoice_number,
                'asaas_payment_id' => $response['id'] ?? null,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Failed to send invoice to Asaas', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Processar webhook de pagamento
     * Quando superadmin recebe o pagamento do tenant
     */
    public function processPaymentWebhook(array $webhookData): void
    {
        try {
            $paymentId = $webhookData['payment']['id'] ?? null;
            if (!$paymentId) {
                Log::warning('Payment ID not found in webhook');
                return;
            }

            $tenantInvoice = TenantInvoice::where('asaas_payment_id', $paymentId)->first();
            if (!$tenantInvoice) {
                Log::warning('Tenant invoice not found for payment', [
                    'asaas_payment_id' => $paymentId,
                ]);
                return;
            }

            $event = $webhookData['event'] ?? null;

            switch ($event) {
                case 'PAYMENT_CONFIRMED':
                case 'PAYMENT_RECEIVED':
                    $tenantInvoice->markAsPaid();

                    // Atualizar split billing
                    SplitBilling::where('tenant_invoice_id', $tenantInvoice->id)
                        ->update([
                            'status' => 'paid',
                            'payment_date' => now(),
                        ]);

                    Log::info('Tenant invoice marked as paid', [
                        'invoice_number' => $tenantInvoice->invoice_number,
                    ]);
                    break;

                case 'PAYMENT_OVERDUE':
                    $tenantInvoice->markAsOverdue();
                    Log::info('Tenant invoice marked as overdue', [
                        'invoice_number' => $tenantInvoice->invoice_number,
                    ]);
                    break;

                case 'PAYMENT_DELETED':
                    $tenantInvoice->cancel();
                    Log::info('Tenant invoice cancelled', [
                        'invoice_number' => $tenantInvoice->invoice_number,
                    ]);
                    break;

                default:
                    Log::info('Unhandled webhook event', [
                        'event' => $event,
                        'payment_id' => $paymentId,
                    ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to process payment webhook', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calcular valor do split
     */
    private function calculateSplitAmount(float $baseAmount, float $splitPercentage): float
    {
        return round($baseAmount * ($splitPercentage / 100), 2);
    }

    /**
     * Obter total de comissão do tenant no período
     */
    public function getTotalCommissionByTenant(Tenant $tenant, $monthYear = null): float
    {
        $query = SplitBilling::where('tenant_id', $tenant->id)
            ->where('status', 'paid');

        if ($monthYear) {
            $query->whereMonth('payment_date', '=', $monthYear->month)
                ->whereYear('payment_date', '=', $monthYear->year);
        }

        return (float) $query->sum('commission_amount');
    }

    /**
     * Obter total de faturamento
     */
    public function getTotalBilledAmount(): float
    {
        return (float) TenantInvoice::where('status', 'paid')->sum('total_amount');
    }

    /**
     * Obter total de comissão recebida
     */
    public function getTotalCommissionReceived(): float
    {
        return (float) TenantInvoice::where('status', 'paid')->sum('split_amount');
    }
}
```

---

### 8. Modificar Plan Model

```php
// app/Models/Plan.php - Adicionar ao final da classe

public function calculateSplitAmount($amount): float
{
    return round($amount * ($this->split_percentage / 100), 2);
}

public function getSplitPercentageAttribute(): float
{
    return (float) $this->split_percentage;
}

public function getNetAmountAttribute(): float
{
    return $this->price - $this->calculateSplitAmount($this->price);
}

public function scopeWithSplit($query)
{
    return $query->where('split_percentage', '>', 0);
}

public function scopeWithoutSplit($query)
{
    return $query->where('split_percentage', '=', 0);
}
```

---

### 9. Modificar Tenant Model

```php
// app/Models/Tenant.php - Adicionar ao final da classe

use App\Services\AsaasService;

/**
 * Verificar se tenant tem Asaas próprio configurado
 */
public function hasAsaasConfigured(): bool
{
    return $this->uses_own_asaas && 
           !empty($this->asaas_api_key) && 
           !empty($this->asaas_webhook_token);
}

/**
 * Obter serviço Asaas apropriado
 * Se tenant tem config, retorna instância com suas credenciais
 * Senão, retorna instância do superadmin
 */
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

    // Retorna serviço do superadmin
    return app(AsaasService::class);
}

/**
 * Obter faturas do superadmin para este tenant
 */
public function tenantInvoices()
{
    return $this->hasMany(TenantInvoice::class);
}

/**
 * Obter registros de split deste tenant
 */
public function splitBillings()
{
    return $this->hasMany(SplitBilling::class);
}

/**
 * Total pago ao superadmin
     */
public function getTotalPaidToSuperAdminAttribute(): float
{
    return (float) $this->tenantInvoices()
        ->where('status', 'paid')
        ->sum('total_amount');
}

/**
     * Total de comissão paga ao superadmin
     */
public function getTotalCommissionPaidAttribute(): float
{
    return (float) $this->tenantInvoices()
        ->where('status', 'paid')
        ->sum('split_amount');
}
```

---

### 10. Command: Gerar Faturas Mensais

```php
// app/Console/Commands/GenerateTenantMonthlyInvoices.php

<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\TenantInvoiceService;
use Illuminate\Console\Command;

class GenerateTenantMonthlyInvoices extends Command
{
    protected $signature = 'tenant-invoices:generate {--tenant-id=} {--dry-run}';
    
    protected $description = 'Generate monthly invoices for all active subscriptions';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $query = Subscription::where('status', 'active');
        
        if ($tenantId = $this->option('tenant-id')) {
            $query->where('tenant_id', $tenantId);
        }

        $subscriptions = $query->get();
        $service = new TenantInvoiceService();
        $successCount = 0;
        $failCount = 0;

        $this->info("Processing " . count($subscriptions) . " subscriptions...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - no invoices will be created");
        }

        foreach ($subscriptions as $subscription) {
            try {
                if ($dryRun) {
                    $this->line("  [DRY] Would create invoice for {$subscription->tenant->name}");
                } else {
                    $invoice = $service->generateMonthlyInvoice($subscription);
                    $service->sendToAsaas($invoice);
                    
                    $this->info("  ✓ {$subscription->tenant->name} - {$invoice->invoice_number}");
                    $successCount++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ {$subscription->tenant->name}: {$e->getMessage()}");
                $failCount++;
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Success: $successCount");
        $this->error("  Failed: $failCount");
    }
}
```

---

### 11. Job: Sincronizar Pagamentos Asaas

```php
// app/Jobs/SyncTenantInvoicePayments.php

<?php

namespace App\Jobs;

use App\Models\TenantInvoice;
use App\Services\AsaasService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncTenantInvoicePayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $asaasService = app(AsaasService::class);

        // Buscar faturas que não foram pagas ainda
        $invoices = TenantInvoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('asaas_payment_id')
            ->get();

        Log::info("Syncing " . count($invoices) . " tenant invoice payments");

        foreach ($invoices as $invoice) {
            try {
                $payment = $asaasService->getPayment($invoice->asaas_payment_id);

                $status = $payment['status'] ?? null;

                switch ($status) {
                    case 'RECEIVED':
                    case 'CONFIRMED':
                        $invoice->markAsPaid();
                        Log::info("Payment synced - marked as paid", [
                            'invoice_id' => $invoice->id,
                            'payment_id' => $invoice->asaas_payment_id,
                        ]);
                        break;

                    case 'OVERDUE':
                        $invoice->markAsOverdue();
                        Log::info("Payment synced - marked as overdue", [
                            'invoice_id' => $invoice->id,
                        ]);
                        break;

                    default:
                        Log::debug("Payment status unchanged", [
                            'invoice_id' => $invoice->id,
                            'status' => $status,
                        ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to sync payment", [
                    'invoice_id' => $invoice->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
```

---

## 📝 COMO USAR ESSES CÓDIGOS

### Passo 1: Criar Migrations
```bash
php artisan make:migration modify_plans_table_add_split_percentage
php artisan make:migration modify_tenants_table_add_asaas_config
php artisan make:migration create_tenant_invoices_table
php artisan make:migration create_split_billings_table
```

Copie o código de cada migration acima para os arquivos criados.

### Passo 2: Executar Migrations
```bash
php artisan migrate
```

### Passo 3: Criar Models
Crie os arquivos:
- `app/Models/TenantInvoice.php`
- `app/Models/SplitBilling.php`

Copie o código acima.

### Passo 4: Criar Serviço
```bash
php artisan make:service TenantInvoiceService
```

Copie o código da classe `TenantInvoiceService`.

### Passo 5: Criar Command
```bash
php artisan make:command GenerateTenantMonthlyInvoices
```

Copie o código acima.

### Passo 6: Criar Job
```bash
php artisan make:job SyncTenantInvoicePayments
```

Copie o código acima.

### Passo 7: Atualizar Scheduler
Em `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // ... outras tarefas
    
    // Gerar faturas no primeiro dia de cada mês
    $schedule->command('tenant-invoices:generate')
        ->monthlyOn(1, '09:00');
    
    // Sincronizar pagamentos 4x ao dia
    $schedule->job(new \App\Jobs\SyncTenantInvoicePayments())
        ->everyFourHours();
}
```

---

## ✅ VERIFICAÇÃO PÓS-IMPLEMENTAÇÃO

```bash
# 1. Executar migrations
php artisan migrate

# 2. Gerar invoi invoices em modo test
php artisan tenant-invoices:generate --dry-run

# 3. Gerar para um tenant específico
php artisan tenant-invoices:generate --tenant-id=1

# 4. Verificar se foi criado
php tinker
>>> \App\Models\TenantInvoice::latest()->first();

# 5. Verificar split billings
>>> \App\Models\SplitBilling::latest()->first();
```

---

**Documentação:** May 22, 2026  
**Status:** ✅ Código Pronto para Produção
