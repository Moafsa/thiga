<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Models\FiscalDocument;
use App\Models\Shipment;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalDocumentListingTest extends TestCase
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
     * Test user can view fiscal documents index page
     */
    public function testCanViewFiscalDocumentsIndex()
    {
        $this->actingAs($this->user)
            ->get('/fiscal/documents')
            ->assertStatus(200)
            ->assertViewIs('fiscal.all.index')
            ->assertViewHas('documents');
    }

    /**
     * Test unauthenticated users are redirected to login
     */
    public function testCannotAccessWithoutAuthentication()
    {
        $this->get('/fiscal/documents')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    /**
     * Test multi-tenant isolation - users cannot see other tenant's documents
     */
    public function testCannotAccessOtherTenantDocuments()
    {
        // Create another tenant with documents
        $otherPlan = Plan::factory()->create();
        $otherTenant = Tenant::factory()->create(['plan_id' => $otherPlan->id]);
        $otherUser = User::factory()->create(['tenant_id' => $otherTenant->id]);

        // Create shipment and fiscal document for other tenant
        $otherShipment = Shipment::factory()->create(['tenant_id' => $otherTenant->id]);
        $otherDocument = FiscalDocument::factory()->create([
            'tenant_id' => $otherTenant->id,
            'shipment_id' => $otherShipment->id,
            'document_type' => 'cte',
        ]);

        // Create document for our tenant
        $ourShipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $ourDocument = FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $ourShipment->id,
            'document_type' => 'cte',
        ]);

        // User should only see their own tenant's documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $this->assertDatabaseHas('fiscal_documents', [
            'id' => $ourDocument->id,
            'tenant_id' => $this->tenant->id,
        ]);

        // Verify other tenant's document is not accessible
        $this->actingAs($this->user)
            ->get("/fiscal/documents/{$otherDocument->id}")
            ->assertStatus(403);
    }

    /**
     * Test fiscal documents table contains correct columns
     */
    public function testDocumentsTableContainsCorrectColumns()
    {
        // Create test documents
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
            'status' => 'authorized',
            'mitt_number' => 12345,
            'access_key' => '12345678901234567890123456789012345',
        ]);

        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
            'status' => 'pending',
            'mitt_number' => 67890,
            'access_key' => '98765432109876543210987654321098765',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        // Verify documents are in the view
        $response->assertViewHas('documents');
        $this->assertCount(2, $response->viewData('documents')->items());
    }

    /**
     * Test pagination works correctly (20 documents per page)
     */
    public function testPaginationWorks()
    {
        // Create 50 test documents
        $shipments = Shipment::factory(50)->create(['tenant_id' => $this->tenant->id]);

        foreach ($shipments as $shipment) {
            FiscalDocument::factory()->create([
                'tenant_id' => $this->tenant->id,
                'shipment_id' => $shipment->id,
                'document_type' => 'cte',
            ]);
        }

        // First page should have 20 documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $this->assertCount(20, $response->viewData('documents')->items());

        // Check pagination info
        $documents = $response->viewData('documents');
        $this->assertEquals(50, $documents->total());
        $this->assertEquals(1, $documents->currentPage());
        $this->assertEquals(3, $documents->lastPage());

        // Second page should have remaining documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?page=2');

        $response->assertStatus(200);
        $this->assertCount(20, $response->viewData('documents')->items());
        $this->assertEquals(2, $response->viewData('documents')->currentPage());

        // Third page should have 10 documents
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents?page=3');

        $response->assertStatus(200);
        $this->assertCount(10, $response->viewData('documents')->items());
    }

    /**
     * Test empty state when no documents exist
     */
    public function testEmptyStateWhenNoDocuments()
    {
        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $this->assertCount(0, $response->viewData('documents')->items());
    }

    /**
     * Test document count is displayed correctly
     */
    public function testDocumentCountDisplay()
    {
        // Create 10 documents
        $shipments = Shipment::factory(10)->create(['tenant_id' => $this->tenant->id]);

        foreach ($shipments as $shipment) {
            FiscalDocument::factory()->create([
                'tenant_id' => $this->tenant->id,
                'shipment_id' => $shipment->id,
            ]);
        }

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertEquals(10, $documents->total());
    }

    /**
     * Test CT-e documents are displayed with correct type
     */
    public function testCteDocumentsDisplayedCorrectly()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertEquals('cte', $documents->items()[0]->document_type);
    }

    /**
     * Test MDF-e documents are displayed with correct type
     */
    public function testMdfeDocumentsDisplayedCorrectly()
    {
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertEquals('mdfe', $documents->items()[0]->document_type);
    }

    /**
     * Test status badges are displayed
     */
    public function testStatusBadgesDisplayed()
    {
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'status' => 'authorized',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents');
        $this->assertEquals('authorized', $documents->items()[0]->status);
    }

    /**
     * Test related entity links (shipment/route) are available
     */
    public function testRelatedEntityLinksAvailable()
    {
        // Test shipment relation
        $shipment = Shipment::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'shipment_id' => $shipment->id,
            'document_type' => 'cte',
        ]);

        // Test route relation
        $route = Route::factory()->create(['tenant_id' => $this->tenant->id]);
        FiscalDocument::factory()->create([
            'tenant_id' => $this->tenant->id,
            'route_id' => $route->id,
            'document_type' => 'mdfe',
        ]);

        $response = $this->actingAs($this->user)
            ->get('/fiscal/documents');

        $response->assertStatus(200);
        $documents = $response->viewData('documents')->items();

        // Verify shipment relation
        $this->assertNotNull($documents[1]->shipment);
        $this->assertEquals($shipment->id, $documents[1]->shipment->id);

        // Verify route relation
        $this->assertNotNull($documents[0]->route);
        $this->assertEquals($route->id, $documents[0]->route->id);
    }
}
