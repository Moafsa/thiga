# Plano de Desenvolvimento Distribuído - TMS SaaS

**Data de Criação**: 04/11/2025  
**Última Atualização**: 11/11/2025 (Adicionado: Google Maps API, Geolocalização na Entrega, Timeline e Notificações WhatsApp)  
**Objetivo**: Definir prioridades e distribuir o desenvolvimento das funcionalidades core do sistema TMS

## 🎯 RESUMO EXECUTIVO

Este plano define as **prioridades máximas** para o desenvolvimento do sistema TMS:

### Prioridades Máximas (Core Operacional)
1. **ROTAS** - Sistema completo com geração automática, vinculação de veículo, manifesto, manutenção, despesas e métricas
2. **MANIFESTO (MDF-e)** - Emissão e gestão completa de manifestos eletrônicos vinculados às rotas
3. **CTe** - Emissão e gestão completa de Conhecimentos de Transporte Eletrônico

### Prioridades Altas (Estrutura Base)
4. **Cadastro de Motorista** - CRUD funcional com relacionamento many-to-many com veículos
5. **Cadastro de Veículo** - CRUD funcional (um veículo pode ser dirigido por múltiplos motoristas)
6. **Sistema de Manutenção** - Gestão de manutenções vinculadas às rotas
7. **Sistema de Despesas por Rota** - Gestão de despesas específicas por rota
8. **Cálculo de Médias** - Sistema automático de métricas e médias por rota
9. **Google Maps API e Geolocalização** - Foto de entrega com geolocalização obrigatória como prova
10. **Timeline/Histórico de Entrega** - Timeline completa para cliente acompanhar em tempo real
11. **Integração WuzAPI - Notificações** - Notificar cliente via WhatsApp a cada mudança de status com confirmação

### Princípios Fundamentais
- **Um veículo pode ser dirigido por mais de um motorista** (relacionamento many-to-many)
- **A cada rota, deve ser vinculado**: veículo, manifesto (MDF-e), manutenção, despesa, média ao motorista
- **O sistema deve gerar automaticamente a rota do motorista**
- **Todas as métricas e cálculos devem ser automáticos e vinculados às rotas**
- **Foto de entrega DEVE incluir geolocalização** como garantia e prova de entrega
- **Cliente deve ser notificado via WhatsApp** a cada mudança de status
- **Cliente deve confirmar recebimento** via WhatsApp quando produto for entregue
- **Timeline completa** deve estar disponível para o cliente acompanhar todo o processo

---

## 📊 Análise do Sistema Atual

### ✅ O que JÁ FOI IMPLEMENTADO

#### Fase 0: Fundação e Estrutura
- ✅ **Multi-Tenant**: Implementado com `spatie/laravel-multitenancy`
  - Models: `Tenant`, `Plan`, `Subscription`, `Payment`
  - Migrations completas
  - Relacionamentos configurados

- ✅ **Billing/Assinaturas**: Sistema básico implementado
  - `AsaasService` criado (métodos básicos)
  - `SubscriptionController` implementado
  - Webhooks de Asaas configurados
  - Views de planos e assinaturas

- ✅ **Permissões**: Sistema de roles implementado
  - `spatie/laravel-permission` instalado
  - Roles: Admin Tenant, Financeiro, Operacional, Vendedor
  - Seeder de roles criado

- ✅ **Autenticação**: Sistema completo
  - Login/Register implementado
  - Middleware de autenticação
  - Proteção de rotas

#### Módulo 1: Configurações da Transportadora
- ✅ **Empresas e Filiais**: CRUD completo
  - Models: `Company`, `Branch`
  - `CompanyController` implementado
  - Migrations e relacionamentos
  - Views básicas criadas

#### Módulo 2: CRM
- ✅ **Clientes**: Model e migration criados
  - `Client`, `ClientAddress` models
  - Relacionamentos configurados

- ✅ **Vendedores**: CRUD completo
  - `Salesperson` model e controller
  - Sistema de desconto máximo por vendedor
  - Views criadas

- ✅ **Propostas Comerciais**: CRUD completo
  - `Proposal` model e controller
  - Cálculo de desconto com validação
  - Views criadas

#### Módulo 3: Operacional - Coletas e Entregas (Parcial)
- ✅ **Models e Migrations**: Estrutura completa
  - `Shipment` model e migration
  - `Route` model e migration
  - `Driver` model e migration
  - `DeliveryProof` model e migration
  - `LocationTracking` model e migration
  - `FiscalDocument` model e migration

- ✅ **Controllers**: PARCIALMENTE IMPLEMENTADOS
  - ✅ `ShipmentController` existe (CRUD básico)
  - ✅ `RouteController` existe (CRUD básico)
  - ✅ `DriverController` existe (CRUD básico)
  - ⚠️ **FALTA**: Métodos avançados (geração automática de rota, vinculação de veículo, etc.)
  
- ⚠️ **Views**: FALTANDO
  - Não há views completas para Shipments
  - Não há views completas para Rotas
  - Não há views completas para Drivers
  - Não há wizard de 3 passos conforme especificado
  - Não há cálculo de frete integrado nas views

#### Integração WhatsApp
- ✅ **WuzAPI**: Serviço implementado
  - `WuzApiService` completo
  - `WhatsAppAiService` criado (integração OpenAI)
  - Webhooks configurados
  - API de rastreamento pública (`TrackingController`)

#### Integração Fiscal
- ✅ **MittService**: IMPLEMENTADO PARCIALMENTE
  - ✅ `MittService.php` criado com métodos `issueCte()`, `issueMdfe()`, `cancelCte()`
  - ✅ Métodos `getCteStatus()` e `getMdfeStatus()` para consultar status
  - ✅ Integração com API Mitt funcionando
  - ✅ Tratamento de erros e retry logic implementado
  - ⚠️ **FALTA**: Métodos para buscar documentos completos (XML, PDF) do Mitt
  - ⚠️ **FALTA**: Métodos para listar CT-es e MDF-es do Mitt por período
  - ⚠️ **FALTA**: Sincronização de documentos do Mitt com banco local
  
- ✅ **FiscalService**: IMPLEMENTADO PARCIALMENTE
  - ✅ `FiscalService.php` criado como orquestrador fiscal
  - ✅ Métodos `requestCteIssuance()` e `requestMdfeIssuance()` implementados
  - ✅ Pré-validação de dados implementada
  - ✅ Método `updateDocumentStatusFromWebhook()` existe mas precisa melhorias
  - ⚠️ **FALTA**: Métodos de sincronização (`syncCteFromMitt()`, `syncMdfeFromMitt()`)
  - ⚠️ **FALTA**: Buscar XML e PDF do Mitt quando documento for autorizado
  
- ✅ **Eventos e Listeners**: IMPLEMENTADOS COMPLETOS
  - Event `CteIssuanceRequested` criado
  - Event `MdfeIssuanceRequested` criado
  - Listener `ProcessCteIssuance` implementado
  - Listener `ProcessMdfeIssuance` implementado
  
- ✅ **Jobs**: IMPLEMENTADOS COMPLETOS
  - `SendCteToMittJob` implementado com preparação de dados
  - `SendMdfeToMittJob` implementado com preparação de dados
  - Tratamento de falhas implementado
  
- ✅ **FiscalController**: IMPLEMENTADO
  - Métodos `issueCte()` e `issueMdfe()` implementados
  - Validação de acesso implementada
  
- ⚠️ **FALTA**: Interface visual nas views (botões de emissão)
- ⚠️ **FALTA**: Webhook handler completo para atualizações do Mitt
- ⚠️ **FALTA**: Métodos auxiliares `buildCteDataFromShipment()` e `buildMdfeDataFromRoute()` no FiscalService

---

### ❌ O que FALTA SER IMPLEMENTADO

#### Prioridade ALTA (Core do Negócio)

1. **Módulo Operacional - Shipments (Completo)**
   - Controller `ShipmentController` com CRUD completo
   - Componente Livewire `CreateShipment` com wizard de 3 passos:
     - Passo 1: Selecionar Remetente e preencher Destinatário
     - Passo 2: Dados da mercadoria (peso, volume, valor, NFe)
     - Passo 3: Calcular frete usando tabelas de frete
   - Views completas (listagem, criar, editar, visualizar)
   - Tabelas de frete (model, migration, CRUD)
   - Integração com cálculo automático de frete

2. **Módulo Fiscal Completo (Integração Mitt)**
   - `MittService.php` com métodos:
     - `issueCte(Shipment $shipment)`
     - `issueMdfe(array $shipments)`
     - `cancelCte($cteId)`
     - `getSpedData(Carbon $startDate, Carbon $endDate)`
   - Eventos Laravel: `CteIssuanceRequested`, `MdfeIssuanceRequested`
   - Listeners e Jobs para processamento assíncrono
   - Interface para emissão de CT-e na página de detalhes do Shipment
   - Interface para emissão de MDF-e na página de Route
   - Status tracking de documentos fiscais
   - Webhook handler completo para atualizações do Mitt

3. **Módulo Financeiro - Faturamento**
   - Models: `Invoice`, `InvoiceItem`
   - Componente Livewire `InvoicingTool`
   - Página `/invoicing` para gerar faturas
   - Agrupamento de Shipments por cliente/período
   - Status de fatura (Aberta, Paga, Vencida)
   - Views e rotas completas

4. **Módulo Financeiro - Contas a Receber**
   - Página `/accounts/receivable`
   - Listagem de faturas com filtros
   - Registro de pagamentos (baixa manual)
   - Relatório de faturas vencidas
   - Views e rotas

#### Prioridade MÉDIA

5. **Módulo Financeiro - Contas a Pagar**
   - Model `Expense` (Despesa)
   - CRUD completo de despesas
   - Categorias de despesas (Combustível, Salários, Manutenção, etc.)
   - Status (A Pagar, Pago)
   - Página `/accounts/payable`
   - Views e rotas

6. **Módulo Financeiro - Fluxo de Caixa**
   - Página `/cash-flow`
   - Extrato consolidado (recebimentos + pagamentos)
   - Filtros por data
   - Saldo consolidado
   - Views e rotas

7. **Módulo Rotas - CRUD e Interface**
   - `RouteController` completo
   - Views de rotas (listar, criar, editar, visualizar)
   - Associação de Shipments a rotas
   - Associação de Driver a rota
   - Integração com MDF-e

8. **PWA - App do Motorista**
   - Área separada `/driver/dashboard`
   - Autenticação específica para motoristas
   - Listagem de entregas da rota atual
   - Atualização de status de entrega
   - Captura de foto de comprovante
   - Interface PWA otimizada para mobile

#### Prioridade BAIXA (Melhorias)

9. **Super Admin Panel**
   - Área administrativa para gestão da plataforma
   - Gerenciamento de tenants
   - Métricas e analytics
   - Configurações globais

10. **Melhorias de Views**
    - Padronização visual com identidade Thiga
    - Componentes Livewire reutilizáveis
    - Dashboard com métricas

---

## 🎯 Plano de Desenvolvimento Distribuído

### 🔴 REORGANIZAÇÃO DE PRIORIDADES (Atualizado em 11/11/2025)

**IMPORTANTE**: As prioridades foram reorganizadas conforme solicitado pelo usuário:

#### **PRIORIDADE MÁXIMA - Core Operacional**

1. **ROTAS** - Sistema completo de gerenciamento de rotas
   - CRUD completo de rotas
   - Geração automática de rota do motorista
   - Associação de veículo, manifesto (MDF-e), manutenção, despesas e métricas à rota
   - Cálculo de médias por rota (consumo, tempo, distância)

2. **MANIFESTO (MDF-e)** - Emissão e gestão de manifestos eletrônicos
   - Integração completa com Mitt para emissão de MDF-e
   - Vinculação de MDF-e às rotas
   - Status tracking e webhooks

3. **CTe** - Emissão e gestão de Conhecimentos de Transporte Eletrônico
   - Integração completa com Mitt para emissão de CT-e
   - Vinculação de CT-e aos shipments
   - Status tracking e webhooks

#### **PRIORIDADE ALTA - Estrutura Base**

4. **Cadastro de Motorista** - CRUD funcional completo
   - Cadastro completo com validações
   - Relacionamento com veículos (many-to-many)
   - Histórico de rotas e métricas

5. **Cadastro de Veículo** - CRUD funcional completo
   - Modelo Vehicle separado (um veículo pode ser dirigido por múltiplos motoristas)
   - Relacionamento many-to-many com motoristas
   - Histórico de manutenções e rotas

6. **Sistema de Manutenção** - Gestão de manutenções de veículos
   - Model VehicleMaintenance
   - Vinculação de manutenção à rota
   - Alertas de manutenção preventiva

7. **Sistema de Despesas por Rota** - Gestão de despesas vinculadas às rotas
   - Despesas específicas por rota (combustível, pedágio, alimentação, etc.)
   - Cálculo automático de custos por rota
   - Relatórios de despesas

8. **Cálculo de Médias** - Sistema de métricas e médias
   - Model RouteMetrics para armazenar métricas por rota
   - Cálculo de médias: consumo de combustível, tempo médio, distância média
   - Relatórios e dashboards com métricas

**OBSERVAÇÕES IMPORTANTES**:
- Um veículo pode ser dirigido por mais de um motorista (relacionamento many-to-many)
- A cada rota, deve ser vinculado: veículo, manifesto (MDF-e), manutenção, despesa, média ao motorista
- O sistema deve gerar automaticamente a rota do motorista
- Todas as métricas e cálculos devem ser automáticos e vinculados às rotas

---

### ✅ CONCLUÍDO (Agente 4 - EU):

1. ✅ **Migration e Model FreightTable**: Tabela de frete por tenant criada
2. ✅ **FreightCalculationService**: Serviço completo com lógica do pracas.html
3. ✅ **McpFreightController**: API MCP para integração com n8n
4. ✅ **FreightTableController**: CRUD de tabelas de frete por tenant
5. ✅ **SalespersonDashboardController**: Dashboard do vendedor com calculadora
6. ✅ **Rotas configuradas**: API MCP e rotas web

**PRÓXIMOS PASSOS**:
- Criar views para CRUD de tabelas de frete
- Criar view do dashboard do vendedor com calculadora integrada
- Implementar autenticação por token para API MCP (Sanctum ou similar)
- Criar seeder com dados de exemplo da tabela do pracas.html

---

## 👥 DIVISÃO DO TRABALHO - 2 AGENTES

### 📊 Estratégia de Divisão

O trabalho foi dividido entre **2 agentes** trabalhando simultaneamente, considerando:
- **Dependências entre funcionalidades**
- **Equilíbrio de carga de trabalho**
- **Possibilidade de trabalho paralelo sem bloqueios**

### 🔵 AGENTE 1: "Operational & Infrastructure Expert"
**Foco**: Infraestrutura operacional, cadastros, rotas, métricas e geolocalização

**Prioridades Atribuídas:**
1. ✅ **PRIORIDADE MÁXIMA 1**: ROTAS - Sistema completo
2. ✅ **PRIORIDADE ALTA 4**: Cadastro de Motorista
3. ✅ **PRIORIDADE ALTA 5**: Cadastro de Veículo
4. ✅ **PRIORIDADE ALTA 6**: Sistema de Manutenção
5. ✅ **PRIORIDADE ALTA 7**: Sistema de Despesas por Rota
6. ✅ **PRIORIDADE ALTA 8**: Cálculo de Médias e Métricas
7. ✅ **PRIORIDADE ALTA 9**: Google Maps API e Geolocalização na Entrega

**Tempo Estimado Total**: 18-22 dias

**Ordem de Execução Sugerida:**
1. **Semana 1**: Prioridade 4 (Motorista) + Prioridade 5 (Veículo) - Base para tudo
2. **Semana 2**: Prioridade 1 (Rotas) - Sistema completo
3. **Semana 3**: Prioridade 6 (Manutenção) + Prioridade 7 (Despesas) + Prioridade 8 (Métricas)
4. **Semana 4**: Prioridade 9 (Google Maps e Geolocalização)

---

### 🟢 AGENTE 2: "Fiscal & Communication Expert"
**Foco**: Documentos fiscais, timeline e comunicação com cliente

**Prioridades Atribuídas:**
1. ✅ **PRIORIDADE MÁXIMA 2**: MANIFESTO (MDF-e) - Completar integração e interface
2. ✅ **PRIORIDADE MÁXIMA 3**: CTe - Completar integração e interface
3. ✅ **PRIORIDADE ALTA 10**: Timeline/Histórico de Entrega
4. ✅ **PRIORIDADE ALTA 11**: Integração WuzAPI - Notificações

**⚠️ OBSERVAÇÃO IMPORTANTE**: 
- **MittService** (emissão), **FiscalService** (orquestração básica), **Eventos**, **Listeners** e **Jobs** JÁ ESTÃO IMPLEMENTADOS!
- **FALTA IMPLEMENTAR**: Métodos de busca de documentos do Mitt (XML, PDF)
- Agente 2 deve focar em:
  - **Buscar documentos do Mitt**: Implementar métodos `getCte()`, `getMdfe()`, `getCtePdf()`, `getMdfePdf()`, etc.
  - **Sincronização**: Métodos `syncCteFromMitt()`, `syncMdfeFromMitt()` no FiscalService
  - **Webhook melhorado**: Buscar XML/PDF quando documento for autorizado via webhook
  - **Interfaces visuais**: Botões de emissão e busca nas views
  - **Timeline completa**: Sistema de eventos e histórico
  - **Notificações WhatsApp**: Integração completa com WuzAPI

**Tempo Estimado Total**: 13-16 dias (ajustado devido à necessidade de buscar documentos do Mitt)

**Ordem de Execução Sugerida:**
1. **Semana 1**: 
   - Prioridade 3 (CTe) - Métodos de busca no MittService (2 dias)
   - Prioridade 3 (CTe) - Sincronização e interface (2 dias)
   - Prioridade 10 (Timeline) - Estrutura (1 dia)
2. **Semana 2**: 
   - Prioridade 2 (MDF-e) - Métodos de busca no MittService (2 dias)
   - Prioridade 2 (MDF-e) - Sincronização e interface (2 dias)
   - Prioridade 10 (Timeline) - Views (1 dia)
3. **Semana 3**: Prioridade 11 (Notificações WhatsApp) - Integração completa (5 dias)

---

### 🔗 Dependências entre Agentes

**AGENTE 1 → AGENTE 2:**
- ✅ Agente 2 precisa que Agente 1 complete **Prioridade 1 (Rotas)** para vincular MDF-e
- ✅ Agente 2 pode começar **Prioridade 3 (CTe)** independentemente
- ✅ Agente 2 precisa de **Prioridade 9 (Geolocalização)** para Timeline completa

**AGENTE 2 → AGENTE 1:**
- ✅ Agente 1 precisa que Agente 2 complete **Prioridade 3 (CTe)** para vincular CT-e às rotas
- ✅ Agente 1 pode trabalhar nas outras prioridades independentemente

**Estratégia de Coordenação:**
1. **Semana 1**: Ambos trabalham independentemente
   - Agente 1: Motorista + Veículo
   - Agente 2: CTe
2. **Semana 2**: Coordenação necessária
   - Agente 1: Rotas (precisa de CTe do Agente 2)
   - Agente 2: MDF-e (precisa de Rotas do Agente 1)
   - **Solução**: Agente 1 cria estrutura de Rotas, Agente 2 cria estrutura de MDF-e, depois integram
3. **Semana 3-4**: Trabalho paralelo com integrações pontuais

---

### 📋 Checklist de Coordenação

**Antes de começar:**
- [ ] Ambos agentes revisam o plano completo
- [ ] Definir convenções de código e migrations (prefixos de data)
- [ ] Criar branch separada para cada agente ou coordenar commits
- [ ] Definir interface de comunicação (Slack, Discord, etc.)

**Durante o desenvolvimento:**
- [ ] Agente 1 comunica quando Prioridade 1 (Rotas) estiver pronta para integração
- [ ] Agente 2 comunica quando Prioridade 3 (CTe) estiver pronta
- [ ] Ambos atualizam status diariamente
- [ ] Revisar código do outro agente antes de integrar

**Antes de integrar:**
- [ ] Agente 1: Verificar se migrations não conflitam com Agente 2
- [ ] Agente 2: Verificar se migrations não conflitam com Agente 1
- [ ] Ambos: Testar localmente antes de merge
- [ ] Documentar APIs e interfaces criadas

---

## 📋 DETALHAMENTO DAS PRIORIDADES

### 🔴 PRIORIDADE MÁXIMA 1: ROTAS - Sistema Completo

**Objetivo**: Implementar sistema completo de gerenciamento de rotas com geração automática, vinculação de veículo, manifesto, manutenção, despesas e métricas.

#### Tarefas:

1. **Estrutura de Dados - Vehicle e Relacionamentos**
   - ✅ Criar migration `create_vehicles_table`
   - ✅ Criar Model `Vehicle` com relacionamentos
   - ✅ Criar migration `create_driver_vehicle_table` (pivot many-to-many)
   - ✅ Criar migration `create_vehicle_maintenances_table`
   - ✅ Criar migration `create_route_expenses_table` (despesas por rota)
   - ✅ Criar migration `create_route_metrics_table` (métricas por rota)
   - ✅ Atualizar migration `routes` para incluir `vehicle_id` e `mdfe_id`
   - ✅ Atualizar Model `Route` com relacionamentos

2. **RouteController Completo**
   - ✅ Métodos CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - ✅ Método `generate()` - Geração automática de rota do motorista
   - ✅ Método `assignVehicle()` - Vincular veículo à rota
   - ✅ Método `assignExpenses()` - Vincular despesas à rota
   - ✅ Método `calculateMetrics()` - Calcular médias da rota
   - ✅ Filtros por motorista, veículo, data, status
   - ✅ Validações completas

3. **RouteService - Lógica de Negócio**
   - ✅ Criar `RouteService` com métodos:
     - `generateRouteForDriver()` - Gerar rota automaticamente
     - `calculateRouteMetrics()` - Calcular médias (consumo, tempo, distância)
     - `linkExpensesToRoute()` - Vincular despesas
     - `linkMaintenanceToRoute()` - Vincular manutenção
     - `optimizeRoute()` - Otimizar ordem de entregas

4. **Views de Rotas**
   - ✅ `routes/index.blade.php` - Listagem com filtros
   - ✅ `routes/create.blade.php` - Criação com wizard
   - ✅ `routes/show.blade.php` - Detalhes completos (veículo, manifesto, despesas, métricas)
   - ✅ `routes/edit.blade.php` - Edição
   - ✅ Componente Livewire para geração automática

5. **Integração com MDF-e**
   - ✅ Vincular MDF-e à rota ao emitir
   - ✅ Exibir status do manifesto na rota
   - ✅ Botão para emitir MDF-e na página da rota

**Arquivos a criar/modificar:**
- `app/Models/Vehicle.php` (NOVO)
- `app/Models/VehicleMaintenance.php` (NOVO)
- `app/Models/RouteExpense.php` (NOVO)
- `app/Models/RouteMetrics.php` (NOVO)
- `app/Http/Controllers/RouteController.php` (NOVO/ATUALIZAR)
- `app/Services/RouteService.php` (NOVO)
- `database/migrations/XXXX_create_vehicles_table.php` (NOVO)
- `database/migrations/XXXX_create_driver_vehicle_table.php` (NOVO)
- `database/migrations/XXXX_create_vehicle_maintenances_table.php` (NOVO)
- `database/migrations/XXXX_create_route_expenses_table.php` (NOVO)
- `database/migrations/XXXX_create_route_metrics_table.php` (NOVO)
- `database/migrations/XXXX_add_vehicle_id_to_routes_table.php` (NOVO)
- `app/Models/Route.php` (MODIFICAR - adicionar relacionamentos)
- `app/Models/Driver.php` (MODIFICAR - adicionar relacionamento many-to-many com Vehicle)
- `resources/views/routes/*.blade.php` (NOVO)

**Tempo estimado**: 4-5 dias

---

### 🔴 PRIORIDADE MÁXIMA 2: MANIFESTO (MDF-e) - Busca e Integração Completa
**Responsável**: 🟢 AGENTE 2

**⚠️ STATUS**: Base de emissão implementada! Faltam busca de documentos do Mitt, interface e webhook.

**Objetivo**: Implementar busca de MDF-es do Mitt, completar integração visual e webhook handler.

#### Tarefas:

1. **MittService - Métodos de Busca** (FALTA IMPLEMENTAR)
   - ⚠️ Método `getMdfe(string $mdfeId)` - Buscar MDF-e completo do Mitt (XML, PDF, dados)
   - ⚠️ Método `getMdfePdf(string $mdfeId)` - Buscar PDF do MDF-e
   - ⚠️ Método `getMdfeXml(string $mdfeId)` - Buscar XML do MDF-e
   - ⚠️ Método `listMdfes(array $filters)` - Listar MDF-es do Mitt por período/filtros
   - ✅ Método `getMdfeStatus()` já existe (apenas status)

2. **FiscalService - Sincronização com Mitt** (FALTA IMPLEMENTAR)
   - ⚠️ Método `syncMdfeFromMitt(string $mittId)` - Buscar e sincronizar MDF-e do Mitt
   - ⚠️ Método `syncRouteMdfe(Route $route)` - Sincronizar MDF-e da rota do Mitt
   - ⚠️ Método `updateDocumentStatusFromWebhook()` - Já existe, mas precisa buscar documentos completos
   - ⚠️ Armazenar XML e PDF do MDF-e quando autorizado
   - ⚠️ Atualizar URLs de PDF e XML no FiscalDocument

3. **Interface de Emissão e Visualização** (FALTA COMPLETAR)
   - ⚠️ Botão "Emitir MDF-e" na view `routes/show.blade.php`
   - ⚠️ Botão "Buscar do Mitt" para sincronizar MDF-e existente
   - ⚠️ Validação visual: verificar se todos os shipments têm CT-e autorizado
   - ⚠️ Card com informações do MDF-e (status, chave de acesso, número)
   - ⚠️ Links para visualizar/download PDF e XML do MDF-e
   - ⚠️ Loading state durante processamento e busca

4. **Webhook Handler** (FALTA COMPLETAR)
   - ⚠️ Completar método `updateDocumentStatusFromWebhook()` no FiscalService
   - ⚠️ Quando MDF-e for autorizado via webhook, buscar XML e PDF do Mitt
   - ⚠️ Atualizar FiscalDocument com dados completos
   - ⚠️ Atualizar rota quando MDF-e for autorizado
   - ⚠️ Notificações de status do MDF-e
   - ⚠️ Tratamento de erros e rejeições

**Arquivos a criar/modificar:**
- `app/Services/MittService.php` (MODIFICAR - adicionar métodos de busca: `getMdfe()`, `getMdfePdf()`, `getMdfeXml()`, `listMdfes()`)
- `app/Services/FiscalService.php` (MODIFICAR - adicionar métodos de sincronização: `syncMdfeFromMitt()`, `syncRouteMdfe()`, melhorar `updateDocumentStatusFromWebhook()`)
- `app/Models/Route.php` (MODIFICAR - relacionamento já existe)
- `resources/views/routes/show.blade.php` (MODIFICAR - adicionar seção MDF-e com busca e visualização)
- `app/Http/Controllers/FiscalController.php` (MODIFICAR - adicionar método `syncMdfe()`)

**Tempo estimado**: 3-4 dias (aumentado devido à busca de documentos)

---

### 🟠 PRIORIDADE ALTA 4: Cadastro de Motorista - CRUD Funcional

**Objetivo**: Implementar CRUD completo e funcional de motoristas com relacionamento many-to-many com veículos.

#### Tarefas:

1. **DriverController Completo**
   - ✅ Métodos CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - ✅ Método `assignVehicles()` - Vincular veículos ao motorista
   - ✅ Método `getRoutes()` - Histórico de rotas do motorista
   - ✅ Método `getMetrics()` - Métricas do motorista
   - ✅ Validações completas (CNH, CPF, etc.)

2. **Views de Motoristas**
   - ✅ `drivers/index.blade.php` - Listagem com filtros
   - ✅ `drivers/create.blade.php` - Formulário de criação
   - ✅ `drivers/show.blade.php` - Detalhes (veículos, rotas, métricas)
   - ✅ `drivers/edit.blade.php` - Formulário de edição
   - ✅ Componente para vincular veículos

3. **Validações e Regras de Negócio**
   - ✅ Validação de CNH (número, categoria, validade)
   - ✅ Validação de CPF
   - ✅ Validação de email e telefone
   - ✅ Verificar se motorista está ativo antes de atribuir rota

**Arquivos a criar/modificar:**
- `app/Http/Controllers/DriverController.php` (NOVO/ATUALIZAR)
- `app/Models/Driver.php` (MODIFICAR - adicionar relacionamento many-to-many com Vehicle)
- `resources/views/drivers/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 2 dias

---

### 🟠 PRIORIDADE ALTA 5: Cadastro de Veículo - CRUD Funcional

**Objetivo**: Implementar CRUD completo de veículos com relacionamento many-to-many com motoristas.

#### Tarefas:

1. **VehicleController Completo**
   - ✅ Métodos CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - ✅ Método `assignDrivers()` - Vincular motoristas ao veículo
   - ✅ Método `getMaintenances()` - Histórico de manutenções
   - ✅ Método `getRoutes()` - Histórico de rotas do veículo
   - ✅ Validações completas (placa, renavam, etc.)

2. **Views de Veículos**
   - ✅ `vehicles/index.blade.php` - Listagem com filtros
   - ✅ `vehicles/create.blade.php` - Formulário de criação
   - ✅ `vehicles/show.blade.php` - Detalhes (motoristas, manutenções, rotas)
   - ✅ `vehicles/edit.blade.php` - Formulário de edição
   - ✅ Componente para vincular motoristas

3. **Validações e Regras de Negócio**
   - ✅ Validação de placa (formato brasileiro)
   - ✅ Validação de RENAVAM (se aplicável)
   - ✅ Verificar se veículo está disponível antes de atribuir rota

**Arquivos a criar/modificar:**
- `app/Http/Controllers/VehicleController.php` (NOVO)
- `app/Models/Vehicle.php` (NOVO)
- `resources/views/vehicles/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 2 dias

---

### 🟠 PRIORIDADE ALTA 6: Sistema de Manutenção

**Objetivo**: Implementar sistema de gestão de manutenções de veículos vinculado às rotas.

#### Tarefas:

1. **VehicleMaintenanceController**
   - ✅ Métodos CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - ✅ Método `linkToRoute()` - Vincular manutenção à rota
   - ✅ Método `getUpcoming()` - Próximas manutenções preventivas
   - ✅ Alertas de manutenção preventiva

2. **Views de Manutenção**
   - ✅ `maintenances/index.blade.php` - Listagem
   - ✅ `maintenances/create.blade.php` - Formulário
   - ✅ `maintenances/show.blade.php` - Detalhes
   - ✅ Alertas visuais de manutenção pendente

3. **Lógica de Negócio**
   - ✅ Cálculo automático de próxima manutenção baseado em km ou tempo
   - ✅ Alertas quando manutenção está próxima
   - ✅ Histórico completo de manutenções por veículo

**Arquivos a criar/modificar:**
- `app/Http/Controllers/VehicleMaintenanceController.php` (NOVO)
- `app/Models/VehicleMaintenance.php` (NOVO)
- `resources/views/maintenances/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 2 dias

---

### 🟠 PRIORIDADE ALTA 7: Sistema de Despesas por Rota

**Objetivo**: Implementar sistema de gestão de despesas específicas por rota.

#### Tarefas:

1. **RouteExpenseController**
   - ✅ Métodos CRUD: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - ✅ Método `linkToRoute()` - Vincular despesa à rota
   - ✅ Método `calculateTotal()` - Calcular total de despesas da rota
   - ✅ Categorias: Combustível, Pedágio, Alimentação, Hospedagem, Outros

2. **Views de Despesas**
   - ✅ `route-expenses/index.blade.php` - Listagem
   - ✅ `route-expenses/create.blade.php` - Formulário
   - ✅ Exibir despesas na página de detalhes da rota
   - ✅ Relatório de despesas por rota

3. **Lógica de Negócio**
   - ✅ Cálculo automático de custo total da rota
   - ✅ Relatórios de despesas por período
   - ✅ Comparação de despesas entre rotas

**Arquivos a criar/modificar:**
- `app/Http/Controllers/RouteExpenseController.php` (NOVO)
- `app/Models/RouteExpense.php` (NOVO)
- `resources/views/route-expenses/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 2 dias

---

### 🟠 PRIORIDADE ALTA 8: Cálculo de Médias e Métricas

**Objetivo**: Implementar sistema de cálculo automático de médias e métricas por rota.

#### Tarefas:

1. **RouteMetricsService**
   - ✅ Criar `RouteMetricsService` com métodos:
     - `calculateFuelConsumption()` - Calcular consumo médio de combustível
     - `calculateAverageTime()` - Calcular tempo médio de rota
     - `calculateAverageDistance()` - Calcular distância média
     - `calculateCostPerKm()` - Calcular custo por km
     - `saveMetrics()` - Salvar métricas na tabela

2. **RouteMetricsController**
   - ✅ Método `getMetrics()` - Obter métricas de uma rota
   - ✅ Método `getDriverMetrics()` - Métricas de um motorista
   - ✅ Método `getVehicleMetrics()` - Métricas de um veículo
   - ✅ Relatórios e dashboards

3. **Views de Métricas**
   - ✅ Exibir métricas na página de detalhes da rota
   - ✅ Dashboard com gráficos de métricas
   - ✅ Comparação de métricas entre rotas/motoristas/veículos

4. **Cálculo Automático**
   - ✅ Calcular métricas automaticamente ao finalizar rota
   - ✅ Atualizar médias históricas
   - ✅ Alertas quando métricas estão fora do esperado

**Arquivos a criar/modificar:**
- `app/Services/RouteMetricsService.php` (NOVO)
- `app/Http/Controllers/RouteMetricsController.php` (NOVO)
- `app/Models/RouteMetrics.php` (NOVO)
- `resources/views/route-metrics/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 3 dias

---

### 🟠 PRIORIDADE ALTA 9: Google Maps API e Geolocalização na Entrega

**Objetivo**: Integrar Google Maps API e garantir que fotos de entrega incluam geolocalização como prova de entrega.

#### Tarefas:

1. **Configuração Google Maps API**
   - ✅ Adicionar `GOOGLE_MAPS_API_KEY` nas variáveis de ambiente
   - ✅ Criar `GoogleMapsService` para interação com API
   - ✅ Métodos: geocoding reverso, cálculo de distância, validação de localização
   - ✅ Configurar limites de uso e billing alerts

2. **Atualização DeliveryProof - Geolocalização Obrigatória**
   - ✅ Modificar migration `delivery_proofs` para tornar latitude/longitude obrigatórios
   - ✅ Adicionar campos: `geolocation_accuracy`, `geolocation_timestamp`, `geolocation_source`
   - ✅ Validação: foto só pode ser salva se geolocalização estiver disponível
   - ✅ Verificar se geolocalização está próxima do endereço de entrega (raio de tolerância)

3. **API do Motorista - Captura de Foto com Geolocalização**
   - ✅ Atualizar endpoint `POST /api/driver/shipments/{id}/delivery-proof`
   - ✅ Exigir geolocalização antes de permitir upload de foto
   - ✅ Capturar geolocalização no momento exato da foto
   - ✅ Validar precisão da geolocalização (accuracy < 50m)
   - ✅ Geocoding reverso para obter endereço completo

4. **PWA do Motorista - Câmera com Geolocalização**
   - ✅ Implementar captura de foto com geolocalização em tempo real
   - ✅ Exibir precisão da localização antes de tirar foto
   - ✅ Bloquear foto se geolocalização não estiver disponível ou imprecisa
   - ✅ Mostrar mapa com localização atual e endereço de entrega
   - ✅ Validar se motorista está próximo do endereço de entrega

5. **Validação de Localização**
   - ✅ Criar método `validateDeliveryLocation()` no `DeliveryProofService`
   - ✅ Comparar coordenadas da foto com endereço de entrega
   - ✅ Calcular distância entre localização da foto e endereço
   - ✅ Alertar se distância for maior que tolerância configurada (ex: 100m)
   - ✅ Permitir override manual com justificativa

**Arquivos a criar/modificar:**
- `app/Services/GoogleMapsService.php` (NOVO)
- `app/Services/DeliveryProofService.php` (NOVO)
- `database/migrations/XXXX_add_geolocation_fields_to_delivery_proofs.php` (NOVO)
- `app/Http/Controllers/Api/DriverController.php` (MODIFICAR - endpoint delivery-proof)
- `app/Models/DeliveryProof.php` (MODIFICAR - adicionar validações)
- `resources/views/driver/delivery-proof.blade.php` (MODIFICAR - adicionar mapa e validação)
- `config/services.php` (MODIFICAR - adicionar google_maps)
- `.env.example` (MODIFICAR - adicionar GOOGLE_MAPS_API_KEY)

**Tempo estimado**: 3-4 dias

---

### 🟠 PRIORIDADE ALTA 10: Timeline/Histórico de Entrega para Cliente

**Objetivo**: Implementar timeline completa de eventos da entrega para o cliente acompanhar em tempo real.

#### Tarefas:

1. **Model ShipmentTimeline**
   - ✅ Criar migration `create_shipment_timelines_table`
   - ✅ Campos: shipment_id, event_type, description, occurred_at, location, metadata
   - ✅ Eventos: created, collected, in_transit, out_for_delivery, delivery_attempt, delivered, exception
   - ✅ Criar Model `ShipmentTimeline` com relacionamentos

2. **ShipmentTimelineService**
   - ✅ Criar `ShipmentTimelineService` com métodos:
     - `recordEvent()` - Registrar evento na timeline
     - `getTimeline()` - Obter timeline completa do shipment
     - `getPublicTimeline()` - Timeline pública para cliente (sem dados sensíveis)
     - `notifyClient()` - Notificar cliente sobre novo evento

3. **Eventos Automáticos na Timeline**
   - ✅ Registrar evento quando shipment é criado
   - ✅ Registrar evento quando status muda
   - ✅ Registrar evento quando foto de entrega é capturada
   - ✅ Registrar evento quando localização é atualizada
   - ✅ Registrar evento quando CT-e é emitido/autorizado
   - ✅ Registrar evento quando MDF-e é emitido/autorizado

4. **API Pública de Timeline**
   - ✅ Endpoint `GET /api/tracking/{tracking_number}/timeline`
   - ✅ Autenticação via token público ou tracking number
   - ✅ Retornar timeline formatada com eventos ordenados
   - ✅ Incluir fotos e documentos quando disponíveis

5. **Views de Timeline**
   - ✅ Componente Livewire `ShipmentTimeline` para exibir timeline
   - ✅ Timeline visual estilo "stepper" ou "timeline vertical"
   - ✅ Exibir fotos, mapas e documentos nos eventos
   - ✅ Página pública `/tracking/{tracking_number}` com timeline
   - ✅ Integração na página de detalhes do shipment

**Arquivos a criar/modificar:**
- `app/Models/ShipmentTimeline.php` (NOVO)
- `app/Services/ShipmentTimelineService.php` (NOVO)
- `app/Http/Controllers/Api/TrackingController.php` (MODIFICAR - adicionar timeline)
- `app/Http/Controllers/TrackingController.php` (NOVO - página pública)
- `database/migrations/XXXX_create_shipment_timelines_table.php` (NOVO)
- `app/Livewire/ShipmentTimeline.php` (NOVO)
- `resources/views/tracking/show.blade.php` (NOVO - página pública)
- `resources/views/shipments/show.blade.php` (MODIFICAR - adicionar timeline)
- `routes/web.php` (MODIFICAR - adicionar rotas públicas)
- `routes/api.php` (MODIFICAR - adicionar endpoint timeline)

**Tempo estimado**: 3-4 dias

---

### 🟠 PRIORIDADE ALTA 11: Integração WuzAPI - Notificações de Status

**Objetivo**: Notificar cliente via WhatsApp a cada mudança de status do shipment, com confirmação quando entregue.

#### Tarefas:

1. **WhatsAppNotificationService**
   - ✅ Criar `WhatsAppNotificationService` especializado em notificações de shipment
   - ✅ Métodos:
     - `notifyStatusChange()` - Notificar mudança de status
     - `notifyDeliveryConfirmation()` - Solicitar confirmação de entrega
     - `notifyTimelineUpdate()` - Notificar novo evento na timeline
     - `sendTrackingLink()` - Enviar link de rastreamento

2. **Templates de Mensagens WhatsApp**
   - ✅ Criar templates para cada tipo de evento:
     - Shipment criado
     - Coletado
     - Em trânsito
     - Saiu para entrega
     - Tentativa de entrega
     - Entregue (com solicitação de confirmação)
     - Exceção/Problema
   - ✅ Templates personalizáveis por tenant
   - ✅ Suporte a variáveis dinâmicas (tracking_number, cliente, endereço, etc.)

3. **Listener para Mudanças de Status**
   - ✅ Atualizar `ShipmentStatusChanged` notification
   - ✅ Criar Listener `SendWhatsAppStatusNotification`
   - ✅ Disparar notificação via WuzAPI quando status mudar
   - ✅ Registrar evento na timeline após notificar

4. **Sistema de Confirmação de Entrega**
   - ✅ Quando shipment é marcado como entregue, enviar mensagem WhatsApp:
     - Foto do produto entregue
     - Link para confirmar recebimento
     - Botões de ação: "Confirmar Recebimento" / "Reportar Problema"
   - ✅ Criar endpoint para receber confirmação do cliente
   - ✅ Atualizar status do shipment baseado na confirmação
   - ✅ Registrar confirmação na timeline

5. **Webhook Handler para Respostas WhatsApp**
   - ✅ Atualizar `WebhookController` para processar respostas do cliente
   - ✅ Processar confirmação de entrega
   - ✅ Processar reporte de problemas
   - ✅ Atualizar shipment e timeline baseado na resposta

6. **Dashboard de Notificações**
   - ✅ Exibir histórico de notificações enviadas
   - ✅ Status de leitura das mensagens
   - ✅ Taxa de confirmação de entrega
   - ✅ Relatórios de engajamento

**Arquivos a criar/modificar:**
- `app/Services/WhatsAppNotificationService.php` (NOVO)
- `app/Models/WhatsAppMessageTemplate.php` (MODIFICAR - adicionar templates de status)
- `app/Listeners/SendWhatsAppStatusNotification.php` (NOVO)
- `app/Http/Controllers/Api/DeliveryConfirmationController.php` (NOVO)
- `app/Http/Controllers/WebhookController.php` (MODIFICAR - processar confirmações)
- `app/Notifications/ShipmentStatusChanged.php` (MODIFICAR - integrar WhatsApp)
- `database/migrations/XXXX_add_templates_to_whatsapp_message_templates.php` (NOVO)
- `resources/views/notifications/index.blade.php` (NOVO - dashboard)

**Tempo estimado**: 4-5 dias

---

### 🔗 Integração entre Funcionalidades

**Fluxo Completo de Entrega com Notificações:**

1. **Motorista captura foto da entrega**
   - ✅ Geolocalização é capturada automaticamente
   - ✅ Foto é salva com coordenadas GPS
   - ✅ Evento "delivered" é registrado na timeline

2. **Sistema processa entrega**
   - ✅ Valida geolocalização (proximidade ao endereço)
   - ✅ Atualiza status do shipment para "delivered"
   - ✅ Dispara notificação WhatsApp para cliente

3. **Cliente recebe notificação**
   - ✅ Mensagem WhatsApp com foto do produto
   - ✅ Link para visualizar timeline completa
   - ✅ Botões para confirmar recebimento ou reportar problema

4. **Cliente confirma recebimento**
   - ✅ Resposta via WhatsApp é processada
   - ✅ Status do shipment é atualizado
   - ✅ Timeline é atualizada com confirmação
   - ✅ Notificação interna para transportadora

**Arquivos de Integração:**
- `app/Events/ShipmentDelivered.php` (NOVO)
- `app/Listeners/ProcessDeliveryConfirmation.php` (NOVO)
- `app/Jobs/SendDeliveryConfirmationWhatsApp.php` (NOVO)

---

---

## 📚 REFERÊNCIAS DETALHADAS DAS PRIORIDADES

> **NOTA**: As seções abaixo contêm detalhamento completo de cada prioridade. Consulte a seção "👥 DIVISÃO DO TRABALHO - 2 AGENTES" acima para ver qual agente é responsável por cada prioridade.

### 🔴 PRIORIDADE MÁXIMA 2: MANIFESTO (MDF-e) - Emissão Completa
**Responsável**: 🟢 AGENTE 2

#### Tarefas:
1. **Criar MittService.php**
   - Implementar métodos de comunicação com API Mitt
   - Métodos: `issueCte()`, `issueMdfe()`, `cancelCte()`, `getSpedData()`
   - Tratamento de erros e retry logic
   - Logging detalhado

2. **Sistema de Eventos e Jobs**
   - Criar Event: `CteIssuanceRequested`
   - Criar Event: `MdfeIssuanceRequested`
   - Criar Listener: `ProcessCteIssuance`
   - Criar Listener: `ProcessMdfeIssuance`
   - Criar Job: `SendCteToMittJob`
   - Criar Job: `SendMdfeToMittJob`
   - Configurar filas (queue workers)

3. **FiscalService (Núcleo Fiscal)**
   - Criar classe `FiscalService` como orquestrador
   - Pré-validação de dados antes de enviar ao Mitt
   - Gerenciamento de status dos documentos fiscais
   - Armazenamento de respostas e IDs do Mitt

4. **Interface de Emissão**
   - Adicionar botão "Emitir CT-e" na página de detalhes do Shipment
   - Adicionar botão "Emitir MDF-e" na página de Route
   - Status visual dos documentos fiscais
   - Feedback ao usuário (loading, sucesso, erro)

5. **Webhook Handler Mitt**
   - Completar método `handleFiscalDocumentUpdate()` no `WebhookController`
   - Atualização automática de status quando Mitt responder
   - Notificações de erros

6. **Migrations e Models Fiscais**
   - Criar migration para `fiscal_documents` table (CT-e, MDF-e)
   - Model `FiscalDocument` com relacionamentos
   - Campos: tipo, status, mitt_id, xml, erros, etc.

**Arquivos a criar/modificar:**
- `app/Services/MittService.php` (NOVO)
- `app/Services/FiscalService.php` (NOVO)
- `app/Events/CteIssuanceRequested.php` (NOVO)
- `app/Events/MdfeIssuanceRequested.php` (NOVO)
- `app/Listeners/ProcessCteIssuance.php` (NOVO)
- `app/Listeners/ProcessMdfeIssuance.php` (NOVO)
- `app/Jobs/SendCteToMittJob.php` (NOVO)
- `app/Jobs/SendMdfeToMittJob.php` (NOVO)
- `app/Models/FiscalDocument.php` (NOVO)
- `database/migrations/XXXX_create_fiscal_documents_table.php` (NOVO)
- `app/Http/Controllers/WebhookController.php` (MODIFICAR - método mitt)
- Views de Shipment e Route (MODIFICAR - adicionar botões)

**Tempo estimado**: 2-3 dias (BAIXA PRIORIDADE - fazer por último)

---

### 🔴 PRIORIDADE MÁXIMA 3: CTe - Busca e Integração Completa
**Responsável**: 🟢 AGENTE 2

**⚠️ STATUS**: Base de emissão implementada! Faltam busca de documentos do Mitt, interface e webhook.

**Objetivo**: Implementar busca de CT-es do Mitt, completar integração visual e webhook handler.

#### Tarefas:

1. **MittService - Métodos de Busca** (FALTA IMPLEMENTAR)
   - ⚠️ Método `getCte(string $cteId)` - Buscar CT-e completo do Mitt (XML, PDF, dados)
   - ⚠️ Método `getCtePdf(string $cteId)` - Buscar PDF do CT-e
   - ⚠️ Método `getCteXml(string $cteId)` - Buscar XML do CT-e
   - ⚠️ Método `listCtes(array $filters)` - Listar CT-es do Mitt por período/filtros
   - ✅ Método `getCteStatus()` já existe (apenas status)

2. **FiscalService - Sincronização com Mitt** (FALTA IMPLEMENTAR)
   - ⚠️ Método `syncCteFromMitt(string $mittId)` - Buscar e sincronizar CT-e do Mitt
   - ⚠️ Método `syncShipmentCte(Shipment $shipment)` - Sincronizar CT-e do shipment do Mitt
   - ⚠️ Método `updateDocumentStatusFromWebhook()` - Já existe, mas precisa buscar documentos completos
   - ⚠️ Armazenar XML e PDF do CT-e quando autorizado
   - ⚠️ Atualizar URLs de PDF e XML no FiscalDocument

3. **Interface de Emissão e Visualização** (FALTA COMPLETAR)
   - ⚠️ Botão "Emitir CT-e" na view `shipments/show.blade.php`
   - ⚠️ Botão "Buscar do Mitt" para sincronizar CT-e existente
   - ⚠️ Validação visual de dados antes de emitir
   - ⚠️ Card com informações do CT-e (status, chave de acesso, número)
   - ⚠️ Links para visualizar/download PDF e XML do CT-e
   - ⚠️ Loading state durante processamento e busca

4. **Webhook Handler** (FALTA COMPLETAR)
   - ⚠️ Completar método `updateDocumentStatusFromWebhook()` no FiscalService
   - ⚠️ Quando CT-e for autorizado via webhook, buscar XML e PDF do Mitt
   - ⚠️ Atualizar FiscalDocument com dados completos
   - ⚠️ Atualizar shipment quando CT-e for autorizado
   - ⚠️ Notificações de status do CT-e
   - ⚠️ Tratamento de erros e rejeições

**Arquivos a criar/modificar:**
- `app/Services/MittService.php` (MODIFICAR - adicionar métodos de busca: `getCte()`, `getCtePdf()`, `getCteXml()`, `listCtes()`)
- `app/Services/FiscalService.php` (MODIFICAR - adicionar métodos de sincronização: `syncCteFromMitt()`, `syncShipmentCte()`, melhorar `updateDocumentStatusFromWebhook()`)
- `app/Models/Shipment.php` (MODIFICAR - relacionamento já existe)
- `resources/views/shipments/show.blade.php` (MODIFICAR - adicionar seção CT-e com busca e visualização)
- `app/Http/Controllers/FiscalController.php` (MODIFICAR - adicionar método `syncCte()`)

**Tempo estimado**: 3-4 dias (aumentado devido à busca de documentos)

---

### 🟠 PRIORIDADE ALTA 10: Timeline/Histórico de Entrega para Cliente
**Responsável**: 🟢 AGENTE 2

#### Tarefas:
1. **Módulo de Faturamento**
   - Models: `Invoice`, `InvoiceItem`
   - Migrations para `invoices` e `invoice_items`
   - Componente Livewire `InvoicingTool`
   - Lógica de agrupamento de Shipments:
     - Selecionar cliente
     - Selecionar período
     - Listar Shipments não faturados (com CT-e emitido)
     - Permitir seleção múltipla
     - Gerar fatura
   - Status: Aberta, Paga, Vencida
   - Data de vencimento configurável
   - Views: `/invoicing` (ferramenta), visualização de faturas

2. **Módulo Contas a Receber**
   - Controller `AccountsReceivableController`
   - Página `/accounts/receivable`
   - Listagem de todas as faturas
   - Filtros: status, cliente, período
   - Funcionalidade de "Registrar Pagamento" (baixa manual)
   - Relatório de faturas vencidas
   - Views completas

3. **Módulo Contas a Pagar**
   - Model `Expense` (Despesa)
   - Migration `create_expenses_table`
   - Controller `ExpenseController` - CRUD completo
   - Categorias de despesas (model `ExpenseCategory` ou enum)
   - Status: A Pagar, Pago
   - Data de vencimento
   - Página `/accounts/payable`
   - Views completas

4. **Módulo Fluxo de Caixa**
   - Controller `CashFlowController`
   - Página `/cash-flow`
   - Consolidação de transações:
     - Recebimentos (pagamentos de faturas)
     - Pagamentos (despesas pagas)
   - Ordenação cronológica
   - Filtros por data
   - Saldo consolidado (calculado)
   - Views estilo extrato bancário

5. **Relacionamentos e Integrações**
   - Relacionar Invoice com Shipments (via InvoiceItems)
   - Relacionar Invoice com Payments
   - Relacionar Expense com Payments
   - Criar model `Payment` se necessário (já existe básico)

**Arquivos a criar/modificar:**
- `app/Models/Invoice.php` (NOVO)
- `app/Models/InvoiceItem.php` (NOVO)
- `app/Models/Expense.php` (NOVO)
- `app/Models/ExpenseCategory.php` (NOVO - opcional)
- `app/Livewire/InvoicingTool.php` (NOVO)
- `app/Http/Controllers/AccountsReceivableController.php` (NOVO)
- `app/Http/Controllers/ExpenseController.php` (NOVO)
- `app/Http/Controllers/CashFlowController.php` (NOVO)
- `database/migrations/XXXX_create_invoices_table.php` (NOVO)
- `database/migrations/XXXX_create_invoice_items_table.php` (NOVO)
- `database/migrations/XXXX_create_expenses_table.php` (NOVO)
- `resources/views/invoicing/*.blade.php` (NOVO)
- `resources/views/accounts/receivable/*.blade.php` (NOVO)
- `resources/views/accounts/payable/*.blade.php` (NOVO)
- `resources/views/cash-flow/*.blade.php` (NOVO)
- `routes/web.php` (MODIFICAR - adicionar rotas)

**Tempo estimado**: 3-4 dias

---

### 🟠 PRIORIDADE ALTA 11: Integração WuzAPI - Notificações de Status
**Responsável**: 🟢 AGENTE 2

#### Tarefas:
1. **RouteController Completo**
   - Métodos: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
   - Associação de múltiplos Shipments a uma rota
   - Associação de Driver a rota
   - Filtros por driver, data, status
   - Views completas

2. **Interface de Rotas**
   - Listagem de rotas (`/routes`)
   - Criar rota (`/routes/create`)
   - Visualizar rota (`/routes/{id}`)
   - Editar rota (`/routes/{id}/edit`)
   - Drag-and-drop para associar Shipments (opcional, mas desejável)
   - Integração com botão "Emitir MDF-e" (depois que Agente 1 implementar)

3. **PWA - App do Motorista**
   - Criar área separada `/driver/*`
   - Autenticação específica para motoristas (middleware)
   - `DriverDashboardController`
   - Interface mobile-first:
     - Listagem de entregas da rota atual
     - Card para cada entrega (Shipment)
     - Botão para atualizar status
     - Botão para capturar foto
   - Upload de foto de comprovante (integrar com `DeliveryProof`)
   - Atualização de geolocalização (integrar com `LocationTracking`)
   - Service Worker para PWA
   - Manifest.json para PWA

4. **API para App do Motorista**
   - Endpoints REST para driver:
     - `GET /api/driver/routes/active` - Rota ativa
     - `GET /api/driver/shipments` - Entregas da rota
     - `POST /api/driver/shipments/{id}/update-status` - Atualizar status
     - `POST /api/driver/shipments/{id}/delivery-proof` - Upload foto
     - `POST /api/driver/location/update` - Atualizar localização
   - Autenticação via Sanctum para drivers

5. **Integração com Geolocalização**
   - Endpoint para atualização de localização do driver
   - Armazenar em `location_trackings` table
   - Atualizar `drivers.current_latitude/longitude`
   - Rastreamento em tempo real (opcional: WebSockets)

**Arquivos a criar/modificar:**
- `app/Http/Controllers/RouteController.php` (NOVO)
- `app/Http/Controllers/DriverDashboardController.php` (NOVO)
- `app/Http/Controllers/Api/DriverController.php` (NOVO)
- `resources/views/routes/*.blade.php` (NOVO)
- `resources/views/driver/dashboard.blade.php` (NOVO)
- `resources/views/driver/delivery-card.blade.php` (NOVO - componente)
- `public/sw.js` (NOVO - Service Worker)
- `public/manifest.json` (NOVO - PWA Manifest)
- `routes/api.php` (MODIFICAR - adicionar rotas driver)
- `routes/web.php` (MODIFICAR - adicionar rotas driver)

**Tempo estimado**: 3-4 dias

---

## 📋 Cronograma de Execução - 2 Agentes

### Semana 1: Fundação (Dias 1-5)
**🔵 AGENTE 1:**
- Dias 1-2: Prioridade 4 (Cadastro de Motorista) - CRUD completo
- Dias 3-4: Prioridade 5 (Cadastro de Veículo) - CRUD completo
- Dia 5: Integração Motorista-Veículo (relacionamento many-to-many)

**🟢 AGENTE 2:**
- Dias 1-3: Prioridade 3 (CTe) - Estrutura completa e emissão
- Dias 4-5: Prioridade 3 (CTe) - Interface e webhooks

**Resultado Semana 1**: Base de cadastros e CTe funcionando

---

### Semana 2: Core Features (Dias 6-10)
**🔵 AGENTE 1:**
- Dias 6-8: Prioridade 1 (Rotas) - Estrutura completa, CRUD, relacionamentos
- Dias 9-10: Prioridade 1 (Rotas) - Geração automática e RouteService

**🟢 AGENTE 2:**
- Dias 6-7: Prioridade 2 (MDF-e) - Estrutura e emissão (aguardar Rotas do Agente 1)
- Dias 8-10: Prioridade 2 (MDF-e) - Integração com Rotas e interface

**Resultado Semana 2**: Rotas e MDF-e funcionando e integrados

---

### Semana 3: Funcionalidades Complementares (Dias 11-15)
**🔵 AGENTE 1:**
- Dia 11: Prioridade 6 (Manutenção) - CRUD e vinculação à rota
- Dia 12: Prioridade 7 (Despesas por Rota) - CRUD e cálculos
- Dias 13-15: Prioridade 8 (Cálculo de Médias) - Service completo e métricas

**🟢 AGENTE 2:**
- Dias 11-13: Prioridade 10 (Timeline) - Model, Service e API pública
- Dias 14-15: Prioridade 10 (Timeline) - Views e componente Livewire

**Resultado Semana 3**: Manutenção, Despesas, Métricas e Timeline funcionando

---

### Semana 4: Integrações Finais (Dias 16-20)
**🔵 AGENTE 1:**
- Dias 16-18: Prioridade 9 (Google Maps API) - Service e configuração
- Dias 19-20: Prioridade 9 (Geolocalização) - Validação e integração com DeliveryProof

**🟢 AGENTE 2:**
- Dias 16-18: Prioridade 11 (Notificações WhatsApp) - Service e templates
- Dias 19-20: Prioridade 11 (Confirmação de Entrega) - Webhooks e integração completa

**Resultado Semana 4**: Google Maps, Geolocalização e Notificações WhatsApp funcionando

---

## ✅ Checklist de Validação por Agente

### 🔵 AGENTE 1 - Checklist Final:

**Prioridade 1 (Rotas):**
- [ ] CRUD completo de rotas funcionando
- [ ] Geração automática de rota do motorista
- [ ] Vinculação de veículo, despesas e métricas à rota
- [ ] RouteService com todos os métodos implementados
- [ ] Views completas (index, create, show, edit)

**Prioridade 4 (Motorista):**
- [ ] CRUD completo de motoristas
- [ ] Relacionamento many-to-many com veículos funcionando
- [ ] Validações de CNH, CPF, etc.
- [ ] Views completas

**Prioridade 5 (Veículo):**
- [ ] CRUD completo de veículos
- [ ] Relacionamento many-to-many com motoristas funcionando
- [ ] Validações de placa, RENAVAM, etc.
- [ ] Views completas

**Prioridade 6 (Manutenção):**
- [ ] CRUD completo de manutenções
- [ ] Vinculação à rota funcionando
- [ ] Alertas de manutenção preventiva

**Prioridade 7 (Despesas):**
- [ ] CRUD completo de despesas por rota
- [ ] Cálculo automático de total de despesas
- [ ] Categorias funcionando

**Prioridade 8 (Métricas):**
- [ ] RouteMetricsService calculando todas as métricas
- [ ] Métricas sendo salvas automaticamente
- [ ] Relatórios e dashboards funcionando

**Prioridade 9 (Google Maps e Geolocalização):**
- [ ] GoogleMapsService configurado e funcionando
- [ ] Geolocalização obrigatória na captura de foto
- [ ] Validação de proximidade ao endereço
- [ ] DeliveryProof atualizado com campos de geolocalização

---

### 🟢 AGENTE 2 - Checklist Final:

**Prioridade 2 (MDF-e):**
- [ ] Emissão de MDF-e funcionando via Mitt
- [ ] Vinculação de MDF-e à rota
- [ ] Status tracking e webhooks funcionando
- [ ] Interface de emissão na página de rota

**Prioridade 3 (CTe):**
- [ ] Emissão de CT-e funcionando via Mitt
- [ ] Vinculação de CT-e ao shipment
- [ ] Status tracking e webhooks funcionando
- [ ] Interface de emissão na página de shipment

**Prioridade 10 (Timeline):**
- [ ] ShipmentTimeline model e migration criados
- [ ] ShipmentTimelineService registrando eventos automaticamente
- [ ] API pública de timeline funcionando
- [ ] Componente Livewire exibindo timeline visual
- [ ] Página pública de rastreamento funcionando

**Prioridade 11 (Notificações WhatsApp):**
- [ ] WhatsAppNotificationService enviando notificações
- [ ] Templates de mensagens configurados
- [ ] Notificação a cada mudança de status funcionando
- [ ] Sistema de confirmação de entrega funcionando
- [ ] Webhook processando respostas do cliente
- [ ] Dashboard de notificações funcionando

---

## 📝 Notas Importantes

1. **Comunicação**: Cada agente deve documentar interfaces e contratos para facilitar integração
2. **Testes**: Criar testes básicos para funcionalidades críticas
3. **Migrações**: Coordenar nomes de migrations para evitar conflitos
4. **Views**: Seguir identidade visual do index.html e pracas.html
5. **Segurança**: Sempre validar tenant_id e permissões
6. **Performance**: Usar eager loading em relacionamentos
7. **Logs**: Logar ações importantes para debug

---

**Status**: Plano dividido entre 2 agentes - Pronto para início do desenvolvimento distribuído  
**Próximo Passo**: 
- 🔵 **AGENTE 1**: Começar pela Semana 1 - Prioridade 4 (Cadastro de Motorista)
- 🟢 **AGENTE 2**: Começar pela Semana 1 - Prioridade 3 (CTe)

**Coordenador**: Agente 1 deve comunicar progresso diário e coordenar integrações na Semana 2
