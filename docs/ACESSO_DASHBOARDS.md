# 📊 Guia de Acesso aos Dashboards

## 🎯 Visão Geral

O sistema possui **3 tipos de dashboards** principais:
1. **Dashboard de Cliente** - Para clientes visualizarem seus envios, propostas e faturas
2. **Dashboard de Vendedor** - Para vendedores gerenciarem clientes, propostas e comissões
3. **Dashboard Administrativo** - Para administradores gerenciarem todo o sistema

---

## 👤 Dashboard de Cliente

### 📍 URL de Acesso
```
/client/dashboard
```

### 🔐 Como Acessar

#### Método 1: Login via Telefone (WhatsApp)
1. Acesse: `/client/login/phone`
2. Informe seu número de telefone cadastrado
3. Um código de verificação será enviado via WhatsApp
4. Acesse: `/client/login/code`
5. Digite o código recebido
6. Você será redirecionado automaticamente para `/client.dashboard`

#### Método 2: Login Administrativo (se tiver acesso)
1. Acesse: `/login`
2. Faça login com email/senha
3. Se o usuário estiver vinculado a um cliente, será redirecionado para `/client/dashboard`

### ✨ Funcionalidades Disponíveis

- **📦 Envios Ativos**: Visualizar envios em andamento
- **📋 Propostas**: Ver e gerenciar propostas recebidas
- **💰 Faturas**: Consultar faturas pendentes e pagas
- **📊 Estatísticas**: 
  - Total de envios
  - Envios ativos
  - Envios entregues
  - Propostas pendentes
  - Faturas pendentes

### 🔗 Rotas Relacionadas
- `/client/shipments` - Lista de envios
- `/client/proposals` - Lista de propostas
- `/client/invoices` - Lista de faturas
- `/client/request-proposal` - Solicitar nova proposta

---

## 👔 Dashboard de Vendedor

### 📍 URL de Acesso
```
/salesperson/dashboard
```

### 🔐 Como Acessar

#### Método 1: Login via Telefone (WhatsApp)
1. Acesse: `/salesperson/login/phone`
2. Informe seu número de telefone cadastrado
3. Um código de verificação será enviado via WhatsApp
4. Acesse: `/salesperson/login/code`
5. Digite o código recebido
6. Você será redirecionado automaticamente para `/salesperson.dashboard`

#### Método 2: Login Administrativo (se tiver acesso)
1. Acesse: `/login`
2. Faça login com email/senha
3. Se o usuário estiver vinculado a um vendedor, será redirecionado para `/salesperson/dashboard`

### ✨ Funcionalidades Disponíveis

- **📊 Estatísticas de Vendas**:
  - Total de propostas
  - Propostas pendentes
  - Propostas aceitas
  - Valor total vendido
  - Comissões acumuladas
  - Comissões do período

- **👥 Meus Clientes**: Lista de clientes atribuídos
- **📋 Propostas Recentes**: Últimas propostas criadas
- **🧮 Calculadora de Frete**: Calcular valores de frete em tempo real
- **📈 Gráficos**: Visualização de propostas por status

### 🔗 Rotas Relacionadas
- `/salesperson/calculate-freight` - API para cálculo de frete (AJAX)
- `/freight-tables` - Visualizar tabelas de frete (com exportação PDF)
- `/proposals` - Gerenciar propostas
- `/clients` - Gerenciar clientes

### 🆕 Funcionalidade Especial: Exportação de Tabelas em PDF
Os vendedores podem exportar tabelas de frete em PDF para compartilhar com clientes:
- **Exportar tabela individual**: Botão "Exportar PDF" na página de detalhes
- **Exportar todas as tabelas**: Botão "Exportar Todas em PDF" na listagem

---

## 🛠️ Dashboard Administrativo

### 📍 URL de Acesso
```
/dashboard
```

### 🔐 Como Acessar
1. Acesse: `/login`
2. Faça login com email/senha de administrador
3. Você será redirecionado para `/dashboard`

### ✨ Funcionalidades Disponíveis
- Visão geral completa do sistema
- Gerenciamento de todos os recursos
- Relatórios e estatísticas gerais

---

## 🔄 Fluxo de Redirecionamento

### Após Login Bem-Sucedido:

1. **Cliente** → Redirecionado para `/client/dashboard`
2. **Vendedor** → Redirecionado para `/salesperson/dashboard`
3. **Motorista** → Redirecionado para `/driver/dashboard`
4. **Administrador** → Redirecionado para `/dashboard`

### Verificação Automática
O sistema verifica automaticamente o tipo de usuário após o login e redireciona para o dashboard apropriado.

---

## ⚠️ Requisitos para Acesso

### Dashboard de Cliente
- ✅ Usuário deve estar cadastrado como cliente
- ✅ Cliente deve estar vinculado a um tenant
- ✅ Cliente deve ter um `user_id` associado

### Dashboard de Vendedor
- ✅ Usuário deve estar cadastrado como vendedor (salesperson)
- ✅ Vendedor deve estar vinculado a um tenant
- ✅ Vendedor deve ter um `user_id` associado

---

## 🐛 Solução de Problemas

### Erro: "Usuário não possui tenant associado"
- Verifique se o usuário está vinculado a um tenant no banco de dados
- Verifique se o tenant está ativo

### Erro: "Usuário não é um vendedor cadastrado"
- Verifique se existe um registro na tabela `salespeople` vinculado ao `user_id`
- Verifique se o vendedor está ativo (`is_active = true`)

### Erro: "Você não está registrado como cliente"
- Verifique se existe um registro na tabela `clients` vinculado ao `user_id`
- Verifique se o cliente está ativo (`is_active = true`)

### Código de verificação não chega via WhatsApp
- Verifique se o número está cadastrado corretamente
- Verifique se a integração WhatsApp está configurada
- Verifique os logs do sistema para mais detalhes

---

## 📝 Notas Importantes

1. **Autenticação via WhatsApp**: O sistema usa WhatsApp para envio de códigos de verificação, garantindo segurança adicional.

2. **Multi-tenant**: Cada dashboard respeita o isolamento de dados por tenant.

3. **Permissões**: Cada tipo de usuário tem acesso apenas às funcionalidades permitidas para seu perfil.

4. **Sessão**: Após o login, a sessão é mantida até o logout ou expiração.

---

## 🔗 Links Rápidos

### Login
- Cliente: `/client/login/phone`
- Vendedor: `/salesperson/login/phone`
- Administrador: `/login`

### Dashboards
- Cliente: `/client/dashboard`
- Vendedor: `/salesperson/dashboard`
- Administrador: `/dashboard`

---

**Última atualização**: Janeiro 2025
