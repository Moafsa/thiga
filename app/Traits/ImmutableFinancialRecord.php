<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait ImmutableFinancialRecord
{
    public static function bootImmutableFinancialRecord()
    {
        static::deleting(function (Model $model) {
            // Check if model has 'status' and if it is 'paid'
            if (isset($model->status) && $model->status === 'paid') {
                // Allow force delete or if explicitly bypassed (not standard Eloquent, but good to know)
                // For now, Strict Block.

                // Allow only if it is a SoftDelete action (if model uses SoftDeletes)
                if (method_exists($model, 'isForceDeleting') && !$model->isForceDeleting()) {
                    return true; // Use SoftDeletes normally
                }

                // If it's a hard delete or model doesn't use SoftDeletes
                throw new \Exception("A transação #{$model->id} já foi consolidada (Paga) e não pode ser excluída. Realize um estorno.");
            }
        });

        static::updating(function (Model $model) {
            // Prevent changing amount or date if already paid, unless it's a reversal (not implemented yet)
            // Ideally we would snapshot "original" state.
            if ($model->getOriginal('status') === 'paid' && $model->status === 'paid') {
                if ($model->isDirty('amount') || $model->isDirty('total_amount')) {
                    throw new \Exception("Transações consolidadas não permitem alteração de valor. Crie um novo lançamento de ajuste.");
                }
            }
        });
    }
}
