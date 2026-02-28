<?php

namespace App\Observers;

use App\Models\DriverExpense;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Log;

class DriverExpenseObserver
{
    /**
     * Handle the DriverExpense "updated" event.
     */
    public function updated(DriverExpense $driverExpense): void
    {
        // Only proceed if status changed to 'approved'
        if ($driverExpense->wasChanged('status') && $driverExpense->status === 'approved') {
            $this->createCompanyExpense($driverExpense);
        }
    }

    /**
     * Create a corresponding expense in the company ledger.
     */
    protected function createCompanyExpense(DriverExpense $driverExpense): void
    {
        try {
            // Prevent duplicate creation (check metadata or similar transaction reference)
            // Ideally we should store the 'company_expense_id' in DriverExpense metadata
            $metadata = $driverExpense->metadata ?? [];
            if (!empty($metadata['company_expense_id'])) {
                return;
            }

            // Determine Category ID
            // Map 'expense_type' (enum) to ExpenseCategory (DB)
            // This is a naive mapping. In a real scenario, we might need a settings table.
            $categoryName = match ($driverExpense->expense_type) {
                'fuel' => 'Combustível',
                'toll' => 'Pedágio',
                'meal' => 'Alimentação / Viagens',
                'parking' => 'Estacionamento',
                'maintenance' => 'Manutenção',
                default => 'Despesas Gerais',
            };

            $category = ExpenseCategory::firstOrCreate(
                ['name' => $categoryName, 'tenant_id' => $driverExpense->driver->tenant_id],
                ['description' => 'Categoria criada automaticamente pelo sistema', 'is_active' => true]
            );

            // Create the Expense
            $expense = Expense::create([
                'tenant_id' => $driverExpense->driver->tenant_id,
                'expense_category_id' => $category->id,
                'vehicle_id' => $driverExpense->driver->vehicles->first()->id ?? null, // Best guess
                'route_id' => $driverExpense->route_id,
                'description' => "[MOT] " . $driverExpense->description, // Prefix to identify source
                'amount' => $driverExpense->amount,
                'due_date' => $driverExpense->expense_date, // When it happened
                'paid_at' => $driverExpense->expense_date,  // It was already paid/spent by driver
                'status' => 'paid',
                'payment_method' => $driverExpense->payment_method ?? 'other',
                'notes' => "Gasto aprovado do motorista {$driverExpense->driver->name}. ID Origem: {$driverExpense->id}",
                'metadata' => ['driver_expense_id' => $driverExpense->id],
            ]);

            // Link back
            $metadata['company_expense_id'] = $expense->id;
            $driverExpense->metadata = $metadata;
            $driverExpense->saveQuietly(); // Avoid loop

            Log::info("Synced DriverExpense #{$driverExpense->id} to Company Expense #{$expense->id}");

        } catch (\Exception $e) {
            Log::error("Failed to sync DriverExpense #{$driverExpense->id} to Company Expense: " . $e->getMessage());
        }
    }
}
