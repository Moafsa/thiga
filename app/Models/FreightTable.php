<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FreightTable extends Model
{
    use HasFactory, HasActiveScope;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'is_active',
        'is_default',
        'destination_type',
        'destination_name',
        'destination_state',
        'origin_name',
        'origin_state',
        'cep_range_start',
        'cep_range_end',
        'weight_0_30',
        'weight_31_50',
        'weight_51_70',
        'weight_71_100',
        'weight_over_100_rate',
        'ctrc_tax',
        'ad_valorem_rate',
        'gris_rate',
        'gris_minimum',
        'toll_per_100kg',
        'cubage_factor',
        'min_freight_rate_vs_nf',
        'min_freight_rate_type',
        'min_freight_rate_value',
        'tde_markets',
        'tde_supermarkets_cd',
        'palletization',
        'unloading_tax',
        'weekend_holiday_rate',
        'redelivery_rate',
        'return_rate',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'weight_0_30' => 'decimal:2',
        'weight_31_50' => 'decimal:2',
        'weight_51_70' => 'decimal:2',
        'weight_71_100' => 'decimal:2',
        'weight_over_100_rate' => 'decimal:4',
        'ctrc_tax' => 'decimal:2',
        'ad_valorem_rate' => 'decimal:4',
        'gris_rate' => 'decimal:4',
        'gris_minimum' => 'decimal:2',
        'toll_per_100kg' => 'decimal:2',
        'cubage_factor' => 'decimal:2',
        'min_freight_rate_vs_nf' => 'decimal:4',
        'min_freight_rate_value' => 'decimal:2',
        'tde_markets' => 'decimal:2',
        'tde_supermarkets_cd' => 'decimal:2',
        'palletization' => 'decimal:2',
        'unloading_tax' => 'decimal:2',
        'weekend_holiday_rate' => 'decimal:4',
        'redelivery_rate' => 'decimal:4',
        'return_rate' => 'decimal:4',
        'settings' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the clients associated with this freight table.
     */
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_freight_table')
            ->withTimestamps();
    }


    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByDestination($query, string $destination)
    {
        return $query->where('destination_name', 'like', "%{$destination}%");
    }

    public function scopeByState($query, string $state)
    {
        return $query->where('destination_state', $state);
    }

    /**
     * Get freight value for a specific weight range
     */
    public function getWeightValue(float $weight): float
    {
        if ($weight <= 30) {
            return $this->weight_0_30 ?? 0;
        } elseif ($weight <= 50) {
            return $this->weight_31_50 ?? 0;
        } elseif ($weight <= 70) {
            return $this->weight_51_70 ?? 0;
        } elseif ($weight <= 100) {
            return $this->weight_71_100 ?? 0;
        } else {
            // Above 100kg: base + excess + CTRC
            $base = $this->weight_71_100 ?? 0;
            $excessWeight = $weight - 100;
            $excessCost = $excessWeight * ($this->weight_over_100_rate ?? 0);
            $ctrc = $this->ctrc_tax ?? 0;
            return $base + $excessCost + $ctrc;
        }
    }

    /**
     * Check if CEP is in range
     */
    public function isCepInRange(string $cep): bool
    {
        if (!$this->cep_range_start || !$this->cep_range_end) {
            return false;
        }

        $cepClean = preg_replace('/\D/', '', $cep);
        $startClean = preg_replace('/\D/', '', $this->cep_range_start);
        $endClean = preg_replace('/\D/', '', $this->cep_range_end);

        return $cepClean >= $startClean && $cepClean <= $endClean;
    }
}





















