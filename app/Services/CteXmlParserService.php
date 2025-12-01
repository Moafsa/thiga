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
            
            Log::info('CT-e XML parsed successfully', [
                'access_key' => $accessKey,
                'document_number' => $documentNumber,
                'origin' => $origin['name'] ?? 'N/A',
                'destination' => $destination['name'] ?? 'N/A',
            ]);
            
            return [
                'access_key' => $accessKey,
                'document_number' => $documentNumber,
                'origin' => $origin,
                'destination' => $destination,
                'pickup_date' => $dates['pickup_date'] ?? null,
                'delivery_date' => $dates['delivery_date'] ?? null,
                'value' => $values['value'] ?? null,
                'weight' => $values['weight'] ?? null,
                'volume' => $values['volume'] ?? null,
                'quantity' => $values['quantity'] ?? 1,
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
                        'cnpj' => (string)($remNode->CNPJ ?? ''),
                        'address' => implode(', ', $addressParts),
                        'number' => (string)($endereco->nro ?? ''),
                        'complement' => (string)($endereco->xCpl ?? ''),
                        'neighborhood' => (string)($endereco->xBairro ?? ''),
                        'city' => (string)($endereco->xMun ?? ''),
                        'state' => (string)($endereco->UF ?? ''),
                        'zip_code' => preg_replace('/\D/', '', (string)($endereco->CEP ?? '')),
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
                            'cnpj' => (string)($rem->CNPJ ?? ''),
                            'address' => implode(', ', $addressParts),
                            'number' => (string)($enderReme->nro ?? ''),
                            'complement' => (string)($enderReme->xCpl ?? ''),
                            'neighborhood' => (string)($enderReme->xBairro ?? ''),
                            'city' => (string)($enderReme->xMun ?? ''),
                            'state' => (string)($enderReme->UF ?? ''),
                            'zip_code' => preg_replace('/\D/', '', (string)($enderReme->CEP ?? '')),
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
                        'cnpj' => (string)($destNode->CNPJ ?? ''),
                        'address' => implode(', ', $addressParts),
                        'number' => (string)($endereco->nro ?? ''),
                        'complement' => (string)($endereco->xCpl ?? ''),
                        'neighborhood' => (string)($endereco->xBairro ?? ''),
                        'city' => (string)($endereco->xMun ?? ''),
                        'state' => (string)($endereco->UF ?? ''),
                        'zip_code' => preg_replace('/\D/', '', (string)($endereco->CEP ?? '')),
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
                            'cnpj' => (string)($dest->CNPJ ?? ''),
                            'address' => implode(', ', $addressParts),
                            'number' => (string)($enderDest->nro ?? ''),
                            'complement' => (string)($enderDest->xCpl ?? ''),
                            'neighborhood' => (string)($enderDest->xBairro ?? ''),
                            'city' => (string)($enderDest->xMun ?? ''),
                            'state' => (string)($enderDest->UF ?? ''),
                            'zip_code' => preg_replace('/\D/', '', (string)($enderDest->CEP ?? '')),
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
            'value' => null,
            'weight' => null,
            'volume' => null,
            'quantity' => 1,
        ];

        // Extract value using XPath
        try {
            $vTPrest = $xml->xpath('//cte:vPrest/cte:vTPrest | //vPrest/vTPrest');
            if (!empty($vTPrest)) {
                $values['value'] = (float)(string)$vTPrest[0];
            }
        } catch (\Exception $e) {
            // XPath failed, will use fallback
        }
        
        // Fallback to old method if XPath didn't work
        if ($values['value'] === null) {
            $valuePaths = [
                'CTe->infCte->vPrest->vTPrest',
                'infCte->vPrest->vTPrest',
                'infCTe->vPrest->vTPrest',
            ];

            foreach ($valuePaths as $path) {
                $value = $this->getXmlValue($xml, $path);
                if ($value) {
                    $values['value'] = (float) $value;
                    break;
                }
            }
        }

        // Extract weight - look for infQ where tpMed='KG'
        try {
            $infQ = $xml->xpath('//cte:infQ[cte:tpMed="KG"]/cte:qCarga | //infQ[tpMed="KG"]/qCarga');
            if (!empty($infQ)) {
                $values['weight'] = (float)(string)$infQ[0];
            } else {
                // Fallback: try to get first qCarga
                $qCarga = $xml->xpath('//cte:infQ/cte:qCarga | //infQ/qCarga');
                if (!empty($qCarga)) {
                    $values['weight'] = (float)(string)$qCarga[0];
                }
            }
        } catch (\Exception $e) {
            // XPath failed, will use fallback
        }
        
        // Fallback for weight
        if ($values['weight'] === null) {
            $weightPaths = [
                'CTe->infCte->infCTeNorm->infCarga->infQ->qCarga',
                'infCte->infCTeNorm->infCarga->infQ->qCarga',
            ];

            foreach ($weightPaths as $path) {
                $weight = $this->getXmlValue($xml, $path);
                if ($weight) {
                    $values['weight'] = (float) $weight;
                    break;
                }
            }
        }
        
        // Extract quantity (volumes)
        try {
            $infQVolumes = $xml->xpath('//cte:infQ[cte:tpMed="VOLUMES"]/cte:qCarga | //infQ[tpMed="VOLUMES"]/qCarga');
            if (!empty($infQVolumes)) {
                $values['quantity'] = (int)(string)$infQVolumes[0];
            } else {
                // Fallback: try to get quantity from infQ where tpMed is not KG
                $infQOther = $xml->xpath('//cte:infQ[cte:tpMed!="KG"]/cte:qCarga | //infQ[tpMed!="KG"]/qCarga');
                if (!empty($infQOther)) {
                    $values['quantity'] = (int)(string)$infQOther[0];
                }
            }
        } catch (\Exception $e) {
            // XPath failed, will use default
        }

        return $values;
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

