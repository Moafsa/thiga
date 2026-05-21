<?php

namespace App\Services;

use SimpleXMLElement;
use Exception;
use Illuminate\Support\Facades\Log;

class CteXmlParserService
{
    /**
     * Parse CT-e XML file and extract shipment data
     * 
     * @param string $xmlContent XML content as string
     * @return array Extracted data including addresses, dates, and other shipment info
     * @throws Exception
     */
    public function parseXml(string $xmlContent): array
    {
        try {
            // Load XML with namespaces preserved
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($xmlContent);
            
            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new Exception('Invalid XML format: ' . json_encode($errors));
            }
            
            // Register namespaces
            $xml->registerXPathNamespace('cte', 'http://www.portalfiscal.inf.br/cte');
            
            // Extract access key (chave de acesso) - try multiple methods
            $accessKey = $this->extractAccessKey($xml);
            
            // Extract origin address (remetente)
            $origin = $this->extractOriginAddress($xml);
            
            // Extract destination address (destinatário)
            $destination = $this->extractDestinationAddress($xml);
            
            // Extract dates
            $dates = $this->extractDates($xml);
            
            // Extract values and weights
            $values = $this->extractValues($xml);
            
            // Extract document number
            $documentNumber = $this->extractDocumentNumber($xml);

            // Extract fiscal/financial data for cost management
            $fiscal = $this->extractFiscalData($xml);
            
            Log::info('CT-e XML parsed successfully', [
                'access_key'      => $accessKey,
                'document_number' => $documentNumber,
                'origin'          => $origin['name'] ?? 'N/A',
                'destination'     => $destination['name'] ?? 'N/A',
                'freight_value'   => $values['value'] ?? null,
                'goods_value'     => $fiscal['goods_value'] ?? null,
                'tax_total'       => $fiscal['tax_total'] ?? null,
                'nfe_key'         => $fiscal['nfe_key'] ?? null,
            ]);
            
            return [
                'access_key'          => $accessKey,
                'document_number'     => $documentNumber,
                'origin'              => $origin,
                'destination'         => $destination,
                'pickup_date'         => $dates['pickup_date'] ?? null,
                'delivery_date'       => $dates['delivery_date'] ?? null,
                'value'               => $values['value'] ?? null,    // vTPrest — valor do frete (receita)
                'goods_value'         => $fiscal['goods_value'] ?? null, // vCarga — valor da mercadoria
                'weight'              => $values['weight'] ?? null,
                'volume'              => $values['volume'] ?? null,
                'quantity'            => $values['quantity'] ?? 1,
                // Fiscal / custo
                'tax_total'           => $fiscal['tax_total'] ?? 0.00, // vTotTrib — imposto total
                'nfe_key'             => $fiscal['nfe_key'] ?? null,   // chave NF-e vinculada
                'is_simples_nacional' => $fiscal['is_simples_nacional'] ?? false,
                'origin_city'         => $fiscal['origin_city'] ?? null,  // município de início (xMunIni)
                'origin_state'        => $fiscal['origin_state'] ?? null,
                'destination_city'    => $fiscal['destination_city'] ?? null, // município de fim (xMunFim)
                'destination_state'   => $fiscal['destination_state'] ?? null,
                'cfop'                => $fiscal['cfop'] ?? null,
                'nat_op'              => $fiscal['nat_op'] ?? null,
            ];
        } catch (Exception $e) {
            Log::error('Failed to parse CT-e XML', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new Exception('Failed to parse CT-e XML: ' . $e->getMessage());
        }
    }

    /**
     * Remove XML namespaces for easier parsing (fallback method)
     */
    protected function removeNamespaces(string $xmlContent): string
    {
        // Remove xmlns attributes
        $xmlContent = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlContent);
        return $xmlContent;
    }

    /**
     * Extract access key from XML
     */
    protected function extractAccessKey(SimpleXMLElement $xml): ?string
    {
        // Try to get from protCTe->infProt->chCTe (most reliable)
        try {
            $xml->registerXPathNamespace('cte', 'http://www.portalfiscal.inf.br/cte');
            $protCTe = $xml->xpath('//cte:protCTe/cte:infProt/cte:chCTe | //protCTe/infProt/chCTe');
            if (!empty($protCTe) && strlen((string)$protCTe[0]) === 44) {
                return (string)$protCTe[0];
            }
        } catch (\Exception $e) {
            Log::warning('XPath failed for access key from protCTe', ['error' => $e->getMessage()]);
        }
        
        // Try direct access
        try {
            $chCTe = $xml->protCTe->infProt->chCTe ?? $xml->protCTe->infProt->chCTe ?? null;
            if ($chCTe && strlen((string)$chCTe) === 44) {
                return (string)$chCTe;
            }
        } catch (\Exception $e) {
            // Continue
        }
        
        // Try to get from infCte Id attribute
        try {
            $infCte = $xml->xpath('//cte:infCte/@Id | //infCte/@Id');
            if (!empty($infCte)) {
                $id = (string)$infCte[0];
                // Remove 'CTe' prefix if present
                $id = str_replace('CTe', '', $id);
                if (strlen($id) === 44) {
                    return $id;
                }
            }
        } catch (\Exception $e) {
            // Continue
        }
        
        // Try direct access to Id attribute
        try {
            $infCte = $xml->CTe->infCte ?? $xml->infCte ?? null;
            if ($infCte && isset($infCte['Id'])) {
                $id = (string)$infCte['Id'];
                $id = str_replace('CTe', '', $id);
                if (strlen($id) === 44) {
                    return $id;
                }
            }
        } catch (\Exception $e) {
            // Continue
        }

        return null;
    }

    /**
     * Extract document number from XML
     */
    protected function extractDocumentNumber(SimpleXMLElement $xml): ?string
    {
        // Try XPath first
        try {
            $nCT = $xml->xpath('//cte:ide/cte:nCT | //ide/nCT');
            if (!empty($nCT)) {
                return (string)$nCT[0];
            }
        } catch (\Exception $e) {
            // XPath failed, will use fallback
        }
        
        // Fallback
        $paths = [
            'CTe->infCte->ide->nCT',
            'infCte->ide->nCT',
            'infCTe->ide->nCT',
            'nCT',
        ];

        foreach ($paths as $path) {
            $value = $this->getXmlValue($xml, $path);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Extract origin address from XML (remetente)
     */
    protected function extractOriginAddress(SimpleXMLElement $xml): array
    {
        $address = [];
        
        // Try XPath first (more reliable with namespaces)
        try {
            // Register namespace
            $xml->registerXPathNamespace('cte', 'http://www.portalfiscal.inf.br/cte');
            
            $rem = $xml->xpath('//cte:rem | //rem');
            if (!empty($rem)) {
                $remNode = $rem[0];
                $enderReme = $remNode->xpath('cte:enderReme | enderReme');
                
                if (!empty($enderReme)) {
                    $endereco = $enderReme[0];
                    
                    // Build full address
                    $addressParts = array_filter([
                        (string)($endereco->xLgr ?? ''),
                        (string)($endereco->nro ?? ''),
                        (string)($endereco->xCpl ?? ''),
                    ]);
                    
                    $address = [
                        'name' => (string)($remNode->xNome ?? ''),
                        'cnpj' => (string)($remNode->CNPJ ?? $remNode->CPF ?? ''),
                        'address' => implode(', ', $addressParts),
                        'number' => (string)($endereco->nro ?? ''),
                        'complement' => (string)($endereco->xCpl ?? ''),
                        'neighborhood' => (string)($endereco->xBairro ?? ''),
                        'city' => (string)($endereco->xMun ?? ''),
                        'state' => (string)($endereco->UF ?? ''),
                        'zip_code' => preg_replace('/\D/', '', (string)($endereco->CEP ?? '')),
                        'email' => $this->extractContactEmail($remNode, $endereco),
                        'phone' => $this->extractContactPhone($remNode, $endereco),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('XPath failed for origin address', ['error' => $e->getMessage()]);
        }
        
        // Fallback: direct access without namespaces
        if (empty($address['address'])) {
            try {
                // Access directly through SimpleXML
                $rem = $xml->CTe->infCte->rem ?? $xml->infCte->rem ?? null;
                if ($rem) {
                    $enderReme = $rem->enderReme ?? null;
                    if ($enderReme) {
                        $addressParts = array_filter([
                            (string)($enderReme->xLgr ?? ''),
                            (string)($enderReme->nro ?? ''),
                            (string)($enderReme->xCpl ?? ''),
                        ]);
                        
                        $address = [
                            'name' => (string)($rem->xNome ?? ''),
                            'cnpj' => (string)($rem->CNPJ ?? $rem->CPF ?? ''),
                            'address' => implode(', ', $addressParts),
                            'number' => (string)($enderReme->nro ?? ''),
                            'complement' => (string)($enderReme->xCpl ?? ''),
                            'neighborhood' => (string)($enderReme->xBairro ?? ''),
                            'city' => (string)($enderReme->xMun ?? ''),
                            'state' => (string)($enderReme->UF ?? ''),
                            'zip_code' => preg_replace('/\D/', '', (string)($enderReme->CEP ?? '')),
                            'email' => $this->extractContactEmail($rem, $enderReme),
                            'phone' => $this->extractContactPhone($rem, $enderReme),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Direct access failed for origin address', ['error' => $e->getMessage()]);
            }
        }

        return $address;
    }

    /**
     * Extract destination address from XML (destinatário)
     */
    protected function extractDestinationAddress(SimpleXMLElement $xml): array
    {
        $address = [];
        
        // Try XPath first (more reliable with namespaces)
        try {
            // Register namespace
            $xml->registerXPathNamespace('cte', 'http://www.portalfiscal.inf.br/cte');
            
            $dest = $xml->xpath('//cte:dest | //dest');
            if (!empty($dest)) {
                $destNode = $dest[0];
                $enderDest = $destNode->xpath('cte:enderDest | enderDest');
                
                if (!empty($enderDest)) {
                    $endereco = $enderDest[0];
                    
                    // Build full address
                    $addressParts = array_filter([
                        (string)($endereco->xLgr ?? ''),
                        (string)($endereco->nro ?? ''),
                        (string)($endereco->xCpl ?? ''),
                    ]);
                    
                    $address = [
                        'name' => (string)($destNode->xNome ?? ''),
                        'cnpj' => (string)($destNode->CNPJ ?? $destNode->CPF ?? ''),
                        'address' => implode(', ', $addressParts),
                        'number' => (string)($endereco->nro ?? ''),
                        'complement' => (string)($endereco->xCpl ?? ''),
                        'neighborhood' => (string)($endereco->xBairro ?? ''),
                        'city' => (string)($endereco->xMun ?? ''),
                        'state' => (string)($endereco->UF ?? ''),
                        'zip_code' => preg_replace('/\D/', '', (string)($endereco->CEP ?? '')),
                        'email' => $this->extractContactEmail($destNode, $endereco),
                        'phone' => $this->extractContactPhone($destNode, $endereco),
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('XPath failed for destination address', ['error' => $e->getMessage()]);
        }
        
        // Fallback: direct access without namespaces
        if (empty($address['address'])) {
            try {
                // Access directly through SimpleXML
                $dest = $xml->CTe->infCte->dest ?? $xml->infCte->dest ?? null;
                if ($dest) {
                    $enderDest = $dest->enderDest ?? null;
                    if ($enderDest) {
                        $addressParts = array_filter([
                            (string)($enderDest->xLgr ?? ''),
                            (string)($enderDest->nro ?? ''),
                            (string)($enderDest->xCpl ?? ''),
                        ]);
                        
                        $address = [
                            'name' => (string)($dest->xNome ?? ''),
                            'cnpj' => (string)($dest->CNPJ ?? $dest->CPF ?? ''),
                            'address' => implode(', ', $addressParts),
                            'number' => (string)($enderDest->nro ?? ''),
                            'complement' => (string)($enderDest->xCpl ?? ''),
                            'neighborhood' => (string)($enderDest->xBairro ?? ''),
                            'city' => (string)($enderDest->xMun ?? ''),
                            'state' => (string)($enderDest->UF ?? ''),
                            'zip_code' => preg_replace('/\D/', '', (string)($enderDest->CEP ?? '')),
                            'email' => $this->extractContactEmail($dest, $enderDest),
                            'phone' => $this->extractContactPhone($dest, $enderDest),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Direct access failed for destination address', ['error' => $e->getMessage()]);
            }
        }

        return $address;
    }

    /**
     * Extract dates from XML
     */
    protected function extractDates(SimpleXMLElement $xml): array
    {
        $dates = [
            'pickup_date' => null,
            'delivery_date' => null,
        ];

        // Try XPath first
        try {
            $dhEmi = $xml->xpath('//cte:ide/cte:dhEmi | //ide/dhEmi');
            if (!empty($dhEmi)) {
                $value = (string)$dhEmi[0];
                try {
                    $date = new \DateTime($value);
                    $dates['pickup_date'] = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    // Invalid date format
                }
            }
        } catch (\Exception $e) {
            // XPath failed, will use fallback
        }

        // Fallback: Try to extract dates from various possible paths
        if (!$dates['pickup_date']) {
            $pickupPaths = [
                'CTe->infCte->ide->dhEmi',
                'infCte->ide->dhEmi',
                'infCTe->ide->dhEmi',
            ];

            foreach ($pickupPaths as $path) {
                $value = $this->getXmlValue($xml, $path);
                if ($value) {
                    try {
                        $date = new \DateTime($value);
                        $dates['pickup_date'] = $date->format('Y-m-d');
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }

        // If no pickup date found, use current date
        if (!$dates['pickup_date']) {
            $dates['pickup_date'] = date('Y-m-d');
        }

        // Delivery date defaults to pickup date
        $dates['delivery_date'] = $dates['pickup_date'];

        return $dates;
    }

    /**
     * Extract values, weight, volume from XML
     */
    protected function extractValues(SimpleXMLElement $xml): array
    {
        $values = [
            'value'    => null,
            'weight'   => null,
            'volume'   => null,
            'quantity' => 1,
        ];

        // Valor do frete (vTPrest)
        try {
            $vTPrest = $xml->xpath('//cte:vPrest/cte:vTPrest | //vPrest/vTPrest');
            if (!empty($vTPrest)) {
                $values['value'] = (float)(string)$vTPrest[0];
            }
        } catch (\Exception $e) {
            // XPath failed, will use fallback
        }

        if ($values['value'] === null) {
            foreach (['CTe->infCte->vPrest->vTPrest', 'infCte->vPrest->vTPrest', 'infCTe->vPrest->vTPrest'] as $path) {
                $v = $this->getXmlValue($xml, $path);
                if ($v !== null) {
                    $values['value'] = (float) $v;
                    break;
                }
            }
        }

        // Peso — preferência: PESO REAL ou PESO BASE DE CALCULO; fallback KG
        $weightLabels = ['PESO REAL', 'PESO BASE DE CALCULO', 'PESO BRUTO', 'KG'];
        foreach ($weightLabels as $label) {
            try {
                $nodes = $xml->xpath("//cte:infQ[cte:tpMed=\"{$label}\"]/cte:qCarga | //infQ[tpMed=\"{$label}\"]/qCarga");
                if (!empty($nodes) && (float)(string)$nodes[0] > 0) {
                    $values['weight'] = (float)(string)$nodes[0];
                    break;
                }
            } catch (\Exception $e) {
                // continue
            }
        }

        // Volume (M3)
        try {
            $vol = $xml->xpath('//cte:infQ[cte:tpMed="M3"]/cte:qCarga | //infQ[tpMed="M3"]/qCarga');
            if (!empty($vol)) {
                $values['volume'] = (float)(string)$vol[0];
            }
        } catch (\Exception $e) {
            // continue
        }

        // Quantidade (UNIDADE ou VOLUMES)
        foreach (['UNIDADE', 'VOLUMES', 'CAIXAS'] as $unit) {
            try {
                $qtd = $xml->xpath("//cte:infQ[cte:tpMed=\"{$unit}\"]/cte:qCarga | //infQ[tpMed=\"{$unit}\"]/qCarga");
                if (!empty($qtd) && (int)(string)$qtd[0] > 0) {
                    $values['quantity'] = (int)(string)$qtd[0];
                    break;
                }
            } catch (\Exception $e) {
                // continue
            }
        }

        return $values;
    }

    /**
     * Extract fiscal/financial data for the cost management module:
     * - vCarga (goods value)
     * - vTotTrib (total tax)
     * - chave NF-e vinculada
     * - CRT (tax regime — Simples Nacional detection)
     * - origin/destination municipalities
     * - CFOP and natureza da operação
     */
    protected function extractFiscalData(SimpleXMLElement $xml): array
    {
        $data = [
            'goods_value'         => null,
            'tax_total'           => 0.00,
            'nfe_key'             => null,
            'is_simples_nacional' => false,
            'origin_city'         => null,
            'origin_state'        => null,
            'destination_city'    => null,
            'destination_state'   => null,
            'cfop'                => null,
            'nat_op'              => null,
        ];

        try {
            // vCarga — valor da mercadoria transportada
            $vCarga = $xml->xpath('//cte:infCarga/cte:vCarga | //infCarga/vCarga');
            if (!empty($vCarga)) {
                $data['goods_value'] = (float)(string)$vCarga[0];
            }

            // vTotTrib — total de tributos (ICMS, ISS, etc.)
            $vTotTrib = $xml->xpath('//cte:imp/cte:vTotTrib | //imp/vTotTrib');
            if (!empty($vTotTrib)) {
                $data['tax_total'] = (float)(string)$vTotTrib[0];
            }

            // Chave da NF-e vinculada (dentro de infDoc)
            $nfeKey = $xml->xpath('//cte:infDoc/cte:infNFe/cte:chave | //infDoc/infNFe/chave');
            if (!empty($nfeKey)) {
                $data['nfe_key'] = (string)$nfeKey[0];
            }

            // CRT — regime tributário do emitente (1 = Simples Nacional)
            $crt = $xml->xpath('//cte:emit/cte:CRT | //emit/CRT');
            if (!empty($crt)) {
                $data['is_simples_nacional'] = ((int)(string)$crt[0] === 1);
            }

            // Município de início (origem operacional do trecho)
            $munIni = $xml->xpath('//cte:ide/cte:xMunIni | //ide/xMunIni');
            $ufIni  = $xml->xpath('//cte:ide/cte:UFIni | //ide/UFIni');
            if (!empty($munIni)) {
                $data['origin_city']  = (string)$munIni[0];
                $data['origin_state'] = !empty($ufIni) ? (string)$ufIni[0] : null;
            }

            // Município de fim (destino operacional do trecho)
            $munFim = $xml->xpath('//cte:ide/cte:xMunFim | //ide/xMunFim');
            $ufFim  = $xml->xpath('//cte:ide/cte:UFFim | //ide/UFFim');
            if (!empty($munFim)) {
                $data['destination_city']  = (string)$munFim[0];
                $data['destination_state'] = !empty($ufFim) ? (string)$ufFim[0] : null;
            }

            // CFOP e natureza da operação
            $cfop  = $xml->xpath('//cte:ide/cte:CFOP | //ide/CFOP');
            $natOp = $xml->xpath('//cte:ide/cte:natOp | //ide/natOp');
            if (!empty($cfop))  $data['cfop']   = (string)$cfop[0];
            if (!empty($natOp)) $data['nat_op'] = (string)$natOp[0];

        } catch (\Exception $e) {
            Log::warning('Failed to extract fiscal data from CTe XML', [
                'error' => $e->getMessage(),
            ]);
        }

        return $data;
    }

    /**
     * Extract email from rem/dest node or endereço.
     * CT-e: email pode vir em rem/email, dest/email.
     */
    protected function extractContactEmail(SimpleXMLElement $personNode, SimpleXMLElement $endereco): ?string
    {
        $raw = (string)($personNode->email ?? $personNode->Email ?? $personNode->{'e-mail'} ?? $personNode->{'E-mail'} ?? '');
        $raw = trim($raw);
        if ($raw !== '' && filter_var($raw, FILTER_VALIDATE_EMAIL)) {
            return $raw;
        }
        return null;
    }

    /**
     * Extract phone from rem/dest node or endereço.
     * CT-e: fone pode vir em rem/fone, dest/fone ou enderReme/fone, enderDest/fone.
     */
    protected function extractContactPhone(SimpleXMLElement $personNode, SimpleXMLElement $endereco): ?string
    {
        $raw = (string)($personNode->fone ?? $personNode->Fone ?? $endereco->fone ?? $endereco->Fone ?? '');
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }
        $digits = preg_replace('/\D/', '', $raw);
        return strlen($digits) >= 10 ? $raw : null;
    }

    /**
     * Get XML value by path
     */
    protected function getXmlValue(SimpleXMLElement $xml, string $path): ?string
    {
        $node = $this->getXmlNode($xml, $path);
        return $node ? (string) $node : null;
    }

    /**
     * Get XML node by path
     */
    protected function getXmlNode(SimpleXMLElement $xml, string $path): ?SimpleXMLElement
    {
        $parts = explode('->', $path);
        $current = $xml;

        foreach ($parts as $part) {
            if (!isset($current->$part)) {
                return null;
            }
            $current = $current->$part;
        }

        return $current;
    }
}

