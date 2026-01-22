# Implementação: Sistema de Propostas e Solicitação de Coleta

## Resumo
Implementado sistema completo de propostas com solicitação de coleta, cargas disponíveis para rotas e suporte a clientes globais (multi-tenant).

## Funcionalidades Implementadas

### 1. Criação de Propostas no Sistema
- **Controller**: `ProposalController@store`
- **Funcionalidade**: Propostas são criadas pelo admin ou vendedor através do sistema Laravel

### 2. Campos Adicionados na Tabela `proposals`
- `collection_requested` (boolean) - Indica se coleta foi solicitada
- `collection_requested_at` (timestamp) - Data/hora da solicitação
- `origin_address`, `origin_city`, `origin_state`, `origin_zip_code` - Endereço de origem
- `origin_latitude`, `origin_longitude` - Coordenadas de origem
- `destination_address`, `destination_city`, `destination_state`, `destination_zip_code` - Endereço de destino
- `destination_latitude`, `destination_longitude` - Coordenadas de destino
- `client_name`, `client_whatsapp`, `client_email` - Dados do cliente do formulário
- `destination_name` - Nome do destino (ex: BELO HORIZONTE - MG)

### 3. Tabela `available_cargo`
- Armazena cargas disponíveis para criação de rotas
- Status: `available`, `assigned`, `collected`, `cancelled`
- Relacionamento com `proposals` e `routes`

### 4. Botão "Solicitar Coleta"
- **Localização**: 
  - Admin: `resources/views/proposals/show.blade.php`
  - Cliente: `resources/views/client/proposal-details.blade.php`
- **Condições**: Apenas propostas aceitas podem ter coleta solicitada
- **Ação**: Cria registro em `available_cargo` com status `available`

### 5. Integração com Criação de Rotas
- **Arquivo**: `app/Http/Controllers/RouteController.php`
- **Funcionalidade**: Ao criar rota, pode selecionar cargas disponíveis de propostas
- **Processo**: 
  1. Seleciona cargas disponíveis
  2. Cria `Shipment` a partir da `Proposal`
  3. Atualiza status da carga para `assigned`
  4. Associa à rota

### 6. Clientes Globais (Multi-Tenant)
- **Sistema**: Cliente pode ter propostas de múltiplos tenants
- **Tabela**: `client_users` - relaciona clientes com tenants e usuários
- **Dashboard do Cliente**: 
  - Mostra propostas de todos os tenants
  - Filtro por transportadora (tenant)
  - Estatísticas consolidadas

### 7. Visualizações Atualizadas
- **Admin**: Lista de propostas mostra status de coleta
- **Cliente**: Dashboard mostra propostas de todas as transportadoras
- **Vendedor**: Pode ver e gerenciar propostas normalmente

## Arquivos Criados/Modificados

### Migrations
- `database/migrations/2025_01_22_000000_add_collection_fields_to_proposals_table.php`
- `database/migrations/2025_01_22_000001_create_available_cargo_table.php`

### Models
- `app/Models/AvailableCargo.php` (novo)
- `app/Models/Proposal.php` (atualizado)

### Controllers
- `app/Http/Controllers/ProposalController.php` (atualizado - método `requestCollection`)
- `app/Http/Controllers/RouteController.php` (atualizado - processamento de cargas disponíveis)
- `app/Http/Controllers/ClientDashboardController.php` (atualizado - multi-tenant)

### Views
- `resources/views/proposals/show.blade.php` (botão solicitar coleta)
- `resources/views/proposals/index.blade.php` (coluna coleta)
- `resources/views/client/proposal-details.blade.php` (botão solicitar coleta)
- `resources/views/client/proposals.blade.php` (filtro por tenant)
- `resources/views/routes/create.blade.php` (seção cargas disponíveis)

### Rotas
- `routes/web.php` (rota solicitar coleta)

## Fluxo Completo

1. **Admin ou Vendedor cria proposta** no sistema Laravel
2. **Proposta aparece**:
   - No admin (todos os tenants)
   - Para o vendedor responsável
   - Para o cliente (se tiver login)
5. **Cliente/Admin aceita proposta**
6. **Botão "Solicitar Coleta" aparece**
7. **Ao solicitar coleta**:
   - Cria registro em `available_cargo`
   - Status: `available`
8. **Admin cria rota**:
   - Seleciona cargas disponíveis
   - Sistema cria `Shipment` automaticamente
   - Atualiza status para `assigned`

## Próximos Passos (Opcional)

1. Adicionar geocodificação automática de endereços ao criar proposta
2. Notificações quando coleta é solicitada
3. Dashboard de cargas disponíveis separado
4. Histórico de coletas
