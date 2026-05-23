<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'cnpj',
        'domain',
        'api_token',
        'primary_color',
        'secondary_color',
        'accent_color',
        'asaas_customer_id',
        'plan_id',
        'is_active',
        'trial_ends_at',
        'subscription_status',
        'email_provider',
        'email_config',
        'send_proposal_by_email',
        'send_proposal_by_whatsapp',
        'metadata',
    ];

    protected $hidden = [
        'api_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'email_config' => 'array',
        'send_proposal_by_email' => 'boolean',
        'send_proposal_by_whatsapp' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the users for the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the subscriptions for the tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the current active subscription.
     */
    public function currentSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->orWhere('status', 'trial')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Get the plan for the tenant.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the clients for the tenant.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get the shipments for the tenant.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the branches for the tenant.
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /**
     * Get the freight tables for the tenant.
     */
    public function freightTables(): HasMany
    {
        return $this->hasMany(FreightTable::class);
    }

    /**
     * Get the invoices for the tenant.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the expenses for the tenant.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the expense categories for the tenant.
     */
    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    /**
     * Get WhatsApp integrations for the tenant.
     */
    public function whatsappIntegrations(): HasMany
    {
        return $this->hasMany(WhatsAppIntegration::class);
    }

    /**
     * Get WhatsApp message templates for the tenant.
     */
    public function whatsappMessageTemplates(): HasMany
    {
        return $this->hasMany(WhatsAppMessageTemplate::class);
    }

    /**
     * Check if tenant is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant has active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->is_active && 
               $this->subscription_status === 'active' && 
               !$this->isOnTrial();
    }

    /**
     * Generate a new API token for the tenant.
     */
    public function generateApiToken(): string
    {
        $token = \Illuminate\Support\Str::random(60);
        $this->api_token = hash('sha256', $token);
        $this->save();

        return $token;
    }

    // ========== ASAAS MULTI-TENANT METHODS ==========

    /**
     * Check if tenant has configured their own Asaas account
     */
    public function hasAsaasConfigured(): bool
    {
        return $this->uses_own_asaas &&
               !empty($this->asaas_api_key) &&
               !empty($this->asaas_webhook_token);
    }

    /**
     * Get appropriate Asaas service for this tenant
     * Returns tenant's own service if configured, otherwise superadmin's
     */
    public function getAsaasService(): \App\Services\AsaasService
    {
        if ($this->hasAsaasConfigured()) {
            return new \App\Services\AsaasService(
                apiKey: $this->asaas_api_key,
                webhookToken: $this->asaas_webhook_token,
                baseUrl: config('services.asaas.api_url'),
                accountType: 'tenant'
            );
        }

        // Return superadmin's Asaas service
        return app(\App\Services\AsaasService::class);
    }

    /**
     * Get tenant invoices (faturas do superadmin para este tenant)
     */
    public function tenantInvoices()
    {
        return $this->hasMany(\App\Models\TenantInvoice::class);
    }

    /**
     * Get split billings for this tenant
     */
    public function splitBillings()
    {
        return $this->hasMany(\App\Models\SplitBilling::class);
    }

    /**
     * Get total amount paid to superadmin
     */
    public function getTotalPaidToSuperAdminAttribute(): float
    {
        return (float) $this->tenantInvoices()
            ->where('status', 'paid')
            ->sum('total_amount');
    }

    /**
     * Get total commission paid to superadmin
     */
    public function getTotalCommissionPaidAttribute(): float
    {
        return (float) $this->tenantInvoices()
            ->where('status', 'paid')
            ->sum('split_amount');
    }

    /**
     * Get total pending amount
     */
    public function getTotalPendingAmountAttribute(): float
    {
        return (float) $this->tenantInvoices()
            ->whereIn('status', ['issued', 'overdue'])
            ->sum('total_amount');
    }

    /**
     * Scope: Get tenants that use their own Asaas
     */
    public function scopeWithOwnAsaas($query)
    {
        return $query->where('uses_own_asaas', true);
    }

    /**
     * Scope: Get tenants that use superadmin Asaas
     */
    public function scopeWithSuperAdminAsaas($query)
    {
        return $query->where('uses_own_asaas', false);
    }

    /**
     * Resolve OpenAI API Key for this tenant.
     */
    public function resolveOpenAiApiKey(): ?string
    {
        $settings = $this->metadata['whatsapp_ai'] ?? [];
        
        if (!empty($settings['openai_api_key_encrypted'])) {
            try {
                return \Illuminate\Support\Facades\Crypt::decryptString($settings['openai_api_key_encrypted']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to decrypt OpenAI API key for tenant', [
                    'tenant_id' => $this->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return config('services.openai.api_key');
    }
}
