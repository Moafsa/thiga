Guia Mestre de Desenvolvimento com IA (Cursor)

Projeto: Plataforma de Gestão de Transportes (TMS) - Modelo SaaS

Este documento serve como um guia completo e passo a passo para o desenvolvimento de um sistema TMS multi-tenant usando Laravel, PostgreSQL e Tailwind CSS.

Fase 0: Fundação e Estrutura do Projeto

Objetivo: Criar a base da aplicação, garantindo que a arquitetura multi-tenant, a segurança e o ambiente de desenvolvimento estejam configurados corretamente.

Passo 0.1: Configuração Inicial do Projeto

Prompt para IA: "Inicie um novo projeto Laravel chamado tms_saas. Configure o Laravel Sail para usar PostgreSQL. No arquivo .env, ajuste as credenciais do banco de dados (DB_CONNECTION=pgsql, DB_HOST=pgsql, etc.). Instale o Laravel Breeze com a stack Livewire (php artisan breeze:install livewire)."

Passo 0.2: Implementação da Arquitetura Multi-Tenant

Lógica: Cada transportadora (Tenant) terá seus dados (cargas, clientes, finanças) completamente isolados. Usaremos uma abordagem de banco de dados único com um tenant_id em cada tabela relevante para garantir o isolamento.

Prompt para IA: "Instale e configure o pacote spatie/laravel-multitenancy. Crie o model e a migration para Tenant com os campos: name, cnpj, domain (para futuro subdomínio). Adapte o model User e todas as futuras models de negócio para pertencer a um Tenant. Implemente a lógica para que, após o login, o Tenant correto seja identificado e definido para todas as consultas subsequentes."

Passo 0.3: Sistema de Planos e Assinaturas (Billing com Asaas)

Lógica: A plataforma será monetizada através de planos de assinatura. Utilizaremos a API do Asaas para gerenciar clientes e cobranças recorrentes, garantindo um processo de pagamento nacional e eficiente.

Prompt para IA: 1. "Crie um model Plan com os campos: name (ex: Básico, Profissional), price, e features (JSON).
2. Crie uma classe de serviço AsaasService.php para encapsular a comunicação com a API do Asaas. Instale o Guzzle HTTP Client. Implemente os métodos iniciais: createCustomer(Tenant $tenant), createSubscription(string $customerId, Plan $plan), e um método para gerar links de pagamento de assinatura.
3. Na página de registro do sistema, o usuário deverá escolher um plano. Após criar a conta do Tenant, o sistema chamará o AsaasService para criar um cliente no Asaas e, em seguida, gerar uma cobrança ou assinatura. Armazene o customerId do Asaas no seu model de Tenant.
4. Crie um endpoint de webhook (/webhooks/asaas) para receber e processar as atualizações de status de pagamento enviadas pelo Asaas (ex: PAYMENT_CONFIRMED). Implemente a lógica para ativar o acesso do Tenant ao sistema assim que o primeiro pagamento for confirmado."

Passo 0.4: Layout Principal e Níveis de Usuário

Lógica: Precisamos de um layout base para a área logada e um sistema robusto de permissões.

Prompt para IA: "Instale o pacote spatie/laravel-permission. Crie roles iniciais: 'Admin Tenant', 'Financeiro', 'Operacional', 'Vendedor'. Crie o layout principal da aplicação (layouts/app.blade.php) com uma barra de navegação lateral (sidebar) que exiba os menus de acordo com a permissão do usuário logado."

Fase 1: MVP - Módulos Essenciais do Tenant

Objetivo: Desenvolver o núcleo de funcionalidades para que uma transportadora possa gerenciar suas operações diárias.

Módulo 1: Onboarding e Configurações da Transportadora

Páginas: /settings/company, /settings/branches, /settings/users

Lógica: O admin do tenant deve poder configurar sua matriz, filiais e convidar usuários para sua equipe.

Prompt para IA: "Crie um componente Livewire CompanySettings. Ele deve permitir ao admin do tenant editar os dados da sua empresa (Razão Social, CNPJ, Endereço, IE). Em seguida, crie um CRUD completo para Branches (Filiais), onde o admin pode adicionar/editar as filiais associadas ao seu Tenant. Por fim, crie uma tela de gerenciamento de Users, onde o admin pode convidar novos usuários via e-mail e atribuir-lhes uma role."

Módulo 2: CRM (Clientes e Vendedores)

Páginas: /clients, /salespeople

Lógica: Cadastro de clientes (embarcadores) e da equipe de vendas.

Prompt para IA: "Crie um CRUD completo para Clients (Clientes). O formulário de cliente deve conter dados fiscais, contatos e a capacidade de adicionar múltiplos endereços (coleta/entrega). Em seguida, crie um CRUD para Salespeople (Vendedores), associando cada vendedor a um usuário do sistema. Um cliente deve poder ser associado a um vendedor."

Módulo 3: Operacional - Coletas e Entregas

Páginas: /shipments (listagem), /shipments/create

Lógica: O coração do sistema. Registrar uma ordem de transporte, desde os dados da carga até o cálculo do frete.

Prompt para IA:

"Crie o model e a migration para Shipment (Carga/Transporte) com campos detalhados: tenant_id, sender_client_id (remetente), recipient_name, recipient_address (destinatário), status, invoice_details (JSON com dados da NFe da mercadoria), weight, dimensions, goods_value, freight_value (valor do frete).

Crie um componente Livewire CreateShipment. O formulário deve ser um wizard de 3 passos:

Passo 1: Selecionar Remetente (busca na base de clientes) e preencher dados do Destinatário.

Passo 2: Inserir dados da mercadoria (peso, volume, valor, chave da NFe).

Passo 3: Calcular o Frete. Crie uma tela /freight-tables onde o tenant cadastra suas tabelas de frete (por faixa de CEP, peso, etc.). O formulário deve usar essas tabelas para calcular o freight_value automaticamente.

A listagem de Shipments deve ser uma tabela rica, com filtros por status, cliente e data."

Módulo 4: Integração Fiscal (Sistema Mitt)

Lógica: Automatizar a emissão de documentos fiscais. Criaremos uma camada de serviço (Service Layer) para abstrair a comunicação com a API do Mitt.

Prompt para IA:

"Crie uma classe de serviço MittService.php. Dentro desta classe, crie métodos placeholders: issueCte(Shipment $shipment), issueMdfe(array $shipments), cancelCte($cteId), getSpedData(Carbon $startDate, Carbon $endDate).

Na página de detalhes de um Shipment, adicione um botão 'Emitir CT-e'. Ao ser clicado, este botão deve chamar o método issueCte do MittService, passando os dados do shipment. A resposta (sucesso/erro, ID do CT-e) deve ser armazenada no Shipment e exibida na tela.

Nota: Explique no código que a lógica interna desses métodos dependerá da documentação da API do Mitt, que será fornecida pelo cliente."

Fase 2: Gestão Financeira Completa

Objetivo: Dar ao tenant uma visão clara e completa da saúde financeira da sua operação.

Módulo 5: Faturamento e Contas a Receber

Páginas: /invoicing, /accounts/receivable

Lógica: Gerar faturas para clientes agrupando múltiplos fretes e controlar os recebimentos.

Prompt para IA:

"Crie um componente InvoicingTool. O usuário seleciona um cliente e um período. O sistema lista todos os Shipments (CT-es emitidos) daquele cliente que ainda não foram faturados. O usuário seleciona quais incluir e clica em 'Gerar Fatura'.

Crie os models Invoice e InvoiceItem. A fatura gerada deve ter um status (Aberta, Paga, Vencida) e uma data de vencimento.

Crie a página /accounts/receivable, uma listagem de todas as faturas geradas, com filtros por status. Adicione funcionalidades para registrar um pagamento (baixa manual) e visualizar faturas vencidas."

Módulo 6: Contas a Pagar e Fluxo de Caixa

Páginas: /accounts/payable, /cash-flow

Lógica: Registrar despesas e visualizar um extrato financeiro completo.

Prompt para IA:

"Crie um CRUD completo para Expense (Despesa). As despesas devem ter categorias (Combustível, Salários, Manutenção, etc.), data de vencimento e status (A Pagar, Pago).

Crie a página /cash-flow. Ela deve exibir uma tabela no estilo de um extrato bancário, listando todas as transações (recebimento de faturas e pagamento de despesas) em ordem cronológica. Adicione filtros por data e um saldo consolidado."

Fase 3: Automação e Inteligência

Objetivo: Adicionar camadas de automação para otimizar a comunicação e a operação.

Módulo 7: Gestão de Rotas e App do Motorista

Páginas: /routes, (PWA) /driver/dashboard

Lógica: Agrupar entregas em rotas otimizadas e fornecer uma ferramenta para o motorista em campo.

Prompt para IA:

"Crie um CRUD para Route (Rota). Uma rota agrupa múltiplos Shipments e é associada a um Driver (crie o CRUD de motoristas) e um Vehicle.

Na página da rota, adicione um botão 'Emitir MDF-e', que chamará o método issueMdfe do MittService.

Crie uma área separada (PWA - Progressive Web App) para motoristas. Após o login, o motorista verá um DriverDashboard com a lista de entregas da sua rota atual. Para cada entrega, ele poderá atualizar o status (Ex: 'Entregue', 'Ocorrência') e, opcionalmente, capturar uma foto do comprovante."

Módulo 8: IA para Atendimento via WhatsApp

Lógica: Usar uma IA para responder a perguntas comuns de clientes no WhatsApp, como o status de uma entrega. O Laravel proverá uma API para a ferramenta de automação.

Prompt para IA:

"Crie um endpoint de API seguro: POST /api/v1/track-shipment. Ele receberá um tracking_code ou invoice_number. O endpoint deve buscar o Shipment correspondente e retornar seu último status em formato JSON.

Nota: Explique que este endpoint será consumido por uma plataforma de automação (como n8n ou outra) que conectará o WhatsApp a um modelo de IA (como GPT). O fluxo será: Cliente envia mensagem -> Plataforma de Automação -> IA interpreta o pedido -> IA chama nossa API -> IA formata a resposta e envia ao cliente."

Fase 4: Painel Super Admin

Objetivo: Criar a área de gerenciamento da plataforma para o dono do SaaS.

Módulo 9: Gestão de Tenants e Planos

Páginas: /superadmin/dashboard, /superadmin/tenants, /superadmin/plans

Lógica: Um painel para administrar todos os clientes (tenants) da plataforma.

Prompt para IA: "Crie uma área de Super Admin com um middleware de autorização. O dashboard deve mostrar métricas chave (nº de tenants, MRR, etc.). Crie uma tela para visualizar e gerenciar todos os Tenants (ativar/desativar, ver detalhes da assinatura). Por fim, crie um CRUD para gerenciar os Plans que são oferecidos no site."