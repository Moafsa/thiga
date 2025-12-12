# Informações sobre Pedágios no Google Maps

## ❌ Google Maps Directions API NÃO Fornece Valores de Pedágios

### Situação Atual:

A **Google Maps Directions API** (que estamos usando) **NÃO fornece valores de pedágios diretamente**. Ela apenas:
- Menciona "pedágio" nas instruções de navegação
- Indica quando há pedágios na rota
- **NÃO fornece valores, localização exata ou detalhes**

### ✅ Soluções Disponíveis:

#### 1. **Google Maps Routes API (Nova API - Paga)**
- Fornece informações de pedágios via `tollPass`
- Requer migração da Directions API para Routes API
- Mais cara, mas fornece dados reais
- Disponível apenas em alguns países/regiões

#### 2. **APIs Especializadas de Terceiros:**

**a) Maplink Toll API:**
- Fornece valores reais de pedágios brasileiros
- Integração com base de dados ANTT
- Valores atualizados
- Requer contrato/API key

**b) AILOG Toll API:**
- Identifica pedágios em rotas
- Valores detalhados por praça
- Base de dados nacional
- Requer contrato/API key

#### 3. **Solução Atual (Implementada):**
- Busca pedágios no banco de dados próprio (`toll_plazas`)
- Usa valores cadastrados quando encontrado
- Estima valores quando não encontrado
- Raio de busca aumentado para melhor detecção

## 🔧 O Que Foi Implementado:

### Melhorias na Busca de Pedágios:

1. **Raio aumentado:** 2km → 5km
2. **Busca dupla:** Instruções + Waypoints
3. **Prevenção de duplicatas**
4. **Valores reais quando cadastrados**
5. **Estrutura preparada para API externa**

### Próximos Passos Recomendados:

1. **Opção 1: Cadastrar Pedágios Manualmente**
   - Importar base de pedágios brasileiros
   - Manter atualizada periodicamente
   - Usar valores reais do banco

2. **Opção 2: Integrar API Externa**
   - Contratar Maplink ou AILOG
   - Implementar integração
   - Usar valores em tempo real

3. **Opção 3: Migrar para Routes API**
   - Avaliar custos
   - Migrar código
   - Usar tollPass do Google

## 📊 Status Atual:

- ✅ Busca melhorada no banco próprio
- ✅ Valores reais quando cadastrados
- ⚠️ Estimativas quando não encontrado
- ⚠️ Depende de cadastro manual
- 🔄 Estrutura pronta para API externa

## 💡 Recomendação:

**Curto Prazo:**
- Usar busca atual melhorada
- Cadastrar pedágios principais manualmente
- Usar estimativas para os demais

**Médio Prazo:**
- Avaliar integração com Maplink/AILOG
- Ou migrar para Routes API do Google
- Obter valores reais automaticamente
















