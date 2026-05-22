<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmStage extends Model
{
    protected $fillable = ['tenant_id', 'crm_pipeline_id', 'name', 'order_index', 'color'];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pipeline()
    {
        return $this->belongsTo(CrmPipeline::class, 'crm_pipeline_id');
    }

    public function deals()
    {
        return $this->hasMany(CrmDeal::class);
    }
}
