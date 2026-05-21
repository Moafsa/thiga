<?php

namespace Database\Seeders;

use App\Models\FiscalDocument;
use App\Models\Shipment;
use App\Models\Route;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class FiscalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();

        $this->command->info("Creating fiscal documents for tenant: {$tenant->name}");

        // Get or create shipments and routes
        $shipments = Shipment::where('tenant_id', $tenant->id)->limit(25)->get();
        $routes = Route::where('tenant_id', $tenant->id)->limit(20)->get();

        if ($shipments->isEmpty()) {
            $this->command->warn('No shipments found. Creating test shipments...');
            $shipments = Shipment::factory(25)->for($tenant)->create();
        }

        if ($routes->isEmpty()) {
            $this->command->warn('No routes found. Creating test routes...');
            $routes = Route::factory(20)->for($tenant)->create();
        }

        // Create CT-e documents for shipments
        $this->command->info('Creating CT-e documents...');
        foreach ($shipments as $i => $shipment) {
            FiscalDocument::updateOrCreate(
                ['shipment_id' => $shipment->id, 'document_type' => 'cte'],
                [
                    'tenant_id' => $tenant->id,
                    'mitt_id' => 'mitt_' . uniqid(),
                    'mitt_number' => 1000000 + $i,
                    'access_key' => $this->generateAccessKey(),
                    'status' => $this->randomStatus(),
                    'xml' => $this->generateFakeCteXml($shipment),
                    'pdf_url' => 'https://example.com/cte_' . $i . '.pdf',
                    'xml_url' => 'https://example.com/cte_' . $i . '.xml',
                    'error_message' => null,
                    'sent_at' => now()->subDays(rand(1, 30)),
                    'authorized_at' => now()->subDays(rand(0, 30)),
                ]
            );
        }

        // Create MDF-e documents for routes
        $this->command->info('Creating MDF-e documents...');
        foreach ($routes as $i => $route) {
            FiscalDocument::updateOrCreate(
                ['route_id' => $route->id, 'document_type' => 'mdfe'],
                [
                    'tenant_id' => $tenant->id,
                    'mitt_id' => 'mitt_' . uniqid(),
                    'mitt_number' => 2000000 + $i,
                    'access_key' => $this->generateAccessKey(),
                    'status' => $this->randomStatus(),
                    'xml' => $this->generateFakeMdfeXml($route),
                    'pdf_url' => 'https://example.com/mdfe_' . $i . '.pdf',
                    'xml_url' => 'https://example.com/mdfe_' . $i . '.xml',
                    'error_message' => null,
                    'sent_at' => now()->subDays(rand(1, 30)),
                    'authorized_at' => now()->subDays(rand(0, 30)),
                ]
            );
        }

        $this->command->info('✅ Fiscal documents seeded successfully!');
    }

    /**
     * Generate random status
     */
    private function randomStatus(): string
    {
        $statuses = ['pending', 'validating', 'processing', 'authorized', 'rejected', 'cancelled', 'error'];
        return $statuses[array_rand($statuses)];
    }

    /**
     * Generate a fake CT-e access key
     */
    private function generateAccessKey(): string
    {
        // Format: AABBCCDDEEEFFGGHHJJKKLLMMNNOOPP (35 digits)
        $key = '';
        for ($i = 0; $i < 35; $i++) {
            $key .= rand(0, 9);
        }
        return $key;
    }

    /**
     * Generate fake CT-e XML
     */
    private function generateFakeCteXml(Shipment $shipment): string
    {
        $accessKey = $this->generateAccessKey();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CTe>
    <infCte Id="Id{$accessKey}">
        <ide>
            <cUF>35</cUF>
            <cCT>123456789</cCT>
            <CTRF>000000000000001</CTRF>
            <cfop>07000</cfop>
            <natOp>PRESTACAO DE SERVICO</natOp>
            <mod>57</mod>
            <serie>0</serie>
            <nCT>1</nCT>
            <dEmi>2024-05-21</dEmi>
            <dSaiSaida>2024-05-21</dSaiSaida>
            <hSaiSaida>14:30:00</hSaiSaida>
        </ide>
        <infDoc>
            <infNF>
                <nRoma>12345</nRoma>
                <nNF>123456</nNF>
                <serie>1</serie>
                <dEmi>2024-05-21</dEmi>
                <vNF>5000.00</vNF>
                <dPrev>2024-05-30</dPrev>
            </infNF>
        </infDoc>
        <rem>
            <CNPJ>11222333000181</CNPJ>
            <IE>123456789012</IE>
            <UF>SP</UF>
            <tpPessoa>J</tpPessoa>
            <DABC>123456</DABC>
        </rem>
        <dest>
            <CNPJ>98765432000190</CNPJ>
            <IE>987654321098</IE>
            <UF>RJ</UF>
            <tpPessoa>J</tpPessoa>
        </dest>
        <prop>
            <CNPJ>55555555000155</CNPJ>
            <RNTRC>123456789</RNTRC>
            <inscSuframa>123456789</inscSuframa>
            <UF>SP</UF>
            <tpPessoa>J</tpPessoa>
            <DABC>123456</DABC>
        </prop>
        <infValorCota>
            <Comp>
                <vValePed>100.00</vValePed>
            </Comp>
        </infValorCota>
    </infCte>
</CTe>
XML;
    }

    /**
     * Generate fake MDF-e XML
     */
    private function generateFakeMdfeXml(Route $route): string
    {
        $accessKey = $this->generateAccessKey();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<MDFe>
    <infMDFe Id="Id{$accessKey}">
        <ide>
            <cUF>35</cUF>
            <type>MDFe</type>
            <serie>1</serie>
            <nMDF>123456</nMDF>
            <dEmi>2024-05-21</dEmi>
        </ide>
        <infMunCarrega>
            <cMunCarrega>3550308</cMunCarrega>
            <xMunCarrega>SAO PAULO</xMunCarrega>
            <infDocCarreg>
                <infCte>
                    <chCte>35240521123456789012345678901234567890123456</chCte>
                </infCte>
            </infDocCarreg>
        </infMunCarrega>
        <infMunDescarrega>
            <cMunDescarrega>3300100</cMunDescarrega>
            <xMunDescarrega>RIO DE JANEIRO</xMunDescarrega>
            <infDocDescarreg>
                <infCte>
                    <chCte>35240521123456789012345678901234567890123456</chCte>
                </infCte>
            </infDocDescarreg>
        </infMunDescarrega>
        <infModal versaoModal="3.00">
            <rodo>
                <infANTT>
                    <rntrc>123456789</rntrc>
                    <infPIP>
                        <nPIP>123456</nPIP>
                        <dValid>2025-05-21</dValid>
                    </infPIP>
                </infANTT>
                <infVeicRodo>
                    <infVehicle>
                        <placa>ABC1234</placa>
                        <RENAVAM>12345678901</RENAVAM>
                        <tpCar>03</tpCar>
                        <CapCar>5000</CapCar>
                    </infVehicle>
                </infVeicRodo>
                <lacRodo>
                    <nLac>123456</nLac>
                    <nLacRodo>0</nLacRodo>
                </lacRodo>
            </rodo>
        </infModal>
        <infEmitter>
            <CNPJ>11222333000181</CNPJ>
            <IE>123456789012</IE>
            <UF>SP</UF>
            <tpEmit>1</tpEmit>
        </infEmitter>
        <infUnidCarga>
            <infAtlRodo>
                <nLacRodo>0</nLacRodo>
            </infAtlRodo>
        </infUnidCarga>
    </infMDFe>
</MDFe>
XML;
    }
}
