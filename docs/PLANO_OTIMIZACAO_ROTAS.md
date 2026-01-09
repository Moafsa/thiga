# Plano de Implementação - Otimização Real de Rotas

## 🎯 Objetivos

1. **Otimização Real da Ordem dos Endereços**
   - Implementar algoritmo de otimização de waypoints
   - Minimizar distância total e custos

2. **Valores Reais de Pedágios**
   - Melhorar busca de pedágios na rota
   - Usar valores reais do banco de dados
   - Integrar com API de pedágios (futuro)

3. **Cálculo Real de Combustível**
   - Usar preço real do combustível por tipo
   - Considerar consumo específico de cada veículo
   - Calcular custo total preciso

4. **Comparação Automática de Rotas**
   - Calcular custo total de cada opção
   - Escolher automaticamente a melhor rota
   - Mostrar comparação detalhada

## 📋 Etapas de Implementação

### Fase 1: Otimização de Waypoints ✅
- [x] Implementar `optimizeWaypoints: true` no Google Maps
- [ ] Criar algoritmo alternativo caso Google Maps falhe
- [ ] Testar otimização com diferentes quantidades de endereços

### Fase 2: Melhorar Busca de Pedágios ✅
- [ ] Aumentar raio de busca de 2km para 5km
- [ ] Melhorar detecção de pedágios nas instruções
- [ ] Adicionar fallback para busca por coordenadas
- [ ] Criar job para atualizar pedágios periodicamente

### Fase 3: Cálculo Real de Combustível ✅
- [ ] Criar tabela de configuração de preços de combustível
- [ ] Adicionar consumo específico por veículo
- [ ] Implementar cálculo baseado em distância + consumo + preço
- [ ] Considerar tipo de combustível (diesel, gasolina, etc)

### Fase 4: Comparação e Seleção Automática ✅
- [ ] Criar função de cálculo de custo total
- [ ] Comparar todas as rotas calculadas
- [ ] Selecionar automaticamente a melhor
- [ ] Mostrar breakdown de custos

### Fase 5: Melhorias e Testes ✅
- [ ] Adicionar logs detalhados
- [ ] Criar testes unitários
- [ ] Documentar API
- [ ] Otimizar performance

## 🔧 Implementação Técnica

### 1. Otimização de Waypoints

**Google Maps API:**
```php
$params['waypoints'] = 'optimize:true|' . $waypointsStr;
```

**Algoritmo Alternativo (TSP simples):**
- Calcular distâncias entre todos os pontos
- Usar algoritmo Nearest Neighbor
- Considerar custos de pedágio na otimização

### 2. Busca de Pedágios

**Melhorias:**
- Aumentar raio de busca
- Buscar por múltiplos métodos
- Cache de resultados
- Atualização periódica

### 3. Cálculo de Combustível

**Estrutura:**
```php
$fuelCost = ($distance / 1000) * $vehicle->fuel_consumption_per_km * $fuelPrice;
```

**Configuração:**
- Preço por tipo de combustível
- Consumo por tipo de veículo
- Atualização de preços

### 4. Comparação de Rotas

**Métrica:**
```php
$totalCost = $fuelCost + $tollCost + ($timeCost * $driverHourlyRate);
```

**Seleção:**
- Menor custo total
- Considerar tempo também
- Mostrar todas as opções para escolha manual

## 📊 Estrutura de Dados

### Nova Tabela: fuel_prices
```sql
- id
- fuel_type (diesel, gasoline, ethanol)
- price_per_liter
- effective_date
- is_active
```

### Nova Tabela: vehicle_fuel_specs
```sql
- vehicle_id
- fuel_type
- consumption_per_km
- tank_capacity
```

### Atualizar: routes
```sql
- optimized_waypoints_order (JSON)
- total_fuel_cost
- total_toll_cost
- total_cost
- optimization_score
```

## 🚀 Ordem de Implementação

1. **Otimização de Waypoints** (Prioridade Alta)
2. **Cálculo Real de Combustível** (Prioridade Alta)
3. **Melhorar Busca de Pedágios** (Prioridade Média)
4. **Comparação Automática** (Prioridade Média)
5. **Melhorias e Testes** (Prioridade Baixa)

## ✅ Critérios de Sucesso

- [ ] Rotas otimizadas reduzem distância em pelo menos 10%
- [ ] Valores de pedágios são reais em 90%+ dos casos
- [ ] Cálculo de combustível usa preços reais
- [ ] Sistema seleciona automaticamente a melhor rota
- [ ] Performance aceitável (< 5 segundos para calcular)































