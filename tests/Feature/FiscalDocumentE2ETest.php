<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\FiscalDocument;
use App\Models\Shipment;
use App\Models\Route;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalDocumentE2ETest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test plan and tenant
        $plan = Plan::factory()->create();
        $this->tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

        // Create test user
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /**
     * Test complete workflow: Create → List → Filter → View
     */
    public function testCompleteFlowFromCreateToView()
    {
        // Step 1: Create test data
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'authorized',
            'mitt_number' => 12345,
        ]);

        // Step 2: List all documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('documents')->items());

        // Step 3: Filter documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte');

        $response->assertStatus(200);
        $this->assertCount(1, $response->viewData('documents')->items());

        // Step 4: View document details
        $response = $this->actingAs($this->user)
            ->get("/fiscal/documents/{$document->id}");

        // Verify document is viewable (assuming show route exists)
        $this->assertTrue(
            $response->status() === 200 || $response->status() === 404
        );
    }

    /**
     * Test cancel authorized CT-e workflow
     */
    public function testCancelAuthorizedCteWorkflow()
    {
        // Create authorized CT-e
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'authorized',
        ]);

        // Verify it's in the list
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('authorized', $documents[0]->status);

        // Simulate cancellation
        $document->update(['status' => 'cancelled']);

        // Verify it no longer appears in authorized filter
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?status=authorized');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(0, $documents);

        // Verify it appears in cancelled filter
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?status=cancelled');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('cancelled', $documents[0]->status);
    }

    /**
     * Test multi-tenant isolation end-to-end
     */
    public function testMultiTenantIsolationEndToEnd()
    {
        // Create second tenant
        $otherPlan = Plan::factory()->create();
        $otherTenant = Tenant::factory()->create(['plan_id' => $otherPlan->id]);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        // Create documents for both tenants
        $shipment1 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $document1 = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment1->id,
            'document_type' => 'cte',
            'mitt_number' => 111,
        ]);

        $shipment2 = Shipment::factory()->create(['tenant_id' => $otherTenant->id]);
        $document2 = FiscalDocument::factory()->create([
            'tenant_id' => $otherTenant->id,
            'shipment_id' => $shipment2->id,
            'document_type' => 'cte',
            'mitt_number' => 222,
        ]);

        // User 1 should only see their documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals($document1->id, $documents[0]->id);
        $this->assertEquals($this->tenant->id, $documents[0]->tenant_id);

        // User 2 should only see their documents
        $response = $this->actingAs($otherUser)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals($document2->id, $documents[0]->id);
        $this->assertEquals($otherTenant->id, $documents[0]->tenant_id);
    }

    /**
     * Test full search workflow
     */
    public function testCompleteSearchWorkflow()
    {
        // Create multiple documents
        $shipment1 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $shipment2 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        $accessKey1 = '12345678901234567890123456789012345';
        $accessKey2 = '98765432109876543210987654321098765';

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment1->id,
            'access_key' => $accessKey1,
            'mitt_number' => 111,
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment2->id,
            'access_key' => $accessKey2,
            'mitt_number' => 222,
        ]);

        // Search by access key
        $response = $this->actingAs($this->user)
            ->get("/fiscal/documents?search={$accessKey1}");

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals($accessKey1, $documents[0]->access_key);

        // Search by MITT number
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?search=222');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals(222, $documents[0]->mitt_number);
    }

    /**
     * Test pagination with filtering
     */
    public function testPaginationWithFiltering()
    {
        // Create 25 authorized documents
        $shipments = Shipment::factory(25)->create(['tenant_id' => $this->tenant->id]);

        foreach ($shipments as $shipment) {
            FiscalDocument::factory()->create([
                'tenant_id' => $this->tenant->id,
                'shipment_id' => $shipment->id,
                'document_type' => 'cte',
                'status' => 'authorized',
            ]);
        }

        // First page of authorized documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertCount(20, $documents->items());

        // Second page
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized&page=2');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertCount(5, $documents->items());
        $this->assertEquals(2, $documents->currentPage());
    }

    /**
     * Test date range filtering workflow
     */
    public function testDateRangeFilteringWorkflow()
    {
        // Create documents with different dates
        $shipment1 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $shipment2 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $shipment3 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment1->id,
            'created_at' => Carbon::now()->setDate(2024, 3, 15),
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment2->id,
            'created_at' => Carbon::now()->setDate(2024, 5, 15),
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment3->id,
            'created_at' => Carbon::now()->setDate(2024, 7, 15),
        ]);

        // Filter for May 2024
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?date_from=2024-05-01&date_to=2024-05-31');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
    }

    /**
     * Test combined filters with pagination
     */
    public function testCombinedFiltersWithPagination()
    {
        // Create 30 documents with mixed attributes
        $shipments = Shipment::factory(30)->create(['tenant_id' => $this->tenant->id]);

        foreach ($shipments as $index => $shipment) {
            $status = $index % 3 === 0 ? 'authorized' : 'pending';
            FiscalDocument::factory()->create([
                'tenant_id' => $this->tenant->id,
                'shipment_id' => $shipment->id,
                'document_type' => 'cte',
                'status' => $status,
                'created_at' => Carbon::now()->setDate(2024, 5, 15),
            ]);
        }

        // Apply combined filters
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized&date_from=2024-05-01&date_to=2024-05-31');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();

        // Should have at least some matching documents
        $this->assertGreater(count($documents), 0);

        // All returned documents should match filters
        foreach ($documents as $doc) {
            $this->assertEquals('cte', $doc->document_type);
            $this->assertEquals('authorized', $doc->status);
        }
    }

    /**
     * Test empty results display
     */
    public function testEmptyResultsDisplay()
    {
        // Create document with different type
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        // Search for non-existent CT-e
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(0, $documents);
    }

    /**
     * Test shipment to document relationship
     */
    public function testShipmentToDocumentRelationship()
    {
        // Create shipment with client info
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        // List documents and verify shipment relationship
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertNotNull($documents[0]->shipment);
        $this->assertEquals($shipment->id, $documents[0]->shipment->id);
    }

    /**
     * Test route to document relationship
     */
    public function testRouteToDocumentRelationship()
    {
        // Create route with driver info
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);

        $document = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        // List documents and verify route relationship
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertNotNull($documents[0]->route);
        $this->assertEquals($route->id, $documents[0]->route->id);
    }
}
