<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$xmlParser = new \App\Services\CteXmlParserService();
$unsyncedXmls = \App\Models\CteXml::where('is_used', false)->get();
$shipmentTrackingNumbers = \App\Models\Shipment::pluck('tracking_number')->toArray();
$count = 0;

foreach ($unsyncedXmls as $xmlRecord) {
    if (in_array($xmlRecord->cte_number, $shipmentTrackingNumbers)) continue;
    
    $defaultClient = \App\Models\Client::firstOrCreate(
        ['tenant_id' => $xmlRecord->tenant_id, 'name' => 'Cliente Padrão (Sistema)'],
        ['address' => 'N/A', 'city' => 'N/A', 'state' => 'XX', 'zip_code' => '00000000', 'is_active' => true]
    );
    
    try {
        $path = str_replace('local:', '', $xmlRecord->xml_url);
        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($path)) {
            $xmlContent = \Illuminate\Support\Facades\Storage::disk('local')->get($path);
            if (empty($xmlContent)) continue;
            
            $cteData = $xmlParser->parseXml($xmlContent);
            if (empty($cteData['document_number'])) continue;
            
            $cteNumber = $cteData['document_number'];
            
            \App\Models\Shipment::create([
                'tenant_id' => $xmlRecord->tenant_id,
                'sender_client_id' => $defaultClient->id,
                'receiver_client_id' => $defaultClient->id,
                'tracking_number' => $cteNumber,
                'title' => 'CT-e ' . $cteNumber,
                'weight' => $cteData['weight'] ?? 0,
                'volume' => $cteData['volume'] ?? 0,
                'quantity' => $cteData['quantity'] ?? 1,
                'value' => $cteData['goods_value'] ?? $cteData['value'] ?? 0,
                'pickup_address' => $cteData['origin']['address'] ?? 'Endereço Origem',
                'pickup_city' => $cteData['origin']['city'] ?? 'Cidade',
                'pickup_state' => $cteData['origin']['state'] ?? 'XX',
                'pickup_zip_code' => $cteData['origin']['zip_code'] ?? '00000000',
                'pickup_date' => $cteData['pickup_date'] ?? now()->format('Y-m-d'),
                'pickup_time' => '08:00:00',
                'delivery_address' => $cteData['destination']['address'] ?? 'Endereço Destino',
                'delivery_city' => $cteData['destination']['city'] ?? 'Cidade',
                'delivery_state' => $cteData['destination']['state'] ?? 'XX',
                'delivery_zip_code' => $cteData['destination']['zip_code'] ?? '00000000',
                'delivery_date' => $cteData['delivery_date'] ?? now()->format('Y-m-d'),
                'delivery_time' => '18:00:00',
                'status' => 'pending',
                'freight_value' => $cteData['value'] ?? 0,
            ]);
            
            $shipmentTrackingNumbers[] = $cteNumber;
            $count++;
        }
    } catch (\Exception $e) {
        echo 'Erro: ' . $e->getMessage() . "\n";
    }
}
echo "Sincronizados $count XMLs antigos.\n";
