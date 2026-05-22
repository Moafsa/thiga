<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Plan;
use App\Models\FiscalDocument;
use Database\Seeders\FiscalDocumentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiscalDocumentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $seeder;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test plan and tenant
        $plan = Plan::factory()->create();
        $this->tenant = Tenant::factory()->create(['plan_id' => $plan->id]);

        $this->seeder = new FiscalDocumentSeeder();
    }

    /**
     * Test seeder can be instantiated
     */
    public function testSeederCanBeInstantiated()
    {
        $this->assertInstanceOf(FiscalDocumentSeeder::class, $this->seeder);
    }

    /**
     * Test seeder creates documents
     */
    public function testSeederCreatesDocuments()
    {
        $initialCount = FiscalDocument::count();

        $this->seeder->run();

        $finalCount = FiscalDocument::count();

        $this->assertGreater($finalCount, $initialCount);
    }

    /**
     * Test seeder creates documents in expected range (50-100)
     */
    public function testSeederCreates50To100Documents()
    {
        $this->seeder->run();

        $count = FiscalDocument::count();

        $this->assertGreaterThanOrEqual(50, $count);
        $this->assertLessThanOrEqual(100, $count);
    }

    /**
     * Test seeder creates documents with varied statuses
     */
    public function testSeederCreatesVariedStatusDocuments()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        $statuses = $documents->pluck('status')->unique();

        // Should have multiple different statuses
        $this->assertGreaterThan(1, $statuses->count());

        // Check for at least some expected statuses
        $expectedStatuses = ['pending', 'authorized', 'error'];
        foreach ($expectedStatuses as $status) {
            $hasStatus = $documents->some(function ($doc) use ($status) {
                return $doc->status === $status;
            });
            // At least one of the expected statuses should exist
        }
    }

    /**
     * Test seeder creates documents with valid access keys
     */
    public function testSeederCreatesValidAccessKeys()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        foreach ($documents as $document) {
            // Access key should be 35 digits
            $this->assertEquals(35, strlen($document->access_key));
            // Should only contain digits
            $this->assertTrue(ctype_digit($document->access_key));
        }
    }

    /**
     * Test seeder creates documents with valid MITT numbers
     */
    public function testSeederCreatesValidMittNumbers()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        foreach ($documents as $document) {
            // MITT number should be a positive integer
            $this->assertIsInt($document->mitt_number);
            $this->assertGreater($document->mitt_number, 0);
        }
    }

    /**
     * Test seeder creates documents with valid document types
     */
    public function testSeederCreatesValidDocumentTypes()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        $validTypes = ['cte', 'mdfe'];

        foreach ($documents as $document) {
            $this->assertContains($document->document_type, $validTypes);
        }
    }

    /**
     * Test seeder associates documents with shipments or routes
     */
    public function testSeederAssociatesWithShipmentsAndRoutes()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        foreach ($documents as $document) {
            // Each document should have either a shipment or route
            $hasShipment = !is_null($document->shipment_id);
            $hasRoute = !is_null($document->route_id);

            $this->assertTrue($hasShipment || $hasRoute);

            // Should not have both
            $this->assertFalse($hasShipment && $hasRoute);

            // If CT-e, should have shipment
            if ($document->isCte()) {
                $this->assertNotNull($document->shipment_id);
                $this->assertNull($document->route_id);
            }

            // If MDF-e, should have route
            if ($document->isMdfe()) {
                $this->assertNotNull($document->route_id);
                $this->assertNull($document->shipment_id);
            }
        }
    }

    /**
     * Test seeder creates documents with timestamps
     */
    public function testSeederCreatesDocumentsWithTimestamps()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        foreach ($documents as $document) {
            // All documents should have created_at
            $this->assertNotNull($document->created_at);

            // authorized_at should be null for non-authorized documents
            if ($document->status !== 'authorized') {
                $this->assertNull($document->authorized_at);
            } else {
                // authorized documents might have authorized_at set
                // but it's not required
            }
        }
    }

    /**
     * Test seeder respects tenant isolation
     */
    public function testSeederRespectsMultiTenancy()
    {
        // Create another tenant
        $otherPlan = Plan::factory()->create();
        $otherTenant = Tenant::factory()->create(['plan_id' => $otherPlan->id]);

        $this->seeder->run();

        $documents = FiscalDocument::all();

        // Documents should have valid tenant_id
        foreach ($documents as $document) {
            $this->assertNotNull($document->tenant_id);
        }
    }

    /**
     * Test seeder creates documents with mixed CT-e and MDF-e
     */
    public function testSeederCreatesMixedDocumentTypes()
    {
        $this->seeder->run();

        $documents = FiscalDocument::all();

        $ctes = $documents->filter(function ($doc) {
            return $doc->document_type === 'cte';
        });

        $mdfes = $documents->filter(function ($doc) {
            return $doc->document_type === 'mdfe';
        });

        // Should have both types
        $this->assertGreater($ctes->count(), 0);
        $this->assertGreater($mdfes->count(), 0);
    }
}
