# Correção: Origem Deve Ser Depósito/Filial, NÃO Remetente

## ❌ Problema Identificado

O sistema estava usando **endereços de remetente (pickup addresses)** como waypoints na rota, quando deveria usar **APENAS destinatários (delivery addresses)**. O ponto de partida deve ser **SEMPRE o depósito/filial**, nunca o remetente.

## ✅ Correção Implementada

### Mudanças:

1. **Removido pickup addresses dos waypoints:**
   - Antes: Adicionava tanto pickup quanto delivery como waypoints
   - Depois: Adiciona APENAS delivery addresses como waypoints

2. **Origem sempre do depósito/filial:**
   - Origem: `route->start_latitude/longitude` (depósito/filial)
   - Waypoints: Apenas `delivery_latitude/longitude` (destinatários)
   - Nunca usa `pickup_latitude/longitude` como origem ou waypoint

3. **Validação melhorada:**
   - Logs claros indicando que origem é depósito/filial
   - Erro se não houver coordenadas de depósito

### Código Antes (ERRADO):
```php
// Adicionava pickup addresses como waypoints
if ($shipment->pickup_latitude && $shipment->pickup_longitude) {
    $waypoints[] = [
        'lat' => $shipment->pickup_latitude,  // ❌ ERRADO
        'lng' => $shipment->pickup_longitude,
    ];
}
```

### Código Depois (CORRETO):
```php
// APENAS delivery addresses como waypoints
// Origem é sempre depósito/filial (route->start_latitude)
if ($shipment->delivery_latitude && $shipment->delivery_longitude) {
    $waypoints[] = [
        'lat' => $shipment->delivery_latitude,  // ✅ CORRETO
        'lng' => $shipment->delivery_longitude,
    ];
}
```

## 📋 Fluxo Correto

1. **Origem:** Depósito/Filial (`route->start_latitude/longitude`)
2. **Waypoint 1:** Destinatário mais próximo do depósito
3. **Waypoint 2:** Destinatário mais próximo do Waypoint 1
4. **Waypoint 3:** Destinatário mais próximo do Waypoint 2
5. E assim por diante...

**NUNCA usa remetente (pickup) como origem ou waypoint!**

## ✅ Resultado

- ✅ Origem sempre é depósito/filial
- ✅ Waypoints são APENAS destinatários
- ✅ Remetentes não são usados na rota
- ✅ Otimização sequencial funciona corretamente
















