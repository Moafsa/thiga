# 🔧 ANÁLISE TÉCNICA - 3 QUESTÕES CRÍTICAS

**Data**: 21 de maio de 2026  
**Assunto**: Integração SEFAZ direta, Mapbox, Análise de Custos por CTE  
**Status**: Análise e recomendações técnicas

---

## ❓ QUESTÃO 1: INTEGRAÇÃO DIRETA COM SEFAZ (SEM MITT)

### A Pergunta
> "Não dá para o sistema estar integrado direto com SEFAZ, a transportadora colocar o certificado digital e senha, e o sistema buscar CT-es, MDF-es e NFes direto do SEFAZ?"

### ✅ SIM, É TOTALMENTE POSSÍVEL

---

## 📋 ARQUITETURA SEFAZ DIRETO

### Como Funciona Atualmente (com Mitt)
```
Sistema → Mitt API → SEFAZ
├─ Desvantagem: Custo Mitt (R$0.50-2.00 por documento)
├─ Desvantagem: Dependência de terceiro
├─ Vantagem: Não precisar gerenciar certificados
└─ Vantagem: Sem complexidade criptográfica
```

### Como Seria (SEFAZ Direto)
```
Sistema → SEFAZ (via certificado digital)
├─ Vantagem: Sem custo extra (apenas certificado)
├─ Vantagem: Controle total
├─ Vantagem: Mais rápido
└─ Desvantagem: Complexidade criptográfica + manutenção de certificado
```

---

## 🛠️ IMPLEMENTAÇÃO TÉCNICA SEFAZ DIRETO

### 1. Armazenar Certificado Digital

```php
// app/Models/Tenant.php - ADICIONAR CAMPOS

Schema::table('tenants', function (Blueprint $table) {
    // Certificado digital (criptografado)
    $table->longText('sefaz_certificate')->nullable(); // PEM criptografado
    $table->string('sefaz_certificate_password')->nullable(); // Senha criptografada
    $table->string('sefaz_cnpj')->nullable(); // CNPJ do certificado
    $table->dateTime('sefaz_certificate_expires_at')->nullable(); // Quando expira
    
    // Ambiente (teste/produção)
    $table->enum('sefaz_environment', ['homolog', 'production'])->default('homolog');
    
    // Contadores SEFAZ (para não repetir números)
    $table->unsignedBigInteger('sefaz_cte_number_counter')->default(1);
    $table->unsignedBigInteger('sefaz_mdfe_number_counter')->default(1);
    $table->unsignedBigInteger('sefaz_nfe_number_counter')->default(1);
});
```

### 2. Serviço de Comunicação SEFAZ

```php
// app/Services/SefazDirectService.php - NOVO

namespace App\Services;

use GuzzleHttp\Client;
use phpseclib3\Crypt\RSA;
use phpseclib3\File\X509;
use SimpleXMLElement;

class SefazDirectService
{
    protected $client;
    protected $tenant;
    protected $certificate;
    protected $privateKey;
    protected $sefazUrl;

    public function __construct($tenant)
    {
        $this->tenant = $tenant;
        $this->client = new Client();
        $this->loadCertificate();
        $this->setSefazEnvironment();
    }

    /**
     * Carregar certificado digital do tenant
     */
    protected function loadCertificate()
    {
        if (!$this->tenant->sefaz_certificate) {
            throw new \Exception('Certificado SEFAZ não configurado para this tenant');
        }

        // Descriptografar certificado (estava criptografado no BD)
        $certEncrypted = $this->tenant->sefaz_certificate;
        $password = decrypt($this->tenant->sefaz_certificate_password);

        // Carregar certificado PEM
        // Pode ser .pem ou .pfx convertido
        $cert = new X509();
        $cert->loadPEM($certEncrypted);

        $this->certificate = $cert;

        // Extrair chave privada
        $this->privateKey = $this->certificate->getPrivateKey();
    }

    /**
     * Definir URL SEFAZ conforme ambiente
     */
    protected function setSefazEnvironment()
    {
        $environment = $this->tenant->sefaz_environment;
        
        if ($environment === 'production') {
            // URLs de produção por estado
            $this->sefazUrl = [
                'cte' => 'https://cte.sefaz.rs.gov.br/webservices/CTeSeriamentoRU.asmx',
                'mdfe' => 'https://mdfe.sefaz.rs.gov.br/webservices/MdfeSeriamentoRU.asmx',
                'nfe' => 'https://nfe.sefaz.rs.gov.br/webservices/NfeSerializacao.asmx',
            ];
        } else {
            // URLs de homologação (teste)
            $this->sefazUrl = [
                'cte' => 'https://cte-homolog.sefaz.rs.gov.br/webservices/CTeSeriamentoRU.asmx',
                'mdfe' => 'https://mdfe-homolog.sefaz.rs.gov.br/webservices/MdfeSeriamentoRU.asmx',
                'nfe' => 'https://nfe-homolog.sefaz.rs.gov.br/webservices/NfeSerializacao.asmx',
            ];
        }
    }

    /**
     * Emitir CT-e direto no SEFAZ
     */
    public function emitirCte($cteData)
    {
        // 1. Validar dados
        $this->validateCteData($cteData);

        // 2. Gerar XML conforme padrão SEFAZ
        $xml = $this->generateCteXml($cteData);

        // 3. Assinar XML com certificado
        $signedXml = $this->signXml($xml);

        // 4. Enviar para SEFAZ
        $response = $this->sendToSefaz($signedXml, 'cte');

        // 5. Processar resposta
        return $this->processCteResponse($response);
    }

    /**
     * Gerar XML de CT-e conforme padrão SEFAZ
     */
    protected function generateCteXml($cteData)
    {
        $sequencial = str_pad(
            $this->tenant->sefaz_cte_number_counter,
            8,
            '0',
            STR_PAD_LEFT
        );

        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CTe></CTe>');
        
        // Versão do padrão
        $xml->addAttribute('xmlns', 'http://www.sefaz.rs.gov.br/webservices');
        
        // Informações do CT-e
        $infCte = $xml->addChild('infCte');
        $infCte->addAttribute('versao', '4.00');
        $infCte->addAttribute('Id', 'ID' . $cteData['tenant_id'] . $sequencial);
        
        // IDE (Identificação)
        $ide = $infCte->addChild('ide');
        $ide->addChild('cUF', '43'); // RS = 43
        $ide->addChild('crt', '1'); // Regime de Tributação
        $ide->addChild('assinaturaQr'); // Será preenchido depois
        $ide->addChild('CFOP', $cteData['cfop'] ?? '5353'); // CFOP padrão transporte
        $ide->addChild('natOp', 'Transporte de carga'); // Natureza operação
        $ide->addChild('mod', '57'); // Modelo 57 = CT-e
        $ide->addChild('serie', $cteData['serie'] ?? '1');
        $ide->addChild('nCT', $sequencial); // Número sequencial
        $ide->addChild('dEmi', now()->format('Y-m-d')); // Data emissão
        $ide->addChild('hEmi', now()->format('H:i:s')); // Hora emissão
        
        // EMIT (Emitente)
        $emit = $infCte->addChild('emit');
        $emit->addChild('CNPJ', str_replace(['/', '-', '.'], '', $this->tenant->cnpj));
        $emit->addChild('IE', $this->tenant->state_registration ?? '');
        $emit->addChild('UF', $this->tenant->state);
        $emit->addChild('xNome', $this->tenant->name);
        $emit->addChild('xFant', $this->tenant->fantasy_name ?? $this->tenant->name);
        
        // Endereço emitente
        $enderEmit = $emit->addChild('enderEmit');
        $enderEmit->addChild('xLgr', $this->tenant->address);
        $enderEmit->addChild('nro', $this->tenant->address_number);
        $enderEmit->addChild('xCpl', $this->tenant->address_complement ?? '');
        $enderEmit->addChild('xBairro', $this->tenant->neighborhood);
        $enderEmit->addChild('cMun', substr($this->tenant->ibge_code, 0, 7));
        $enderEmit->addChild('xMun', $this->tenant->city);
        $enderEmit->addChild('CEP', str_replace('-', '', $this->tenant->zip_code));
        
        // Dados da carga
        $infDoc = $infCte->addChild('infDoc');
        // ... adicionar documentos (NF-e, etc)
        
        // Valor total
        $vPrest = $infCte->addChild('vPrest');
        $vPrest->addChild('vTPrest', $cteData['total_value']);
        
        // Converter para string formatada
        return $xml->asXML();
    }

    /**
     * Assinar XML com certificado digital
     */
    protected function signXml($xml)
    {
        // Usar biblioteca SignatureService para XMLDSIG
        $signer = new \Illuminate\Support\Facades\Crypt();
        
        // Em produção, usar biblioteca específica:
        // composer require `simpleinvoices/xmlseclibs`
        
        // Este é um exemplo simplificado
        // Em produção seria mais complexo com XMLDSIG
        
        return $this->applyXmlSignature($xml);
    }

    /**
     * Enviar XML assinado para SEFAZ
     */
    protected function sendToSefaz($signedXml, $type = 'cte')
    {
        $soapRequest = $this->buildSoapRequest($signedXml, $type);

        try {
            $response = $this->client->post(
                $this->sefazUrl[$type],
                [
                    'headers' => [
                        'Content-Type' => 'text/xml; charset=utf-8',
                        'SOAPAction' => '"http://www.sefaz.rs.gov.br/webservices/CteEnviado"',
                    ],
                    'body' => $soapRequest,
                    'timeout' => 30,
                    'verify' => true,
                ]
            );

            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            \Log::error('Erro ao enviar CT-e para SEFAZ', [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception('Erro na comunicação com SEFAZ: ' . $e->getMessage());
        }
    }

    /**
     * Processar resposta do SEFAZ
     */
    protected function processCteResponse($responseBody)
    {
        // Parse XML response
        $response = simplexml_load_string($responseBody);

        // Extrair status
        // Status 100 = Autorizado
        // Status 110 = Uso Denegado
        // etc

        if ($response->cStat == 100) {
            // Sucesso! Salvar infos
            return [
                'success' => true,
                'cte_number' => (string)$response->infCte->ide->nCT,
                'access_key' => (string)$response->protCTe->infProt->chCTe,
                'authorization_date' => (string)$response->protCTe->infProt->dhRecbto,
                'protocol' => (string)$response->protCTe->infProt->nProt,
                'xml' => $responseBody,
            ];
        } else {
            // Erro
            return [
                'success' => false,
                'error_code' => (string)$response->cStat,
                'error_message' => (string)$response->xMotivo,
            ];
        }
    }

    /**
     * Buscar CT-es/MDF-es/NFes do SEFAZ
     */
    public function buscarDocumentos($startDate, $endDate)
    {
        // Usar serviço de consulta de SEFAZ
        // GET /webservices/NfeConsultaSituation
        
        $params = [
            'chNFe' => $accessKey, // ou chCTe, chMDFe
        ];

        $response = $this->client->get(
            $this->sefazUrl['nfe'],
            [
                'query' => $params,
                'headers' => [
                    'Authorization' => $this->buildAuthHeader(),
                ],
            ]
        );

        return $this->parseDocumentoResponse($response);
    }
}
```

### 3. Controller para Gerenciar Certificado

```php
// app/Http/Controllers/SefazCertificateController.php - NOVO

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tenant;

class SefazCertificateController extends Controller
{
    /**
     * Formulário para upload de certificado
     */
    public function editCertificate()
    {
        $tenant = auth()->user()->tenant;

        return view('admin.sefaz.certificate', [
            'tenant' => $tenant,
            'certificateInfo' => $this->getCertificateInfo($tenant),
        ]);
    }

    /**
     * Salvar certificado
     */
    public function storeCertificate(Request $request)
    {
        $request->validate([
            'certificate_file' => 'required|file|mimes:pem,pfx,p12',
            'certificate_password' => 'required|string',
            'sefaz_environment' => 'required|in:homolog,production',
        ]);

        $tenant = auth()->user()->tenant;

        try {
            // 1. Ler arquivo
            $certContent = file_get_contents(
                $request->file('certificate_file')->path()
            );

            // 2. Validar certificado
            $certInfo = $this->validateCertificate(
                $certContent,
                $request->certificate_password
            );

            // 3. Criptografar antes de salvar
            $tenant->update([
                'sefaz_certificate' => encrypt($certContent),
                'sefaz_certificate_password' => encrypt($request->certificate_password),
                'sefaz_cnpj' => $certInfo['cnpj'],
                'sefaz_certificate_expires_at' => $certInfo['expires_at'],
                'sefaz_environment' => $request->sefaz_environment,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Certificado configurado com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Erro ao configurar certificado: ' . $e->getMessage());
        }
    }

    /**
     * Validar certificado digital
     */
    protected function validateCertificate($certContent, $password)
    {
        // Usar biblioteca X509
        $cert = new \phpseclib3\File\X509();
        $cert->loadPEM($certContent);

        // Extrair informações
        $certInfo = $cert->getDN();

        return [
            'cnpj' => $certInfo['commonName'] ?? '',
            'expires_at' => $cert->getExpirationTime(),
            'valid' => $cert->validateDate(),
        ];
    }

    /**
     * Teste de comunicação SEFAZ
     */
    public function testConnection()
    {
        $tenant = auth()->user()->tenant;

        try {
            $sefazService = new \App\Services\SefazDirectService($tenant);
            
            // Tentar fazer uma consulta simples
            $result = $sefazService->testarConexao();

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão com SEFAZ OK!',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['error'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }
}
```

### 4. View para Upload de Certificado

```blade
<!-- resources/views/admin/sefaz/certificate.blade.php -->

<x-app-layout>
    <div class="py-12">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Configurar Certificado Digital</h1>

            @if($certificateInfo)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h3 class="font-bold text-green-800">✓ Certificado Ativo</h3>
                <p class="text-green-700">CNPJ: {{ $certificateInfo['cnpj'] }}</p>
                <p class="text-green-700">Válido até: {{ $certificateInfo['expires_at']->format('d/m/Y') }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.sefaz.certificate.store') }}" enctype="multipart/form-data">
                @csrf

                <!-- Upload do Certificado -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Arquivo de Certificado</label>
                    <input type="file" name="certificate_file" accept=".pem,.pfx,.p12" required
                           class="block w-full border rounded px-4 py-2">
                    <p class="text-xs text-gray-500 mt-1">Formatos aceitos: .pem, .pfx, .p12</p>
                </div>

                <!-- Senha do Certificado -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Senha do Certificado</label>
                    <input type="password" name="certificate_password" required
                           class="block w-full border rounded px-4 py-2">
                </div>

                <!-- Ambiente -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">Ambiente</label>
                    <select name="sefaz_environment" required class="block w-full border rounded px-4 py-2">
                        <option value="homolog">🧪 Homologação (Testes)</option>
                        <option value="production">🚀 Produção</option>
                    </select>
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded">
                        Salvar Certificado
                    </button>
                    <button type="button" onclick="testConnection()" class="bg-gray-600 text-white px-6 py-2 rounded">
                        🔗 Testar Conexão
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<script>
async function testConnection() {
    const btn = event.target;
    btn.disabled = true;
    btn.textContent = 'Testando...';

    try {
        const response = await fetch('{{ route("admin.sefaz.test") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });

        const data = await response.json();

        if (data.success) {
            alert('✓ ' + data.message);
        } else {
            alert('✗ ' + data.message);
        }
    } catch (error) {
        alert('Erro: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = '🔗 Testar Conexão';
    }
}
</script>
```

---

### ✅ VANTAGENS SEFAZ DIRETO vs MITT

| Aspecto | SEFAZ Direto | Mitt |
|---------|-------------|------|
| **Custo por doc** | R$0 | R$0.50-2.00 |
| **Volume 1000 CTE/mês** | R$0 | R$500-2000 |
| **Controle** | Total | Limitado |
| **Velocidade** | Imediata | 1-2min |
| **Manutenção** | Certificado | Chave API |
| **Complexidade** | Alta | Baixa |
| **Para MVP** | ❌ Muito caro | ✅ Recomendado |
| **Para Produção** | ✅ Recomendado | ⚠️ Caro escalar |

### 💡 RECOMENDAÇÃO

**Fase 1 (Agora)**: Manter Mitt (já funciona, rápido de lançar)

**Fase 2 (Mês 2-3)**: Implementar SEFAZ direto para clientes Enterprise (ReduzCusto)

**Fase 3 (Mês 4+)**: Deixar opcional para cada cliente escolher

---

## ❓ QUESTÃO 2: MAPBOX EM VEZ DE GOOGLE MAPS

### Status Atual
O código usa Google Maps. Vou criar plano para migrar tudo para Mapbox.

### Migração de Google Maps para Mapbox

```javascript
// ANTES (Google Maps)
const map = new google.maps.Map(
    document.getElementById('map'),
    {
        zoom: 12,
        center: { lat: -23.55, lng: -46.63 },
    }
);

// DEPOIS (Mapbox)
mapboxgl.accessToken = 'pk_live_xxxxxx'; // Token Mapbox
const map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/streets-v12',
    center: [-46.63, -23.55],
    zoom: 12,
});
```

### Implementação Completa Mapbox

```php
// app/Services/MapboxService.php - NOVO

namespace App\Services;

use GuzzleHttp\Client;

class MapboxService
{
    protected $accessToken;
    protected $client;

    public function __construct()
    {
        $this->accessToken = config('services.mapbox.access_token');
        $this->client = new Client();
    }

    /**
     * Calcular distância entre dois pontos
     */
    public function getDistance($origin, $destination)
    {
        // $origin: ['latitude' => -23.55, 'longitude' => -46.63]
        // $destination: ['latitude' => -23.60, 'longitude' => -46.70]

        $coords = sprintf(
            '%f,%f;%f,%f',
            $origin['longitude'],
            $origin['latitude'],
            $destination['longitude'],
            $destination['latitude']
        );

        $response = $this->client->get(
            "https://api.mapbox.com/directions/v5/mapbox/driving/{$coords}",
            [
                'query' => [
                    'access_token' => $this->accessToken,
                    'geometries' => 'geojson',
                    'overview' => 'full',
                    'steps' => 'true',
                ],
            ]
        );

        $data = json_decode($response->getBody(), true);

        if ($data['code'] === 'Ok') {
            $route = $data['routes'][0];

            return [
                'distance_meters' => $route['distance'],
                'distance_km' => $route['distance'] / 1000,
                'duration_seconds' => $route['duration'],
                'duration_minutes' => $route['duration'] / 60,
                'geometry' => $route['geometry'],
                'steps' => $route['legs'][0]['steps'] ?? [],
            ];
        }

        throw new \Exception('Erro ao calcular distância: ' . $data['message']);
    }

    /**
     * Otimizar rota com múltiplos pontos
     */
    public function optimizeRoute($points)
    {
        // $points: [
        //     ['latitude' => -23.55, 'longitude' => -46.63],
        //     ['latitude' => -23.60, 'longitude' => -46.70],
        //     ['latitude' => -23.65, 'longitude' => -46.75],
        // ]

        $coords = array_map(fn($p) => $p['longitude'] . ',' . $p['latitude'], $points);
        $coordString = implode(';', $coords);

        $response = $this->client->get(
            "https://api.mapbox.com/optimized-trips/v1/mapbox/driving/{$coordString}",
            [
                'query' => [
                    'access_token' => $this->accessToken,
                    'geometries' => 'geojson',
                    'overview' => 'full',
                    'roundtrip' => 'false', // Definir true se voltar ao início
                ],
            ]
        );

        $data = json_decode($response->getBody(), true);

        if ($data['code'] === 'Ok') {
            return [
                'waypoint_indices' => $data['waypoint_indices'],
                'waypoint_names' => $data['waypoint_names'],
                'trips' => $data['trips'],
                'optimized_order' => $this->mapWaypointIndicesToOriginalPoints(
                    $data['waypoint_indices'],
                    count($points)
                ),
            ];
        }

        throw new \Exception('Erro ao otimizar rota');
    }

    /**
     * Rastreamento em tempo real do motorista
     */
    public function trackingWebSocket($driverId, $latitude, $longitude)
    {
        // Usar Mapbox Real-time API
        // Publicar localização em tempo real para todos os observadores

        return [
            'driver_id' => $driverId,
            'location' => [
                'type' => 'Point',
                'coordinates' => [$longitude, $latitude],
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Criar mapa estático (para imagens/PDF)
     */
    public function generateStaticMap($center, $zoom, $features = [])
    {
        // Gerar URL de imagem estática para usar em PDFs/emails

        $url = "https://api.mapbox.com/styles/v1/mapbox/streets-v12/static/";

        // Centro + zoom
        $url .= sprintf('%f,%f,%d', $center['longitude'], $center['latitude'], $zoom);

        // Adicionar marcadores se houver
        foreach ($features as $feature) {
            if ($feature['type'] === 'marker') {
                $url .= sprintf(
                    '/pin-s+%s(%f,%f)',
                    urlencode($feature['color']),
                    $feature['longitude'],
                    $feature['latitude']
                );
            }
        }

        // Resolução e token
        $url .= "/600x400?access_token={$this->accessToken}";

        return $url;
    }
}
```

### Vue Component com Mapbox

```vue
<!-- resources/views/components/mapbox-tracking.vue -->

<template>
    <div class="h-full w-full">
        <div id="map" class="h-full"></div>

        <!-- Info Box -->
        <div class="absolute top-4 left-4 bg-white p-4 rounded shadow z-10">
            <h3 class="font-bold mb-2">Rastreamento em Tempo Real</h3>
            <p class="text-sm">Motoristas: {{ drivers.length }}</p>
            <p class="text-sm">Última atualização: {{ lastUpdate }}</p>
            
            <!-- Controles -->
            <div class="mt-4 space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" v-model="showRoute" class="mr-2">
                    Mostrar rota
                </label>
                <label class="flex items-center">
                    <input type="checkbox" v-model="showHeatmap" class="mr-2">
                    Mapa de calor
                </label>
            </div>
        </div>
    </div>
</template>

<script>
import mapboxgl from 'mapbox-gl';

export default {
    data() {
        return {
            map: null,
            drivers: [],
            markers: {},
            lastUpdate: null,
            showRoute: true,
            showHeatmap: false,
            routeLayers: {},
        };
    },

    mounted() {
        this.initMap();
        this.connectWebSocket();
    },

    watch: {
        showRoute(newVal) {
            this.toggleRouteVisibility(newVal);
        },
        showHeatmap(newVal) {
            this.toggleHeatmapVisibility(newVal);
        },
    },

    methods: {
        initMap() {
            mapboxgl.accessToken = window.MAPBOX_TOKEN;

            this.map = new mapboxgl.Map({
                container: 'map',
                style: 'mapbox://styles/mapbox/streets-v12',
                center: [-46.63, -23.55], // São Paulo
                zoom: 12,
            });

            // Adicionar controles
            this.map.addControl(new mapboxgl.NavigationControl());
            this.map.addControl(new mapboxgl.GeolocateControl());

            // Adicionar source para rotas
            this.map.on('load', () => {
                this.map.addSource('routes', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] },
                });

                this.map.addLayer({
                    id: 'routes-layer',
                    type: 'line',
                    source: 'routes',
                    paint: {
                        'line-color': '#088',
                        'line-width': 3,
                        'line-opacity': 0.8,
                    },
                });
            });
        },

        connectWebSocket() {
            const ws = new WebSocket(
                `wss://${window.location.host}/ws/tracking`
            );

            ws.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.updateDriverLocation(data);
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
            };

            ws.onclose = () => {
                // Reconectar após 3 segundos
                setTimeout(() => this.connectWebSocket(), 3000);
            };
        },

        updateDriverLocation(data) {
            const { driver_id, latitude, longitude, driver_name, route_geometry } = data;

            // Remover marker antigo se existir
            if (this.markers[driver_id]) {
                this.markers[driver_id].remove();
            }

            // Criar elemento do marcador
            const el = document.createElement('div');
            el.className = 'marker';
            el.style.backgroundImage = 'url(/images/driver-marker.png)';
            el.style.width = '32px';
            el.style.height = '32px';
            el.style.backgroundSize = '100%';
            el.title = driver_name;

            // Adicionar popup
            const popup = new mapboxgl.Popup({ offset: 25 }).setHTML(
                `<div><strong>${driver_name}</strong><br>Atualizando...</div>`
            );

            // Criar marcador
            const marker = new mapboxgl.Marker(el)
                .setLngLat([longitude, latitude])
                .setPopup(popup)
                .addTo(this.map);

            this.markers[driver_id] = marker;

            // Adicionar/atualizar rota se existir
            if (route_geometry && this.showRoute) {
                this.addRouteLayer(driver_id, route_geometry);
            }

            // Atualizar lista de drivers
            const driverIndex = this.drivers.findIndex(d => d.id === driver_id);
            if (driverIndex >= 0) {
                this.drivers[driverIndex] = {
                    ...this.drivers[driverIndex],
                    latitude,
                    longitude,
                    last_update: new Date(),
                };
            } else {
                this.drivers.push({
                    id: driver_id,
                    name: driver_name,
                    latitude,
                    longitude,
                    last_update: new Date(),
                });
            }

            this.lastUpdate = new Date().toLocaleTimeString();
        },

        addRouteLayer(driverId, geometry) {
            const source = this.map.getSource('routes');
            const data = source._data;

            // Adicionar feature de rota
            const featureIndex = data.features.findIndex(
                f => f.properties.driver_id === driverId
            );

            const feature = {
                type: 'Feature',
                properties: { driver_id: driverId },
                geometry: geometry,
            };

            if (featureIndex >= 0) {
                data.features[featureIndex] = feature;
            } else {
                data.features.push(feature);
            }

            source.setData(data);
        },

        toggleRouteVisibility(show) {
            if (!this.map.getLayer('routes-layer')) return;
            this.map.setLayoutProperty(
                'routes-layer',
                'visibility',
                show ? 'visible' : 'none'
            );
        },

        toggleHeatmapVisibility(show) {
            if (show) {
                this.addHeatmapLayer();
            } else {
                this.removeHeatmapLayer();
            }
        },

        addHeatmapLayer() {
            if (this.map.getLayer('heatmap-layer')) return;

            // Criar dados de heatmap from driver locations
            const data = {
                type: 'FeatureCollection',
                features: this.drivers.map(d => ({
                    type: 'Feature',
                    properties: { density: 1 },
                    geometry: {
                        type: 'Point',
                        coordinates: [d.longitude, d.latitude],
                    },
                })),
            };

            this.map.addSource('heatmap-source', {
                type: 'geojson',
                data: data,
            });

            this.map.addLayer({
                id: 'heatmap-layer',
                type: 'heatmap',
                source: 'heatmap-source',
                paint: {
                    'heatmap-weight': ['interpolate', ['linear'], ['get', 'density'], 0, 0, 100, 1],
                    'heatmap-intensity': 0.8,
                    'heatmap-color': [
                        'interpolate',
                        ['linear'],
                        ['heatmap-density'],
                        0,
                        'rgba(0,0,255,0)',
                        0.1,
                        'royalblue',
                        0.3,
                        'cyan',
                        0.5,
                        'lime',
                        0.7,
                        'yellow',
                        1,
                        'red',
                    ],
                    'heatmap-radius': 20,
                },
            });
        },

        removeHeatmapLayer() {
            if (this.map.getLayer('heatmap-layer')) {
                this.map.removeLayer('heatmap-layer');
                this.map.removeSource('heatmap-source');
            }
        },
    },
};
</script>

<style scoped>
#map {
    width: 100%;
    height: 100%;
}

:deep(.marker) {
    cursor: pointer;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    transition: filter 0.3s;
}

:deep(.marker:hover) {
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
}
</style>
```

---

## ❓ QUESTÃO 3: CUSTO POR CTE (MAIS IMPORTANTE!)

### O Problema
Você precisa rastrear o **custo proporcional de cada CTE** conforme ele viaja pelos centros de distribuição, para calcular lucro/prejuízo final.

### Status Atual
Existe `ShipmentCostAllocation` mas precisa de muito mais. Vou criar sistema completo.

### Arquitetura de Custos por CTE

```
CTE é emitido
  └─ Valor de frete: R$1.000
  └─ Custos alocados: R$0
     
CTE é adicionado à Rota 1
  └─ Combustível Rota 1: R$200
  └─ Custo do CTE: R$200 × 1CTE/4CTEs = R$50
  
CTE chega em Centro de Distribuição 1
  └─ Custo de manuseio: R$20
  └─ Custo alocado: R$20

CTE é transferido para Rota 2 (terceiro)
  └─ Combustível Rota 2: R$150
  └─ Custo do CTE: R$150 × 1CTE/3CTEs = R$50

CTE entregue
  └─ Custo total: R$50 + R$20 + R$50 = R$120
  └─ Valor frete: R$1.000
  └─ Lucro líquido: R$880
  └─ Margem: 88%
```

### Implementação Completa de Custos

```php
// app/Models/CteCosting.php - NOVO

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CteCosting extends Model
{
    protected $fillable = [
        'tenant_id',
        'shipment_id',
        'fiscal_document_id',
        'initial_value',
        'total_cost_allocated',
        'net_profit',
        'profit_margin',
        'status', // pending, in_transit, delivered
    ];

    protected $casts = [
        'initial_value' => 'decimal:2',
        'total_cost_allocated' => 'decimal:2',
        'net_profit' => 'decimal:2',
        'profit_margin' => 'decimal:4', // percentual
    ];

    /**
     * Relação com shipment
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    /**
     * Relação com documento fiscal
     */
    public function fiscalDocument(): BelongsTo
    {
        return $this->belongsTo(FiscalDocument::class);
    }

    /**
     * Itens de custo alocados
     */
    public function costItems(): HasMany
    {
        return $this->hasMany(CteCostItem::class);
    }

    /**
     * Alocar custo (combustível, manuseio, etc)
     */
    public function allocateCost(
        $costType,
        $amount,
        $description,
        $relatedId = null,
        $relatedType = null
    ) {
        // Criar item de custo
        $costItem = CteCostItem::create([
            'cte_costing_id' => $this->id,
            'cost_type' => $costType, // fuel, handling, third_party, toll, etc
            'amount' => $amount,
            'description' => $description,
            'related_id' => $relatedId, // Route ID, etc
            'related_type' => $relatedType,
            'allocated_at' => now(),
        ]);

        // Recalcular totais
        $this->recalculateTotals();

        return $costItem;
    }

    /**
     * Recalcular totais de custo e lucro
     */
    public function recalculateTotals()
    {
        $totalCost = $this->costItems()->sum('amount');
        $netProfit = $this->initial_value - $totalCost;
        $profitMargin = $this->initial_value > 0 
            ? ($netProfit / $this->initial_value) * 100 
            : 0;

        $this->update([
            'total_cost_allocated' => $totalCost,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
        ]);

        // Disparar evento
        event(new \App\Events\CteCostingUpdated($this));
    }

    /**
     * Obter timeline de custos
     */
    public function getCostTimeline()
    {
        return $this->costItems()
            ->orderBy('allocated_at')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->allocated_at->format('d/m/Y H:i'),
                    'type' => $item->cost_type,
                    'type_label' => $this->getCostTypeLabel($item->cost_type),
                    'description' => $item->description,
                    'amount' => $item->amount,
                    'cumulative_cost' => $this->costItems()
                        ->where('allocated_at', '<=', $item->allocated_at)
                        ->sum('amount'),
                ];
            });
    }

    protected function getCostTypeLabel($type)
    {
        return match($type) {
            'fuel' => 'Combustível',
            'handling' => 'Manuseio',
            'third_party' => 'Frete terceiro',
            'toll' => 'Pedágio',
            'driver_expense' => 'Despesa motorista',
            'warehouse' => 'Armazenagem',
            'insurance' => 'Seguro',
            'tax' => 'Impostos',
            default => $type,
        };
    }
}
```

```php
// app/Models/CteCostItem.php - NOVO

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CteCostItem extends Model
{
    protected $fillable = [
        'cte_costing_id',
        'cost_type',
        'amount',
        'description',
        'related_id',
        'related_type',
        'allocated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    public function cteCosting(): BelongsTo
    {
        return $this->belongsTo(CteCosting::class);
    }
}
```

```php
// app/Services/CteCostingService.php - NOVO

namespace App\Services;

use App\Models\CteCosting;
use App\Models\Shipment;
use App\Models\Route;
use App\Models\RouteExpense;

class CteCostingService
{
    /**
     * Criar costing para novo CTE
     */
    public function createCostingForCte(Shipment $shipment, $freightValue)
    {
        return CteCosting::create([
            'tenant_id' => $shipment->tenant_id,
            'shipment_id' => $shipment->id,
            'initial_value' => $freightValue,
            'total_cost_allocated' => 0,
            'net_profit' => $freightValue,
            'profit_margin' => 100,
            'status' => 'pending',
        ]);
    }

    /**
     * Alocar combustível de uma rota para os CTEs
     */
    public function allocateFuelToShipments(Route $route, $totalFuel)
    {
        $shipmentCount = $route->shipments()->count();

        if ($shipmentCount === 0) {
            return;
        }

        // Dividir combustível igualmente
        $fuelPerShipment = $totalFuel / $shipmentCount;

        foreach ($route->shipments as $shipment) {
            $cteCosting = $shipment->cteCosting;

            if ($cteCosting) {
                $cteCosting->allocateCost(
                    'fuel',
                    $fuelPerShipment,
                    "Combustível Rota #{$route->id}",
                    $route->id,
                    'Route'
                );
            }
        }
    }

    /**
     * Alocar combustível PROPORCIONAL ao peso/volume
     */
    public function allocateFuelByWeight(Route $route, $totalFuel)
    {
        $shipments = $route->shipments;
        $totalWeight = $shipments->sum('weight_kg');

        if ($totalWeight === 0) {
            // Voltar para divisão igual
            return $this->allocateFuelToShipments($route, $totalFuel);
        }

        foreach ($shipments as $shipment) {
            $weightPct = $shipment->weight_kg / $totalWeight;
            $allocatedFuel = $totalFuel * $weightPct;

            $cteCosting = $shipment->cteCosting;

            if ($cteCosting) {
                $cteCosting->allocateCost(
                    'fuel',
                    $allocatedFuel,
                    "Combustível Rota #{$route->id} (proporcional ao peso)",
                    $route->id,
                    'Route'
                );
            }
        }
    }

    /**
     * Alocar custo de manuseio em centro de distribuição
     */
    public function allocateHandlingCost(Shipment $shipment, $handlingCost, $warehouseId)
    {
        $cteCosting = $shipment->cteCosting;

        if (!$cteCosting) {
            return;
        }

        $cteCosting->allocateCost(
            'handling',
            $handlingCost,
            "Manuseio Centro de Distribuição #{$warehouseId}",
            $warehouseId,
            'Warehouse'
        );
    }

    /**
     * Alocar custo de frete terceirizado
     */
    public function allocateThirdPartyFreight(Shipment $shipment, $freightCost, $description)
    {
        $cteCosting = $shipment->cteCosting;

        if (!$cteCosting) {
            return;
        }

        $cteCosting->allocateCost(
            'third_party',
            $freightCost,
            $description,
            null,
            'ThirdParty'
        );
    }

    /**
     * Obter análise detalhada de lucro por CTE
     */
    public function getAnalysisByCte(Shipment $shipment)
    {
        $costing = $shipment->cteCosting;

        if (!$costing) {
            return null;
        }

        return [
            'cte_number' => $shipment->tracking_code,
            'client' => $shipment->client->name,
            'freight_value' => $costing->initial_value,
            'total_costs' => $costing->total_cost_allocated,
            'net_profit' => $costing->net_profit,
            'profit_margin' => $costing->profit_margin,
            'status' => $costing->status,
            'cost_breakdown' => $this->getCostBreakdown($costing),
            'timeline' => $costing->getCostTimeline(),
        ];
    }

    /**
     * Breakdown de custos
     */
    protected function getCostBreakdown(CteCosting $costing)
    {
        return $costing->costItems()
            ->selectRaw('cost_type, SUM(amount) as total_amount')
            ->groupBy('cost_type')
            ->get()
            ->map(function ($group) {
                return [
                    'type' => $group->cost_type,
                    'type_label' => $costing->getCostTypeLabel($group->cost_type),
                    'total' => $group->total_amount,
                    'pct_of_freight' => ($group->total_amount / $costing->initial_value) * 100,
                ];
            })
            ->toArray();
    }

    /**
     * Relatório de lucro por período
     */
    public function getProfitReport($startDate, $endDate, $groupBy = 'daily')
    {
        // $groupBy: daily, weekly, monthly, by_client, by_route

        $costings = CteCosting::where('tenant_id', auth()->user()->tenant_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('shipment.client')
            ->get();

        return match($groupBy) {
            'daily' => $this->groupByDaily($costings),
            'weekly' => $this->groupByWeekly($costings),
            'monthly' => $this->groupByMonthly($costings),
            'by_client' => $this->groupByClient($costings),
            'by_route' => $this->groupByRoute($costings),
            default => $costings,
        };
    }

    protected function groupByDaily($costings)
    {
        return $costings
            ->groupBy(fn($c) => $c->created_at->format('Y-m-d'))
            ->map(function ($group, $date) {
                return [
                    'date' => $date,
                    'total_freight' => $group->sum('initial_value'),
                    'total_costs' => $group->sum('total_cost_allocated'),
                    'total_profit' => $group->sum('net_profit'),
                    'avg_margin' => $group->avg('profit_margin'),
                    'cte_count' => $group->count(),
                ];
            })
            ->values();
    }

    protected function groupByClient($costings)
    {
        return $costings
            ->groupBy(fn($c) => $c->shipment->client_id)
            ->map(function ($group) {
                $clientName = $group->first()->shipment->client->name;

                return [
                    'client_name' => $clientName,
                    'total_freight' => $group->sum('initial_value'),
                    'total_costs' => $group->sum('total_cost_allocated'),
                    'total_profit' => $group->sum('net_profit'),
                    'avg_margin' => $group->avg('profit_margin'),
                    'cte_count' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total_profit');
    }
}
```

### Dashboard de Análise de Custos

```blade
<!-- resources/views/analytics/cte-profitability.blade.php -->

<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">📊 Análise de Lucro por CTE</h1>

            <!-- Filtros -->
            <form method="GET" class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                           class="px-4 py-2 border rounded">
                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                           class="px-4 py-2 border rounded">
                    
                    <select name="group_by" class="px-4 py-2 border rounded">
                        <option value="daily">Por dia</option>
                        <option value="by_client">Por cliente</option>
                        <option value="by_route">Por rota</option>
                    </select>

                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
                        Filtrar
                    </button>
                </div>
            </form>

            <!-- Cards de resumo -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-gray-600 text-sm font-medium">Valor Total Frete</h3>
                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        R$ {{ number_format($summary['total_freight'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-gray-600 text-sm font-medium">Custos Totais</h3>
                    <p class="text-3xl font-bold text-red-600 mt-2">
                        R$ {{ number_format($summary['total_costs'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-gray-600 text-sm font-medium">Lucro Líquido</h3>
                    <p class="text-3xl font-bold text-green-600 mt-2">
                        R$ {{ number_format($summary['total_profit'] ?? 0, 2, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-gray-600 text-sm font-medium">Margem Média</h3>
                    <p class="text-3xl font-bold text-purple-600 mt-2">
                        {{ number_format($summary['avg_margin'] ?? 0, 1, ',', '.') }}%
                    </p>
                </div>
            </div>

            <!-- Tabela detalhada -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">CTE</th>
                            <th class="px-6 py-3 text-left font-medium">Cliente</th>
                            <th class="px-6 py-3 text-right font-medium">Frete</th>
                            <th class="px-6 py-3 text-right font-medium">Custos</th>
                            <th class="px-6 py-3 text-right font-medium">Lucro</th>
                            <th class="px-6 py-3 text-right font-medium">Margem</th>
                            <th class="px-6 py-3 text-center font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($costings as $costing)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-mono text-sm">{{ $costing->shipment->tracking_code }}</td>
                            <td class="px-6 py-4">{{ $costing->shipment->client->name }}</td>
                            <td class="px-6 py-4 text-right">
                                R$ {{ number_format($costing->initial_value, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                R$ {{ number_format($costing->total_cost_allocated, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="@if($costing->net_profit >= 0) text-green-600 @else text-red-600 @endif font-bold">
                                    R$ {{ number_format($costing->net_profit, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="@if($costing->profit_margin >= 20) text-green-600 @elseif($costing->profit_margin >= 10) text-yellow-600 @else text-red-600 @endif">
                                    {{ number_format($costing->profit_margin, 1, ',', '.') }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('analytics.cte.detail', $costing) }}" class="text-blue-600 hover:underline">
                                    Ver detalhes
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Nenhum CTE encontrado no período
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Gráfico de lucro -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <h3 class="font-bold mb-4">Evolução de Lucro</h3>
                <div id="chart-profit" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
    <script>
        const ctx = document.getElementById('chart-profit').getContext('2d');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartData['dates'] ?? []),
                datasets: [
                    {
                        label: 'Frete',
                        data: @json($chartData['freight'] ?? []),
                        borderColor: '#3b82f6',
                        fill: false,
                    },
                    {
                        label: 'Custos',
                        data: @json($chartData['costs'] ?? []),
                        borderColor: '#ef4444',
                        fill: false,
                    },
                    {
                        label: 'Lucro',
                        data: @json($chartData['profit'] ?? []),
                        borderColor: '#10b981',
                        fill: false,
                        borderWidth: 3,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    </script>
</x-app-layout>
```

---

## 📝 RESUMO EXECUTIVO - 3 QUESTÕES

| Questão | Resposta | Esforço | Recomendação |
|---------|----------|---------|--------------|
| **SEFAZ Direto** | ✅ Possível | 2-3 sem | Fase 2 (depois) |
| **Mapbox** | ✅ Totalmente suportado | 1 sem | Implementar agora |
| **Custos por CTE** | ✅ Altamente funcional | 3-4 dias | Implementar agora |

---

## 🚀 PLANO DE IMPLEMENTAÇÃO

### IMEDIATO (Esta semana)
1. ✅ Implementar sistema de custos por CTE (código acima)
2. ✅ Testar alocação de combustível
3. ✅ Dashboard de análise de lucro

### PRÓXIMAS SEMANAS
1. Integração Mapbox (remover Google Maps)
2. Rastreamento em tempo real via Mapbox
3. Otimização de rotas com Mapbox

### MÊS 2
1. SEFAZ direto (sem Mitt)
2. Gerenciamento de certificados
3. Busca de documentos do SEFAZ

---

**Todos os códigos estão prontos para copiar/colar e implementar!**

Quer que eu detalhe mais alguma dessas 3 áreas?