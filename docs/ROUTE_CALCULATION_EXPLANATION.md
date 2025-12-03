# Explicação Detalhada do Cálculo de Rotas

## 📍 Como Funciona Atualmente

### 1. **Ponto de Partida (Origem)**

O sistema determina o ponto de partida na seguinte ordem de prioridade:

```php
// 1. Coordenadas da rota (se já definidas)
$originLat = $route->start_latitude;
$originLng = $route->start_longitude;

// 2. Coordenadas do Pavilhão/Filial (se não tiver coordenadas na rota)
if (!$originLat || !$originLng) {
    if ($route->branch) {
        $originLat = $route->branch->latitude;
        $originLng = $route->branch->longitude;
    }
}

// 3. Localização atual do motorista (fallback)
if ((!$originLat || !$originLng) && $route->driver) {
    $originLat = $route->driver->current_latitude;
    $originLng = $route->driver->current_longitude;
}
```

**Por que precisa sair do pavilhão?**
- O pavilhão é o ponto de partida físico onde o veículo está estacionado
- Todas as entregas começam a partir deste ponto
- É necessário para calcular a distância total e o tempo de viagem corretamente

### 2. **Coleta de Waypoints (Endereços de Entrega)**

```php
// Busca todos os shipments da rota com coordenadas válidas
$shipments = $route->shipments()
    ->whereNotNull('delivery_latitude')
    ->whereNotNull('delivery_longitude')
    ->orderBy('id')  // ⚠️ ORDEM FIXA - NÃO OTIMIZADA
    ->get();

// Cria waypoints na ordem que vêm do banco
$waypoints = [];
foreach ($shipments as $shipment) {
    $waypoints[] = [
        'lat' => $shipment->delivery_latitude,
        'lng' => $shipment->delivery_longitude,
    ];
}
```

**⚠️ PROBLEMA IDENTIFICADO:**
- Os waypoints são criados na **ordem do banco de dados** (`orderBy('id')`)
- **NÃO há otimização da ordem** para minimizar distância/custo
- O Google Maps apenas conecta os pontos na ordem fornecida

### 3. **Destino Final**

```php
// Último shipment é usado como destino final
$lastShipment = $shipments->last();
$destinationLat = $lastShipment->delivery_latitude;
$destinationLng = $lastShipment->delivery_longitude;
```

### 4. **Cálculo de Múltiplas Rotas**

O sistema calcula **3 opções de rota** usando Google Maps Directions API:

#### **Opção 1: Rota Mais Rápida**
```php
$route1 = $this->getRouteWithOptions(
    $originLat, $originLng,
    $destinationLat, $destinationLng,
    $waypointsStr,
    [],  // Sem restrições
    $vehicle
);
```
- Busca o menor tempo de viagem
- Pode incluir pedágios
- Usa a ordem dos waypoints fornecida

#### **Opção 2: Rota Sem Pedágios**
```php
$route2 = $this->getRouteWithOptions(
    $originLat, $originLng,
    $destinationLat, $destinationLng,
    $waypointsStr,
    ['avoid' => 'tolls'],  // Evita pedágios
    $vehicle
);
```
- Evita pedágios completamente
- Pode ser mais longa em distância/tempo
- Economiza custos de pedágio

#### **Opção 3: Rota Alternativa**
```php
$route3 = $this->getRouteWithOptions(
    $originLat, $originLng,
    $destinationLat, $destinationLng,
    $waypointsStr,
    ['alternatives' => 'true'],  // Busca alternativas
    $vehicle
);
```
- Busca rotas alternativas do Google Maps
- Pode ter diferentes características

### 5. **Cálculo de Custos**

#### **Custo de Combustível:**
```php
// Estimativa fixa: R$ 0,50 por km
$estimatedCost = ($totalDistance / 1000) * 0.50;

// Consumo por tipo de veículo (armazenado em settings)
$fuelConsumptionPerKm = match($vehicle->vehicle_type) {
    'truck' => 0.35,  // 35L per 100km
    'van' => 0.12,    // 12L per 100km
    'car' => 0.10,    // 10L per 100km
    default => 0.20,
};
```

#### **Custo de Pedágios:**

**Como funciona:**

1. **Detecção de Pedágios:**
   ```php
   // Procura por palavras "pedágio" ou "toll" nas instruções da rota
   if (stripos($step['html_instructions'], 'pedágio') !== false) {
       $hasTolls = true;
   }
   ```

2. **Busca no Banco de Dados:**
   ```php
   // Tenta encontrar pedágio próximo (raio de 2km)
   $tollPlaza = $this->findNearestTollPlaza(
       $startLocation['lat'],
       $startLocation['lng']
   );
   ```

3. **Valores Reais vs Estimados:**

   **✅ VALORES REAIS (se encontrado no banco):**
   ```php
   if ($tollPlaza) {
       // Busca preço específico para o tipo de veículo
       $price = $tollPlaza->getPriceForVehicle(
           $vehicle->vehicle_type,
           $vehicle->axles
       );
   }
   ```
   - Busca na tabela `toll_plazas`
   - Preços específicos por tipo de veículo (carro, van, caminhão por eixos)
   - Valores reais se o pedágio estiver cadastrado

   **⚠️ VALORES ESTIMADOS (se não encontrado):**
   ```php
   else {
       // Estima valores padrão
       $price = match($vehicleType) {
           'car' => 5.00,
           'van' => 8.00,
           'truck' => match($axles) {
               2 => 12.00,
               3 => 18.00,
               4 => 25.00,
               5+ => 35.00,
           },
       };
   }
   ```

## ❌ Problemas Identificados

### 1. **Falta de Otimização da Ordem dos Waypoints**

**Problema:**
- Os waypoints são usados na ordem do banco de dados
- Não há algoritmo de otimização (TSP - Traveling Salesman Problem)
- Pode resultar em rotas muito longas e caras

**Exemplo:**
```
Pavilhão → Entrega A (10km) → Entrega B (50km) → Entrega C (5km)
```

**Seria melhor:**
```
Pavilhão → Entrega C (5km) → Entrega A (8km) → Entrega B (12km)
```

### 2. **Google Maps Não Otimiza Waypoints Automaticamente**

- O Google Maps Directions API **não otimiza** a ordem dos waypoints
- Ele apenas conecta os pontos na ordem fornecida
- Para otimização, é necessário usar `optimizeWaypoints: true` (mas isso não está sendo usado)

### 3. **Cálculo de Custo Simplificado**

- Combustível: R$ 0,50/km fixo (não considera preço real do combustível)
- Não considera custos variáveis (manutenção, desgaste, etc.)
- Não compara eficientemente as 3 opções para escolher a melhor

### 4. **Valores de Pedágios**

**✅ Funciona bem quando:**
- Pedágio está cadastrado no banco (`toll_plazas`)
- Coordenadas estão corretas
- Tipo de veículo está definido

**⚠️ Problemas:**
- Se pedágio não estiver no banco, usa valores estimados
- Depende da detecção por texto nas instruções (pode falhar)
- Raio de busca de 2km pode não encontrar pedágios próximos

## 🔧 O Que Precisa Ser Melhorado

### 1. **Implementar Otimização de Waypoints**

```php
// Usar algoritmo TSP ou Google Maps optimizeWaypoints
$params['waypoints'] = 'optimize:true|' . $waypointsStr;
```

Ou implementar algoritmo próprio:
- Calcular distâncias entre todos os pontos
- Encontrar ordem que minimize distância total
- Considerar custos de pedágio e combustível

### 2. **Melhorar Cálculo de Custos**

- Usar preço real do combustível (API ou configuração)
- Considerar custos variáveis
- Comparar todas as opções e escolher a melhor automaticamente

### 3. **Melhorar Busca de Pedágios**

- Aumentar raio de busca
- Usar API de pedágios (se disponível)
- Melhorar detecção nas instruções da rota

### 4. **Adicionar Otimização por Custo Total**

- Calcular: Distância + Pedágios + Combustível
- Escolher rota com menor custo total
- Considerar tempo também (custo do motorista)

## 📊 Resumo

| Aspecto | Status Atual | Ideal |
|---------|--------------|-------|
| Ponto de partida | ✅ Funciona (pavilhão) | ✅ OK |
| Ordem dos waypoints | ❌ Não otimizada | ⚠️ Precisa otimização |
| Cálculo de distância | ✅ Google Maps | ✅ OK |
| Cálculo de pedágios | ⚠️ Parcial (banco + estimativa) | ⚠️ Melhorar busca |
| Cálculo de combustível | ⚠️ Fixo R$ 0,50/km | ⚠️ Usar preço real |
| Otimização por custo | ❌ Não implementada | ⚠️ Necessária |

## 🎯 Conclusão

**Valores de Pedágios:**
- ✅ **SIM, está buscando valores reais** quando o pedágio está cadastrado no banco
- ⚠️ **Usa estimativas** quando não encontra no banco
- ⚠️ Depende da qualidade do cadastro de pedágios

**Otimização:**
- ❌ **NÃO está otimizando** a ordem dos endereços
- ⚠️ Está usando a ordem do banco de dados
- ⚠️ Precisa implementar algoritmo de otimização

**Recomendação:**
Implementar otimização de waypoints usando `optimizeWaypoints: true` do Google Maps ou algoritmo próprio de TSP para minimizar custos totais.




