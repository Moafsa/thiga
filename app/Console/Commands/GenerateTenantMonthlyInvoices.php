<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\TenantInvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateTenantMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant-invoices:generate {--tenant-id=} {--dry-run}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active tenant subscriptions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $tenantId = $this->option('tenant-id');

        $this->info('Starting tenant invoice generation...');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No invoices will be created');
            $this->newLine();
        }

        // Get active subscriptions
        $query = Subscription::where('status', 'active');

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
            $this->info("Filtering by tenant ID: {$tenantId}");
        }

        $subscriptions = $query->get();
        $service = new TenantInvoiceService();

        if ($subscriptions->isEmpty()) {
            $this->warn('No active subscriptions found.');
            return 0;
        }

        $this->info("Found {$subscriptions->count()} active subscriptions");
        $this->newLine();

        $successCount = 0;
        $failCount = 0;
        $skippedCount = 0;

        // Process each subscription
        $progressBar = $this->output->createProgressBar($subscriptions->count());
        $progressBar->start();

        foreach ($subscriptions as $subscription) {
            try {
                $tenant = $subscription->tenant;
                $plan = $subscription->plan;

                if ($dryRun) {
                    $this->line(
                        "  [DRY] Would create invoice for {$tenant->name} ({$plan->name}) - R$ {$plan->price}"
                    );
                    $successCount++;
                } else {
                    // Check if invoice for this month already exists
                    $existingInvoice = \App\Models\TenantInvoice::where('subscription_id', $subscription->id)
                        ->whereMonth('issue_date', now()->month)
                        ->whereYear('issue_date', now()->year)
                        ->first();

                    if ($existingInvoice) {
                        $skippedCount++;
                        $progressBar->advance();
                        continue;
                    }

                    // Generate invoice
                    $invoice = $service->generateMonthlyInvoice($subscription);

                    try {
                        // Send to Asaas
                        $service->sendToAsaas($invoice);
                    } catch (\Exception $e) {
                        $this->warn("  ⚠️  Created but failed to send to Asaas: {$e->getMessage()}");
                    }

                    $successCount++;
                }

            } catch (\Exception $e) {
                $this->error("  ✗ {$subscription->tenant->name}: {$e->getMessage()}");
                $failCount++;
                Log::error('Failed to generate invoice', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('═══════════════════════════════════');
        $this->info('SUMMARY');
        $this->info('═══════════════════════════════════');
        $this->info("✓ Successful: {$successCount}");
        if ($skippedCount > 0) {
            $this->line("⊘ Skipped (already exists): {$skippedCount}");
        }
        if ($failCount > 0) {
            $this->error("✗ Failed: {$failCount}");
        }
        $this->info('═══════════════════════════════════');
        $this->newLine();

        if ($failCount === 0) {
            $this->info('✅ All invoices generated successfully!');
            return 0;
        } else {
            $this->warn("⚠️  Some invoices failed. Check logs for details.");
            return 1;
        }
    }
}
