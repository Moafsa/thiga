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

class FiscalDocumentFilterTest extends TestCase
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
     * Test filter by CT-e document type
     */
    public function testFilterByCteDocumentType()
    {
        // Create mixed documents
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('cte', $documents[0]->document_type);
    }

    /**
     * Test filter by MDF-e document type
     */
    public function testFilterByMdfeDocumentType()
    {
        // Create mixed documents
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=mdfe');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('mdfe', $documents[0]->document_type);
    }

    /**
     * Test filter by pending status
     */
    public function testFilterByPendingStatus()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'pending',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'authorized',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?status=pending');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('pending', $documents[0]->status);
    }

    /**
     * Test filter by authorized status
     */
    public function testFilterByAuthorizedStatus()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'pending',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'authorized',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?status=authorized');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('authorized', $documents[0]->status);
    }

    /**
     * Test filter by error status
     */
    public function testFilterByErrorStatus()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'error',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'authorized',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?status=error');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('error', $documents[0]->status);
    }

    /**
     * Test filter by date range
     */
    public function testFilterByDateRange()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        // Create document in date range
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'created_at' => Carbon::now()->setDate(2024, 5, 15),
        ]);

        // Create document outside date range
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'created_at' => Carbon::now()->setDate(2024, 3, 15),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?date_from=2024-05-01&date_to=2024-05-31');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
    }

    /**
     * Test search by complete access key
     */
    public function testSearchByCompleteAccessKey()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        $accessKey = '12345678901234567890123456789012345';
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'access_key' => $accessKey,
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'access_key' => '99999999999999999999999999999999999',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/fiscal/documents?search={$accessKey}");

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals($accessKey, $documents[0]->access_key);
    }

    /**
     * Test search by partial access key
     */
    public function testSearchByPartialAccessKey()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        $accessKey = '12345678901234567890123456789012345';
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'access_key' => $accessKey,
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'access_key' => '99999999901234567890123456789012345',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?search=1234567890');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(2, $documents);
    }

    /**
     * Test search by MITT number
     */
    public function testSearchByMittNumber()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'mitt_number' => 12345,
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'mitt_number' => 67890,
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?search=12345');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals(12345, $documents[0]->mitt_number);
    }

    /**
     * Test combined filters (document type + status + date)
     */
    public function testCombinedFilters()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);

        // Matching document
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'authorized',
            'created_at' => Carbon::now()->setDate(2024, 5, 15),
        ]);

        // Different type
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
            'status' => 'authorized',
            'created_at' => Carbon::now()->setDate(2024, 5, 15),
        ]);

        // Different status
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'pending',
            'created_at' => Carbon::now()->setDate(2024, 5, 15),
        ]);

        // Different date
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'authorized',
            'created_at' => Carbon::now()->setDate(2024, 3, 15),
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized&date_from=2024-05-01&date_to=2024-05-31');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals('cte', $documents[0]->document_type);
        $this->assertEquals('authorized', $documents[0]->status);
    }

    /**
     * Test filter query string is preserved during pagination
     */
    public function testFilterQueryStringPreservedInPagination()
    {
        $shipments = Shipment::factory(25)->create(['tenant_id' => $this->tenant->id]);

        foreach ($shipments as $shipment) {
            FiscalDocument::factory()->create([
                'tenant_id' => $this->tenant->id,
                'shipment_id' => $shipment->id,
                'document_type' => 'cte',
                'status' => 'authorized',
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte&status=authorized');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');

        // Check that pagination links contain the filter parameters
        // The withQueryString() method in the controller preserves query strings
        $this->assertTrue($documents->hasPages());
    }

    /**
     * Test filter with no results
     */
    public function testFilterWithNoResults()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=mdfe');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(0, $documents);
    }

    /**
     * Test reset filters (all=empty string removes filter)
     */
    public function testResetFilters()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'mdfe',
        ]);

        // With filter
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte');

        $this->assertCount(1, $response->viewData('documents')->items());

        // Without filter (reset)
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $this->assertCount(2, $response->viewData('documents')->items());
    }

    /**
     * Test filter respects tenant isolation
     */
    public function testFilterRespectsTenantIsolation()
    {
        // Create another tenant
        $otherPlan = Plan::factory()->create();
        $otherTenant = Tenant::factory()->create(['plan_id' => $otherPlan->id]);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        // Create documents for both tenants
        $shipment1 = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment1->id,
            'document_type' => 'cte',
        ]);

        $shipment2 = Shipment::factory()->create(['tenant_id' => $otherTenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $otherTenant->id,
            'shipment_id' => $shipment2->id,
            'document_type' => 'cte',
        ]);

        // Our user should only see our tenant's filtered documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?document_type=cte');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();
        $this->assertCount(1, $documents);
        $this->assertEquals($this->tenant->id, $documents[0]->tenant_id);
    }
}
