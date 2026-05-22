<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmDeal extends Model
{
    protected $fillable = [
        'tenant_id', 'client_id', 'user_id', 'crm_stage_id', 'title',
        'lead_value', 'status', 'contact_channel', 'next_action_date',
        'last_contacted_at', 'ai_summary', 'custom_data'
    ];

    protected $casts = [
        'custom_data' => 'array',
        'next_action_date' => 'date',
        'last_contacted_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stage()
    {
        return $this->belongsTo(CrmStage::class, 'crm_stage_id');
    }

    public function interactions()
    {
        return $this->hasMany(CrmInteraction::class)->orderBy('created_at', 'desc');
    }
}
