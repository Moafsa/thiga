<?php

namespace App\Console\Commands;

use App\Models\Shipment;
use App\Models\Route;
use App\Services\FiscalService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SyncMittDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fiscal:sync-mitt {--tenant-id= : Sync only for a specific tenant}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Synchronize CT-e and MDF-e documents from Mitt API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting Mitt document synchronization...');
        $this->newLine();

        $cteCount = $this->syncCtes();
        $mdfeCount = $this->syncMdfes();

        $this->newLine();
        $this->info('✅ Synchronization complete!');
        $this->table(
            ['Document Type', 'Count'],
            [
                ['CT-e', $cteCount],
                ['MDF-e', $mdfeCount],
                ['Total', $cteCount + $mdfeCount],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Sync CT-e documents from shipments
     */
    private function syncCtes(): int
    {
        $this->info('📋 Syncing CT-e documents...');

        $shipments = $this->getShipments();
        $count = 0;
        $errors = [];

        $bar = $this->output->createProgressBar($shipments->count());
        $bar->start();

        foreach ($shipments as $shipment) {
            try {
                // Check if already has authorized CT-e
                if ($shipment->hasAuthorizedCte()) {
                    $bar->advance();
                    continue;
                }

                // Attempt sync
                $this->resolveFiscalService()->syncShipmentCte($shipment);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Shipment #{$shipment->id}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (!empty($errors)) {
            $this->warn(count($errors) . ' CT-e sync errors:');
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->warn("  • $error");
            }
            if (count($errors) > 5) {
                $this->warn('  ... and ' . (count($errors) - 5) . ' more');
            }
        }

        return $count;
    }

    /**
     * Sync MDF-e documents from routes
     */
    private function syncMdfes(): int
    {
        $this->info('📦 Syncing MDF-e documents...');

        $routes = $this->getRoutes();
        $count = 0;
        $errors = [];

        $bar = $this->output->createProgressBar($routes->count());
        $bar->start();

        foreach ($routes as $route) {
            try {
                // Check if already has authorized MDF-e
                if ($route->hasAuthorizedMdfe()) {
                    $bar->advance();
                    continue;
                }

                // Attempt sync
                $this->resolveFiscalService()->syncRouteMdfe($route);
                $count++;
            } catch (\Exception $e) {
                $errors[] = "Route #{$route->id}: {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if (!empty($errors)) {
            $this->warn(count($errors) . ' MDF-e sync errors:');
            foreach (array_slice($errors, 0, 5) as $error) {
                $this->warn("  • $error");
            }
            if (count($errors) > 5) {
                $this->warn('  ... and ' . (count($errors) - 5) . ' more');
            }
        }

        return $count;
    }

    /**
     * Get shipments to sync
     */
    private function getShipments(): Collection
    {
        $query = Shipment::query();

        if ($tenantId = $this->option('tenant-id')) {
            $query->where('tenant_id', $tenantId);
        }

        // Only shipments that should have CT-e
        return $query
            ->whereIn('status', ['collected', 'in_transit', 'delivered'])
            ->with('tenant', 'senderClient', 'receiverClient')
            ->get();
    }

    /**
     * Get routes to sync
     */
    private function getRoutes(): Collection
    {
        $query = Route::query();

        if ($tenantId = $this->option('tenant-id')) {
            $query->where('tenant_id', $tenantId);
        }

        // Only routes that should have MDF-e
        return $query
            ->whereIn('status', ['in_progress', 'completed'])
            ->with('tenant', 'driver', 'vehicle', 'shipments')
            ->get();
    }

    /**
     * Resolve FiscalService
     */
    private function resolveFiscalService(): FiscalService
    {
        return app(FiscalService::class);
    }
}
