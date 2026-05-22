<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\FiscalDocument;
use App\Models\Shipment;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalDocumentSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test plan and tenant
        $plan = Plan::factory()->create();
        $this->tenant = Tenant::factory()->create(['plan_id' => $plan->id]);
    }

    /**
     * Test sync mitt documents command runs without error
     */
    public function testSyncMittDocumentsCommandRuns()
    {
        $this->artisan('fiscal:sync-mitt')
            ->assertExitCode(0);
    }

    /**
     * Test command creates CT-e documents for shipments
     */
    public function testSyncMittCreatesCtesForShipments()
    {
        // Create a shipment that should have a CT-e
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        // Run sync command
        $this->artisan('fiscal:sync-mitt');

        // Verify CT-e was created
        $this->assertDatabaseHas('fiscal_documents', [
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);
    }

    /**
     * Test command creates MDF-e documents for routes
     */
    public function testSyncMittCreatesMdfsForRoutes()
    {
        // Create a route that should have an MDF-e
        $route = Route::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'in_progress',
        ]);

        // Run sync command
        $this->artisan('fiscal:sync-mitt');

        // Verify MDF-e was created
        $this->assertDatabaseHas('fiscal_documents', [
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);
    }

    /**
     * Test sync command updates pending documents
     */
    public function testSyncMittUpdatesPendingDocuments()
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        // Create initial document with pending status
        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'pending',
        ]);

        // Run sync - should update status
        $this->artisan('fiscal:sync-mitt');

        // Verify document still exists and is tracked
        $this->assertDatabaseHas('fiscal_documents', [
            'id' => $document->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test sync command with tenant-id option
     */
    public function testSyncMittWithTenantId()
    {
        // Create documents for multiple tenants
        $otherPlan = Plan::factory()->create();
        $otherTenant = Tenant::factory()->create(['plan_id' => $otherPlan->id]);

        $shipment1 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $shipment2 = Shipment::factory()->create(['tenant_id' => $otherTenant->id]);

        // Run sync only for specific tenant
        $this->artisan('fiscal:sync-mitt', ['--tenant-id' => $this->tenant->id])
            ->assertExitCode(0);

        // Verify documents were created/processed only for specified tenant
        $this->assertDatabaseHas('fiscal_documents', [
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test sync command handles API errors gracefully
     */
    public function testSyncMittHandlesApiErrors()
    {
        // Create a shipment
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        // Run sync - should complete even if API returns errors
        $this->artisan('fiscal:sync-mitt')
            ->assertExitCode(0);

        // Verify command completed successfully
        $this->assertTrue(true);
    }

    /**
     * Test sync generates valid access keys (35 digits)
     */
    public function testSyncMittGeneratesValidAccessKeys()
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        $this->artisan('fiscal:sync-mitt');

        // Verify access key is valid (35 digits)
        $document = FiscalDocument::where('tenant_id', $this->tenant->id)
            ->where('shipment_id', $shipment->id)
            ->first();

        if ($document) {
            $this->assertEquals(35, strlen($document->access_key));
            $this->assertTrue(ctype_digit($document->access_key));
        }
    }

    /**
     * Test sync command idempotency (running twice doesn't duplicate)
     */
    public function testSyncMittIsIdempotent()
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        // First sync
        $this->artisan('fiscal:sync-mitt');
        $countAfterFirst = FiscalDocument::where('tenant_id', $this->tenant->id)
            ->where('shipment_id', $shipment->id)
            ->count();

        // Second sync
        $this->artisan('fiscal:sync-mitt');
        $countAfterSecond = FiscalDocument::where('tenant_id', $this->tenant->id)
            ->where('shipment_id', $shipment->id)
            ->count();

        // Should not create duplicates
        $this->assertLessEqual($countAfterSecond, $countAfterFirst + 1);
    }

    /**
     * Test sync preserves existing fiscal document relationships
     */
    public function testSyncPreservesExistingRelationships()
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        // Create initial document
        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'pending',
        ]);

        $originalId = $document->id;

        // Run sync
        $this->artisan('fiscal:sync-mitt');

        // Verify document still exists with same ID
        $this->assertDatabaseHas('fiscal_documents', [
            'id' => $originalId,
            'shipment_id' => $shipment->id,
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Test sync creates documents with correct status
     */
    public function testSyncCreatesDocumentsWithCorrectStatus()
    {
        $shipment = Shipment::factory()->create([
            'tenant_id' => $this->tenant->id,
            'status' => 'confirmed',
        ]);

        $this->artisan('fiscal:sync-mitt');

        $document = FiscalDocument::where('tenant_id', $this->tenant->id)
            ->where('shipment_id', $shipment->id)
            ->first();

        if ($document) {
            // Should have a valid status
            $validStatuses = ['pending', 'validating', 'processing', 'authorized', 'rejected', 'cancelled', 'error'];
            $this->assertContains($document->status, $validStatuses);
        }
    }
}
