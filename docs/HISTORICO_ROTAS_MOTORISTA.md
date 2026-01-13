# Sistema de Histórico de Rotas do Motorista

## 📋 Visão Geral

Foi implementado um sistema criativo e completo para armazenar e visualizar o histórico de rotas que o motorista fez ou tem que fazer. O sistema captura automaticamente snapshots detalhados de cada rota completada, incluindo métricas de performance, conquistas e estatísticas.

## 🎯 Funcionalidades Implementadas

### 1. **Tabela de Histórico (`driver_route_history`)**

Criada uma tabela abrangente que armazena:
- **Informações básicas**: Nome, descrição, datas, status
- **Estatísticas de performance**: 
  - Distância planejada vs real
  - Tempo planejado vs real
  - Velocidade média
  - Eficiência de combustível
  - Score de eficiência (0-100)
- **Métricas de entrega**: Total de entregas, entregas bem-sucedidas, coletas, exceções
- **Análise de desvios**: Total de desvios da rota planejada
- **Dados financeiros**: Receita, diárias, despesas, lucro líquido
- **Snapshots de caminhos**: Caminho planejado e caminho real percorrido (JSON)
- **Conquistas/Badges**: Sistema de badges para rotas excepcionais

### 2. **Model `DriverRouteHistory`**

Model completo com:
- Relacionamentos com Driver, Route, Vehicle, Tenant
- Métodos auxiliares para formatação (distância, duração)
- Cálculo de taxa de sucesso
- Sistema de badges de eficiência
- Métodos para verificar se rota foi no prazo e eficiente
- Scopes úteis (byDriver, completed, recent, inPeriod)

### 3. **Service `RouteHistoryService`**

Service que gerencia:
- **Criação automática de snapshots** quando rota é completada
- **Cálculo de distância real** usando rastreamento de localização (Haversine)
- **Cálculo de score de eficiência** baseado em desvios de tempo e distância
- **Análise de desvios** comparando caminho planejado vs real
- **Sistema de conquistas** automático:
  - ✅ No Prazo (on_time)
  - ✅ Rota Perfeita (perfect_route)
  - ✅ Alta Eficiência (high_efficiency)
  - ✅ Muitas Entregas (many_deliveries)
- **Estatísticas agregadas** do motorista

### 4. **Observer Automático**

O `RouteObserver` foi atualizado para:
- **Criar snapshot automaticamente** quando uma rota muda para status "completed"
- Integração transparente com o sistema existente

### 5. **Dashboard do Motorista**

Interface visual criativa com:

#### **Seção de Estatísticas Gerais**
- Total de rotas completadas
- Distância total percorrida
- Eficiência média

#### **Próximas Rotas**
- Cards visuais mostrando rotas agendadas
- Informações de data, horário, número de entregas
- Status visual com badges

#### **Timeline de Rotas Concluídas**
- **Design de timeline vertical** com marcadores coloridos
- **Cards de rota** com:
  - Badge de eficiência (cores: verde/azul/amarelo/vermelho)
  - Estatísticas em grid (distância, duração, entregas, velocidade)
  - Sistema de conquistas com badges visuais
  - Informação de lucro quando aplicável
- **Carregamento progressivo** (pagination) com botão "Carregar Mais"
- **Responsivo** para mobile

### 6. **Endpoints API**

Novos endpoints no `DriverDashboardController`:
- `GET /driver/route-history` - Lista histórico com paginação
- `GET /driver/statistics` - Estatísticas agregadas do motorista

## 🎨 Design e UX

### Características Visuais:
- **Timeline vertical** com linha conectando todas as rotas
- **Marcadores coloridos** baseados na eficiência da rota
- **Cards interativos** com hover effects
- **Badges de conquistas** com ícones e cores distintas
- **Cores semânticas**:
  - 🟢 Verde: Alta eficiência (≥90)
  - 🔵 Azul: Boa eficiência (75-89)
  - 🟡 Amarelo: Eficiência média (60-74)
  - 🔴 Vermelho: Baixa eficiência (<60)

### Responsividade:
- Layout adaptativo para mobile
- Grid de estatísticas que se ajusta
- Timeline otimizada para telas pequenas

## 📊 Métricas Capturadas

Para cada rota completada, o sistema armazena:

1. **Performance Operacional**:
   - Distância planejada vs real
   - Tempo planejado vs real
   - Velocidade média
   - Número de paradas
   - Tempo parado

2. **Eficiência**:
   - Score de eficiência (0-100)
   - Total de desvios da rota
   - Contagem de desvios

3. **Entregas**:
   - Total de entregas
   - Entregas bem-sucedidas
   - Coletas realizadas
   - Exceções

4. **Financeiro**:
   - Receita total
   - Diárias do motorista
   - Despesas totais
   - Lucro líquido

5. **Geográfico**:
   - Coordenadas de início e fim
   - Snapshot do caminho planejado (JSON)
   - Snapshot do caminho real (JSON)

## 🏆 Sistema de Conquistas

Badges automáticos concedidos quando:
- **No Prazo**: Rota completada dentro de 110% do tempo planejado
- **Rota Perfeita**: 100% das entregas bem-sucedidas
- **Alta Eficiência**: Distância real ≤ 110% da planejada
- **Muitas Entregas**: 10+ entregas em uma rota

## 🔄 Fluxo de Funcionamento

1. **Motorista completa uma rota** → Status muda para "completed"
2. **RouteObserver detecta** → Chama `RouteHistoryService::createRouteSnapshot()`
3. **Service calcula todas as métricas**:
   - Analisa LocationTracking para distância real
   - Compara com dados planejados
   - Calcula eficiência e desvios
   - Determina conquistas
4. **Snapshot é salvo** na tabela `driver_route_history`
5. **Dashboard exibe** automaticamente na timeline

## 📁 Arquivos Criados/Modificados

### Novos Arquivos:
- `database/migrations/2025_01_15_000000_create_driver_route_history_table.php`
- `app/Models/DriverRouteHistory.php`
- `app/Services/RouteHistoryService.php`
- `docs/HISTORICO_ROTAS_MOTORISTA.md`

### Arquivos Modificados:
- `app/Observers/RouteObserver.php` - Adicionado criação automática de snapshots
- `app/Http/Controllers/DriverDashboardController.php` - Adicionados métodos de histórico
- `app/Models/Driver.php` - Adicionados relacionamentos
- `resources/views/driver/dashboard.blade.php` - Adicionada seção de histórico
- `routes/web.php` - Adicionadas rotas de histórico

## 🚀 Como Usar

### Para Motoristas:
1. Acesse o dashboard do motorista (`/driver/dashboard`)
2. Role até a seção "Histórico de Rotas"
3. Veja suas estatísticas gerais no topo
4. Visualize próximas rotas agendadas
5. Explore a timeline de rotas concluídas
6. Clique em "Carregar Mais" para ver rotas antigas

### Para Desenvolvedores:
```php
// Criar snapshot manualmente (se necessário)
$routeHistoryService = app(RouteHistoryService::class);
$snapshot = $routeHistoryService->createRouteSnapshot($route);

// Obter estatísticas do motorista
$stats = $routeHistoryService->getDriverStatistics($driverId, $startDate, $endDate);

// Acessar histórico via relacionamento
$driver->routeHistory; // Todas as rotas
$driver->completedRouteHistory; // Apenas completadas
```

## 🎯 Benefícios

1. **Visibilidade**: Motorista vê todo seu histórico de forma visual e organizada
2. **Motivação**: Sistema de conquistas incentiva melhor performance
3. **Análise**: Métricas detalhadas permitem identificar pontos de melhoria
4. **Transparência**: Dados financeiros e operacionais sempre disponíveis
5. **Histórico Completo**: Snapshots preservam estado exato da rota no momento da conclusão

## 🔮 Melhorias Futuras Possíveis

- Gráficos de tendência de performance ao longo do tempo
- Comparação entre rotas similares
- Exportação de relatórios em PDF
- Filtros avançados (por período, tipo de rota, eficiência)
- Ranking de motoristas (se multi-motorista)
- Notificações de novas conquistas
- Compartilhamento de conquistas
