<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmInteraction extends Model
{
    protected $fillable = [
        'tenant_id', 'crm_deal_id', 'type', 'content', 'sender_type',
        'user_id', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function deal()
    {
        return $this->belongsTo(CrmDeal::class, 'crm_deal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
