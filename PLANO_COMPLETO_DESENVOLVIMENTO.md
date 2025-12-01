# Plano Completo de Desenvolvimento - TMS SaaS

**Data de Criação**: 04/11/2025  
**Objetivo**: Mapear todo o sistema, identificar o que está pronto, o que falta e criar um plano detalhado para cada página, funcionalidade e integração.

---

## 📊 Análise Completa do Sistema

### ✅ O QUE JÁ ESTÁ IMPLEMENTADO

#### **Fase 0: Fundação e Estrutura**
- ✅ **Multi-Tenant**: Sistema completo com `spatie/laravel-multitenancy`
  - Models: `Tenant`, `Plan`, `Subscription`, `Payment`
  - Migrations completas
  - Relacionamentos configurados
  - Isolamento de dados por tenant

- ✅ **Billing/Assinaturas**: Sistema básico implementado
  - `AsaasService` criado (métodos básicos)
  - `SubscriptionController` implementado
  - Webhooks de Asaas configurados
  - Views de planos e assinaturas (`subscriptions/*.blade.php`)

- ✅ **Permissões**: Sistema de roles implementado
  - `spatie/laravel-permission` instalado
  - Roles: Admin Tenant, Financeiro, Operacional, Vendedor
  - Seeder de roles criado

- ✅ **Autenticação**: Sistema completo
  - Login/Register implementado
  - Middleware de autenticação
  - Proteção de rotas

#### **Módulo 1: Configurações da Transportadora**
- ✅ **Empresas e Filiais**: CRUD completo
  - Models: `Company`, `Branch`
  - `CompanyController` implementado
  - Migrations e relacionamentos
  - Views básicas criadas (`companies/*.blade.php`)

#### **Módulo 2: CRM**
- ✅ **Clientes**: Model e migration criados
  - `Client`, `ClientAddress` models
  - Relacionamentos configurados
  - ⚠️ **FALTA**: Controller e Views de CRUD de clientes

- ✅ **Vendedores**: CRUD completo
  - `Salesperson` model e controller
  - Sistema de desconto máximo por vendedor
  - Views criadas (`salespeople/*.blade.php`)

- ✅ **Propostas Comerciais**: CRUD completo
  - `Proposal` model e controller
  - Cálculo de desconto com validação
  - Views criadas (`proposals/*.blade.php`)

- ✅ **Dashboard do Vendedor**: Implementado
  - `SalespersonDashboardController` criado
  - View `salesperson/dashboard.blade.php` criada
  - Integração com cálculo de frete

#### **Módulo 3: Operacional - Coletas e Entregas**
- ✅ **Models e Migrations**: Estrutura completa
  - `Shipment` model e migration
  - `Route` model e migration
  - `Driver` model e migration
  - `DeliveryProof` model e migration
  - `LocationTracking` model e migration

- ✅ **ShipmentController**: CRUD completo implementado
  - Métodos: `index()`, `create()`, `store()`, `show()`, `edit()`, `update()`, `destroy()`
  - Filtros por status, cliente, data
  - Paginação

- ✅ **Componente Livewire CreateShipment**: Wizard de 3 passos implementado
  - Passo 1: Selecionar Remetente e preencher Destinatário
  - Passo 2: Dados da mercadoria (peso, volume, valor, NFe)
  - Passo 3: Calcular frete usando tabelas de frete
  - View `livewire/create-shipment.blade.php` criada

- ✅ **Views de Shipments**: Completas
  - `shipments/index.blade.php` - Listagem com filtros
  - `shipments/create-livewire.blade.php` - Formulário com wizard
  - `shipments/show.blade.php` - Detalhes completos
  - `shipments/edit.blade.php` - Edição

- ✅ **Tabelas de Frete**: CRUD completo
  - Model `FreightTable` criado
  - Migration `create_freight_tables_table.php` criada
  - `FreightTableController` implementado
  - Views criadas (`freight-tables/*.blade.php`)
  - `FreightCalculationService` com lógica completa do pracas.html

- ✅ **API MCP para Cálculo de Frete**: Implementado
  - `McpFreightController` criado
  - Endpoints para integração com n8n
  - Rotas configuradas em `routes/api.php`

#### **Módulo 4: Integração Fiscal**
- ✅ **MittService**: Implementado
  - Métodos: `issueCte()`, `issueMdfe()`, `cancelCte()`, `getSpedData()`
  - Tratamento de erros e retry logic
  - Logging detalhado

- ✅ **FiscalService**: Núcleo Fiscal implementado
  - Métodos: `requestCteIssuance()`, `requestMdfeIssuance()`, `cancelCte()`
  - Pré-validação de dados
  - Gerenciamento de status dos documentos fiscais

- ✅ **Sistema de Eventos e Jobs**: Implementado
  - Event: `CteIssuanceRequested`
  - Event: `MdfeIssuanceRequested`
  - Listener: `ProcessCteIssuance`
  - Listener: `ProcessMdfeIssuance`
  - Job: `SendCteToMittJob`
  - Job: `SendMdfeToMittJob`

- ✅ **Model FiscalDocument**: Criado
  - Migration `create_fiscal_documents_table.php` criada
  - Relacionamentos configurados
  - Métodos auxiliares (`isCte()`, `isAuthorized()`, etc.)

- ⚠️ **FALTA**: Interface para emissão de CT-e na página de detalhes do Shipment
- ⚠️ **FALTA**: Interface para emissão de MDF-e na página de Route
- ⚠️ **FALTA**: Webhook handler completo para atualizações do Mitt (parcialmente implementado)

#### **Módulo 5: Gestão Financeira**
- ✅ **Faturamento**: Completo
  - Models: `Invoice`, `InvoiceItem`
  - Migrations criadas
  - Componente Livewire `InvoicingTool` implementado
  - `InvoicingController` implementado
  - Views criadas (`invoicing/*.blade.php`)

- ✅ **Contas a Receber**: Completo
  - `AccountsReceivableController` implementado
  - Views criadas (`accounts/receivable/*.blade.php`)
  - Registro de pagamentos
  - Relatório de faturas vencidas

- ✅ **Contas a Pagar**: Completo
  - Model `Expense` criado
  - Model `ExpenseCategory` criado
  - `ExpenseController` implementado
  - Views criadas (`accounts/payable/*.blade.php`)

- ✅ **Fluxo de Caixa**: Completo
  - `CashFlowController` implementado
  - View `cash-flow/index.blade.php` criada
  - Extrato consolidado

#### **Integração WhatsApp**
- ✅ **WuzAPI**: Serviço implementado
  - `WuzApiService` completo
  - `WhatsAppAiService` criado (integração OpenAI)
  - Webhooks configurados
  - API de rastreamento pública (`TrackingController`)

---

## ❌ O QUE FALTA SER IMPLEMENTADO

### 🔴 PRIORIDADE CRÍTICA (Core do Negócio)

#### 1. **CRUD de Clientes (Clientes)**
**Status**: Model existe, mas falta Controller e Views

**Arquivos a criar:**
- `app/Http/Controllers/ClientController.php`
- `resources/views/clients/index.blade.php`
- `resources/views/clients/create.blade.php`
- `resources/views/clients/edit.blade.php`
- `resources/views/clients/show.blade.php`

**Funcionalidades:**
- Listagem de clientes com filtros (nome, CNPJ, cidade, estado)
- Cadastro completo de cliente (dados fiscais, contatos)
- Múltiplos endereços (coleta/entrega)
- Edição e exclusão
- Associação com vendedor
- Rotas em `routes/web.php`

**Tempo estimado**: 1 dia

---

#### 2. **Módulo Rotas - CRUD Completo**
**Status**: Model existe, mas falta Controller e Views

**Arquivos a criar:**
- `app/Http/Controllers/RouteController.php`
- `resources/views/routes/index.blade.php`
- `resources/views/routes/create.blade.php`
- `resources/views/routes/edit.blade.php`
- `resources/views/routes/show.blade.php`

**Funcionalidades:**
- Listagem de rotas com filtros (driver, data, status)
- Criação de rota
- Associação de múltiplos Shipments a uma rota
- Associação de Driver a rota
- Visualização de rota com mapa (opcional)
- Edição e exclusão
- Botão "Emitir MDF-e" (integração com FiscalService)
- Status visual da rota
- Rotas em `routes/web.php`

**Tempo estimado**: 2 dias

---

#### 3. **CRUD de Motoristas (Drivers)**
**Status**: Model existe, mas falta Controller e Views

**Arquivos a criar:**
- `app/Http/Controllers/DriverController.php`
- `resources/views/drivers/index.blade.php`
- `resources/views/drivers/create.blade.php`
- `resources/views/drivers/edit.blade.php`
- `resources/views/drivers/show.blade.php`

**Funcionalidades:**
- Listagem de motoristas com filtros
- Cadastro completo (nome, CPF, CNH, telefone, veículo)
- Edição e exclusão
- Status (ativo/inativo)
- Associação com rotas
- Rotas em `routes/web.php`

**Tempo estimado**: 1 dia

---

#### 4. **Interface de Emissão Fiscal (CT-e e MDF-e)**
**Status**: Backend completo, falta interface visual

**Arquivos a modificar:**
- `resources/views/shipments/show.blade.php` - Adicionar botão "Emitir CT-e"
- `resources/views/routes/show.blade.php` - Adicionar botão "Emitir MDF-e"

**Arquivos a criar:**
- `app/Http/Controllers/FiscalController.php` - Métodos para emitir CT-e/MDF-e
- `resources/views/fiscal/timeline.blade.php` - Componente de linha do tempo fiscal
- `resources/views/fiscal/cte-status.blade.php` - Status visual do CT-e
- `resources/views/fiscal/mdfe-status.blade.php` - Status visual do MDF-e

**Funcionalidades:**
- Botão "Emitir CT-e" na página de detalhes do Shipment
- Botão "Emitir MDF-e" na página de detalhes da Route
- Linha do tempo fiscal mostrando status em tempo real
- Modal com detalhes do documento fiscal
- Links para PDF/XML quando autorizado
- Mensagens de erro claras e acionáveis
- Integração com `FiscalService`
- Rotas em `routes/web.php`

**Tempo estimado**: 2 dias

---

#### 5. **Webhook Handler Completo para Mitt**
**Status**: Parcialmente implementado

**Arquivos a modificar:**
- `app/Http/Controllers/WebhookController.php` - Completar método `mitt()`

**Funcionalidades:**
- Validação de assinatura do webhook
- Processamento de atualizações de status
- Atualização automática de `FiscalDocument`
- Notificações ao usuário quando documento for autorizado
- Tratamento de erros
- Logging detalhado

**Tempo estimado**: 1 dia

---

### 🟡 PRIORIDADE ALTA (Funcionalidades Importantes)

#### 6. **PWA - App do Motorista**
**Status**: Não implementado

**Arquivos a criar:**
- `app/Http/Controllers/DriverDashboardController.php`
- `app/Http/Controllers/Api/DriverController.php`
- `resources/views/driver/dashboard.blade.php`
- `resources/views/driver/delivery-card.blade.php`
- `public/sw.js` - Service Worker
- `public/manifest.json` - PWA Manifest
- `resources/views/driver/layout.blade.php` - Layout mobile-first

**Funcionalidades:**
- Área separada `/driver/dashboard`
- Autenticação específica para motoristas (middleware)
- Listagem de entregas da rota atual
- Card para cada entrega (Shipment)
- Botão para atualizar status de entrega
- Botão para capturar foto de comprovante
- Upload de foto (integrar com `DeliveryProof`)
- Atualização de geolocalização (integrar com `LocationTracking`)
- Interface mobile-first otimizada
- PWA instalável
- API REST para app mobile (futuro)
- Rotas em `routes/web.php` e `routes/api.php`

**Tempo estimado**: 3-4 dias

---

#### 7. **Dashboard Principal com Métricas**
**Status**: View básica existe, falta métricas

**Arquivos a modificar:**
- `resources/views/dashboard.blade.php`

**Funcionalidades:**
- Cards com métricas principais:
  - Total de cargas (pendentes, em trânsito, entregues)
  - Faturas (abertas, vencidas, pagas)
  - Receita do mês
  - Despesas do mês
  - Saldo consolidado
- Gráficos (Chart.js ou similar):
  - Cargas por status (gráfico de pizza)
  - Receita vs Despesas (gráfico de linha)
  - Cargas por mês (gráfico de barras)
- Lista de ações recentes
- Links rápidos para funcionalidades principais
- Widgets configuráveis (opcional)

**Tempo estimado**: 2 dias

---

#### 8. **Sistema de Notificações**
**Status**: Não implementado

**Arquivos a criar:**
- `app/Notifications/ShipmentStatusChanged.php`
- `app/Notifications/InvoiceOverdue.php`
- `app/Notifications/CteAuthorized.php`
- `app/Notifications/MdfeAuthorized.php`
- `resources/views/notifications/index.blade.php`
- `resources/views/components/notification-bell.blade.php`

**Funcionalidades:**
- Notificações em tempo real (Laravel Notifications)
- Bell de notificações no header
- Lista de notificações não lidas
- Marcar como lida
- Notificações por email (opcional)
- Notificações push (futuro)

**Tempo estimado**: 2 dias

---

### 🟢 PRIORIDADE MÉDIA (Melhorias e Otimizações)

#### 9. **Relatórios e Exportação**
**Status**: Não implementado

**Arquivos a criar:**
- `app/Http/Controllers/ReportController.php`
- `resources/views/reports/index.blade.php`
- `resources/views/reports/shipments.blade.php`
- `resources/views/reports/financial.blade.php`
- `app/Exports/ShipmentsExport.php` (Laravel Excel)
- `app/Exports/FinancialExport.php`

**Funcionalidades:**
- Relatório de cargas (filtros por período, cliente, status)
- Relatório financeiro (receitas, despesas, fluxo de caixa)
- Exportação para PDF
- Exportação para Excel
- Gráficos nos relatórios
- Agendamento de relatórios (futuro)

**Tempo estimado**: 3 dias

---

#### 10. **Sistema de Rastreamento Público**
**Status**: API existe, falta interface web

**Arquivos a criar:**
- `app/Http/Controllers/PublicTrackingController.php`
- `resources/views/public/tracking.blade.php`
- `resources/views/public/tracking-result.blade.php`

**Funcionalidades:**
- Página pública de rastreamento
- Busca por código de rastreamento
- Histórico de status da carga
- Mapa com localização (opcional)
- Compartilhamento de link de rastreamento
- Integração com API existente

**Tempo estimado**: 1 dia

---

#### 11. **Gestão de Filiais (Branches) - CRUD Completo**
**Status**: Model existe, falta Controller e Views

**Arquivos a criar:**
- `app/Http/Controllers/BranchController.php`
- `resources/views/branches/index.blade.php`
- `resources/views/branches/create.blade.php`
- `resources/views/branches/edit.blade.php`
- `resources/views/branches/show.blade.php`

**Funcionalidades:**
- Listagem de filiais
- Cadastro completo (nome, endereço, contato)
- Edição e exclusão
- Associação com rotas
- Rotas em `routes/web.php`

**Tempo estimado**: 1 dia

---

#### 12. **Sistema de Configurações Avançadas**
**Status**: View básica existe, falta funcionalidades

**Arquivos a modificar:**
- `app/Http/Controllers/SettingsController.php`
- `resources/views/settings/index.blade.php`

**Funcionalidades:**
- Configurações da empresa
- Configurações de integração (Mitt, Asaas, WhatsApp)
- Configurações de notificações
- Configurações de faturamento (dias de vencimento padrão)
- Configurações de frete (tabela padrão)
- Backup e exportação de dados

**Tempo estimado**: 2 dias

---

### 🔵 PRIORIDADE BAIXA (Futuro)

#### 13. **Super Admin Panel**
**Status**: Não implementado

**Arquivos a criar:**
- `app/Http/Controllers/SuperAdmin/DashboardController.php`
- `app/Http/Controllers/SuperAdmin/TenantController.php`
- `app/Http/Controllers/SuperAdmin/PlanController.php`
- `resources/views/superadmin/dashboard.blade.php`
- `resources/views/superadmin/tenants/*.blade.php`
- `resources/views/superadmin/plans/*.blade.php`

**Funcionalidades:**
- Dashboard com métricas globais (nº de tenants, MRR, etc.)
- Gerenciamento de tenants (ativar/desativar, ver detalhes)
- Gerenciamento de planos
- Relatórios globais
- Middleware de autorização para super admin

**Tempo estimado**: 3-4 dias

---

#### 14. **API REST Completa para Integrações**
**Status**: API básica existe, falta documentação e endpoints completos

**Arquivos a criar:**
- `app/Http/Controllers/Api/ShipmentController.php`
- `app/Http/Controllers/Api/ClientController.php`
- `app/Http/Controllers/Api/InvoiceController.php`
- `app/Http/Resources/ShipmentResource.php`
- `app/Http/Resources/ClientResource.php`
- `app/Http/Resources/InvoiceResource.php`
- Documentação da API (Swagger/OpenAPI)

**Funcionalidades:**
- Endpoints REST completos para todos os recursos
- Autenticação via Sanctum
- Rate limiting
- Versionamento de API
- Documentação interativa
- Testes de API

**Tempo estimado**: 4-5 dias

---

#### 15. **Sistema de Chat/Comentários em Cargas**
**Status**: Não implementado

**Funcionalidades:**
- Comentários em cargas
- Notificações de comentários
- Histórico de comunicação
- Upload de arquivos em comentários

**Tempo estimado**: 2 dias

---

#### 16. **Integração com GPS/Mapas**
**Status**: Não implementado

**Funcionalidades:**
- Visualização de rotas no mapa
- Rastreamento em tempo real
- Otimização de rotas
- Integração com Google Maps/Mapbox

**Tempo estimado**: 3-4 dias

---

## 📋 PLANO DE EXECUÇÃO POR FASES

### **FASE 1: Completar Core do Negócio (Semanas 1-2)**

**Objetivo**: Ter todas as funcionalidades básicas funcionando

1. **Semana 1**:
   - ✅ CRUD de Clientes (1 dia)
   - ✅ CRUD de Motoristas (1 dia)
   - ✅ CRUD de Rotas (2 dias)
   - ✅ Interface de Emissão Fiscal (2 dias)

2. **Semana 2**:
   - ✅ Webhook Handler Completo (1 dia)
   - ✅ Dashboard com Métricas (2 dias)
   - ✅ Sistema de Notificações (2 dias)

**Resultado**: Sistema funcional com todas as operações básicas

---

### **FASE 2: Funcionalidades Avançadas (Semanas 3-4)**

**Objetivo**: Adicionar funcionalidades que melhoram a experiência do usuário

1. **Semana 3**:
   - ✅ PWA - App do Motorista (3-4 dias)

2. **Semana 4**:
   - ✅ Relatórios e Exportação (3 dias)
   - ✅ Rastreamento Público (1 dia)

**Resultado**: Sistema completo com funcionalidades avançadas

---

### **FASE 3: Melhorias e Otimizações (Semanas 5-6)**

**Objetivo**: Refinar e otimizar o sistema

1. **Semana 5**:
   - ✅ CRUD de Filiais (1 dia)
   - ✅ Configurações Avançadas (2 dias)
   - ✅ Testes e correções (2 dias)

2. **Semana 6**:
   - ✅ Super Admin Panel (3-4 dias)
   - ✅ Documentação (1 dia)

**Resultado**: Sistema robusto e bem documentado

---

## 🎯 CHECKLIST DE VALIDAÇÃO POR MÓDULO

### **Módulo Clientes**
- [ ] CRUD completo funcionando
- [ ] Múltiplos endereços por cliente
- [ ] Filtros e busca funcionando
- [ ] Associação com vendedor
- [ ] Validações de CNPJ/CPF

### **Módulo Rotas**
- [ ] CRUD completo funcionando
- [ ] Associação de Shipments funcionando
- [ ] Associação de Driver funcionando
- [ ] Botão "Emitir MDF-e" funcionando
- [ ] Status visual da rota

### **Módulo Motoristas**
- [ ] CRUD completo funcionando
- [ ] Validação de CNH
- [ ] Associação com rotas
- [ ] Status ativo/inativo

### **Módulo Fiscal**
- [ ] Botão "Emitir CT-e" funcionando
- [ ] Botão "Emitir MDF-e" funcionando
- [ ] Linha do tempo fiscal funcionando
- [ ] Webhook handler funcionando
- [ ] Notificações de autorização

### **PWA Motorista**
- [ ] Dashboard do motorista acessível
- [ ] Listagem de entregas funcionando
- [ ] Atualização de status funcionando
- [ ] Upload de foto funcionando
- [ ] Geolocalização funcionando
- [ ] PWA instalável

---

## 📝 NOTAS IMPORTANTES

1. **Priorização**: Seguir a ordem de prioridades (Crítica → Alta → Média → Baixa)
2. **Testes**: Criar testes básicos para funcionalidades críticas
3. **Documentação**: Documentar APIs e funcionalidades complexas
4. **Segurança**: Sempre validar tenant_id e permissões
5. **Performance**: Usar eager loading em relacionamentos
6. **Logs**: Logar ações importantes para debug
7. **Views**: Seguir identidade visual do index.html e pracas.html
8. **Multi-tenant**: Garantir isolamento de dados em todas as funcionalidades

---

## 🚀 PRÓXIMOS PASSOS IMEDIATOS

1. **Criar CRUD de Clientes** (Prioridade Crítica #1)
2. **Criar CRUD de Rotas** (Prioridade Crítica #2)
3. **Criar CRUD de Motoristas** (Prioridade Crítica #3)
4. **Implementar Interface de Emissão Fiscal** (Prioridade Crítica #4)
5. **Completar Webhook Handler** (Prioridade Crítica #5)

---

**Status**: Plano criado e pronto para execução  
**Última atualização**: 04/11/2025

















