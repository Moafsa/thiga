# Otimização Sequencial de Rotas

## ✅ Correção Implementada

### Problema Identificado:

O sistema estava usando **remetentes (pickup addresses)** como waypoints, quando deveria usar **destinatários (delivery addresses)**. Além disso, o Google Maps `optimizeWaypoints` otimiza todos os waypoints de uma vez a partir da origem, o que não é o comportamento desejado.

### Solução Implementada:

**Otimização Sequencial (Nearest Neighbor):**
- Cada destino se torna a origem para o próximo destino mais próximo
- Não otimiza todos de uma vez, mas sequencialmente

### Como Funciona:

1. **Ponto de Partida:** Depósito/Filial (origem fixa)
2. **Primeiro Destino:** Destinatário mais próximo do depósito
3. **Segundo Destino:** Destinatário mais próximo do primeiro destino
4. **Terceiro Destino:** Destinatário mais próximo do segundo destino
5. E assim por diante...

### Exemplo:

```
Depósito (Origem)
    ↓ (encontra mais próximo)
Destinatário A
    ↓ (encontra mais próximo de A)
Destinatário C
    ↓ (encontra mais próximo de C)
Destinatário B
    ↓ (último)
Destinatário D (Final)
```

**Antes (errado):**
- Usava pickup addresses (remetentes)
- Otimizava todos de uma vez do depósito

**Depois (correto):**
- Usa delivery addresses (destinatários)
- Otimiza sequencialmente (cada destino vira origem)

## 🔧 Implementação Técnica

### Novo Serviço: `RouteOptimizationService`

**Método Principal:**
```php
optimizeSequentialRoute($originLat, $originLng, $destinations)
```

**Algoritmo:**
1. Começa na origem (depósito)
2. Encontra destinatário mais próximo
3. Remove da lista de disponíveis
4. Repete até não haver mais destinatários

**Cálculo de Distância:**
- Usa fórmula de Haversine
- Calcula distância em linha reta (km)
- Considera curvatura da Terra

### Mudanças no Código:

1. **RouteController:**
   - Usa `delivery_latitude` e `delivery_longitude` (destinatários)
   - Chama `RouteOptimizationService` para otimizar
   - Não usa mais `optimizeWaypoints` do Google Maps

2. **GoogleMapsService:**
   - Removido `optimize:true` dos waypoints
   - Usa ordem já otimizada sequencialmente

## 📊 Benefícios

1. **Rota Mais Eficiente:**
   - Minimiza distância total
   - Cada entrega otimizada a partir da anterior

2. **Lógica Correta:**
   - Usa destinatários (não remetentes)
   - Sequencial (não todos de uma vez)

3. **Redução de Custos:**
   - Menos quilometragem
   - Menos tempo de viagem
   - Menos combustível

## 🎯 Resultado

- ✅ Usa **destinatários** (delivery addresses)
- ✅ Otimização **sequencial** (cada destino vira origem)
- ✅ Não usa mais `optimizeWaypoints` do Google Maps
- ✅ Algoritmo próprio de Nearest Neighbor

## 📝 Logs

O sistema agora loga:
- Ordem original dos destinatários
- Ordem otimizada sequencialmente
- IDs dos shipments na ordem otimizada

## 🔍 Verificação

Para verificar se está funcionando:
1. Criar rota com múltiplos CT-e
2. Verificar logs: `optimized_order` mostra a sequência
3. Verificar no mapa: rota deve ir do depósito ao mais próximo, depois ao próximo mais próximo dele, etc.































