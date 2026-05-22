# 🚀 Guia de Onboarding para Novos Tenants - Thiga TMS

## Visão Geral do Processo

```
┌─────────────────────────────────────────────────────────────────────┐
│                      FLUXO DE ONBOARDING COMPLETO                   │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  1️⃣  Criar Conta → 2️⃣  WuzAPI → 3️⃣  Motoristas → 4️⃣  Veículos →    │
│  5️⃣  Clientes → 6️⃣  Rotas → 7️⃣  Coletas → 8️⃣  CTes/MDFes           │
│                                                                       │
│  ⏱️  Tempo estimado: 30-45 minutos                                  │
│  👤 Acesso necessário: Admin do Tenant                             │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 📋 PASSO 1: Criar Conta no Thiga

### O que você recebe:
```
┌──────────────────────────────────────────────┐
│         CRIAÇÃO DE CONTA (Thiga)              │
├──────────────────────────────────────────────┤
│                                              │
│  ✅ Tenant criado no sistema                 │
│  ✅ Plan associado (Trial/Pro/Enterprise)   │
│  ✅ Admin user criado                        │
│  ✅ Acesso ao dashboard                      │
│  ✅ Link para WuzAPI                         │
│                                              │
│  📧 Email: seu-email@empresa.com             │
│  🔑 Senha: (temporária, será alterada)      │
│  🏢 Empresa: Sua Transportadora              │
│                                              │
└──────────────────────────────────────────────┘
```

**Próximo passo:** Acessar dashboard e navegar para "Integração WuzAPI"

---

## 🔌 PASSO 2: Configurar WuzAPI

### O que é WuzAPI?
WuzAPI é o serviço que **emite CT-es e MDF-es** (documentos fiscais). Você precisa criar uma conta lá para gerar seus documentos fiscais.

### Passo a Passo:

```
┌─────────────────────────────────────────────────────────────────┐
│          CONFIGURAÇÃO DO WUZAPI (EXTERNO)                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  1. Acesse: https://wuzapi.com.br                              │
│  2. Clique em "Criar Conta"                                    │
│  3. Preencha dados da empresa:                                 │
│     • Razão Social                                             │
│     • CNPJ                                                      │
│     • Email                                                     │
│     • Telefone                                                  │
│  4. Aguarde aprovação (geralmente 1-2 horas)                  │
│  5. Receba credenciais:                                        │
│     • API KEY                                                   │
│     • API SECRET                                               │
│  6. Copie as credenciais                                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Adicionar Credenciais no Thiga:

```
┌──────────────────────────────────────────────────────────────────┐
│          ADICIONAR WUZAPI NO THIGA                                │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  🌐 Acesse: Dashboard → Configurações → Integrações             │
│                                                                  │
│  📋 Formulário:                                                  │
│     ┌─────────────────────────────────────────┐                │
│     │ WuzAPI Integration                      │                │
│     ├─────────────────────────────────────────┤                │
│     │ API Key: [______________________]       │                │
│     │ API Secret: [__________________]        │                │
│     │ Ambiente: ☐ Teste  ☑️ Produção         │                │
│     ├─────────────────────────────────────────┤                │
│     │ [TESTAR] [SALVAR]                       │                │
│     └─────────────────────────────────────────┘                │
│                                                                  │
│  ✅ Status: Conectado                                          │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

**O que acontece agora:**
- ✅ Thiga pode emitir CT-es e MDF-es automaticamente
- ✅ Documentos são transmitidos para Sefaz
- ✅ Status é atualizado em tempo real

**Próximo passo:** Cadastrar motoristas

---

## 👨‍✈️ PASSO 3: Cadastrar Motoristas

### Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────┐
│                     MOTORISTA (Driver)                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  👤 Informações Pessoais                                        │
│     • Nome completo                                            │
│     • CPF                                                       │
│     • CNH (Número)                                             │
│     • Data de validade CNH                                     │
│     • Telefone                                                  │
│                                                                  │
│  📍 Informações de Contato                                     │
│     • Email                                                     │
│     • Endereço                                                  │
│     • Referência de contato                                    │
│                                                                  │
│  🚗 Veículos Associados (próxima etapa)                        │
│     • Um motorista pode dirigir múltiplos veículos             │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Passo a Passo no Thiga:

```
📍 Menu: Gerenciamento → Motoristas → Novo Motorista

┌─────────────────────────────────────────────────────────────┐
│  NOVO MOTORISTA                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Nome Completo:     [__________________________]            │
│  CPF:               [__________________________]            │
│  Email:             [__________________________]            │
│  Telefone:          [__________________________]            │
│                                                             │
│  CNH:               [__________________________]            │
│  Validade:          [  /  /    ] (DD/MM/AAAA)             │
│                                                             │
│  Endereço:          [__________________________]            │
│  Status:            ☑️ Ativo   ☐ Inativo                   │
│                                                             │
│  [SALVAR]  [CANCELAR]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

✅ Motorista criado!
```

### Exemplo Real:

```
Motorista 1: João Silva
├─ CPF: 123.456.789-00
├─ CNH: 9876543210 (válida até 2028)
├─ Email: joao.silva@empresa.com
└─ Veículos: Volvo FH16 (PRF-1A23)

Motorista 2: Maria Santos
├─ CPF: 987.654.321-00
├─ CNH: 1234567890 (válida até 2027)
├─ Email: maria.santos@empresa.com
└─ Veículos: Scania R440 (PRF-4B56)
```

**Próximo passo:** Cadastrar veículos

---

## 🚛 PASSO 4: Cadastrar Veículos

### Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────┐
│                      VEÍCULO (Vehicle)                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  🚗 Identificação                                              │
│     • Placa (ex: ABC-1234)                                    │
│     • RENAVAM                                                  │
│     • Marca (Volvo, Scania, etc)                              │
│     • Modelo (FH16, R440, etc)                                │
│     • Ano de fabricação                                        │
│                                                                  │
│  📏 Dimensões & Capacidade                                    │
│     • Peso máximo bruto                                        │
│     • Peso tara (vazio)                                        │
│     • Comprimento, largura, altura                            │
│     • Volume de carga (m³)                                     │
│                                                                  │
│  🏷️ Especificações                                             │
│     • Tipo de veículo (Caminhão, Van, etc)                    │
│     • Combustível (Diesel, Gasolina, GNV)                     │
│     • Motorista(s) habilitado(s)                              │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Passo a Passo no Thiga:

```
📍 Menu: Gerenciamento → Veículos → Novo Veículo

┌─────────────────────────────────────────────────────────────┐
│  NOVO VEÍCULO                                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  IDENTIFICAÇÃO:                                             │
│  Placa:             [ABC-1234]                              │
│  RENAVAM:           [__________________________]            │
│  Marca:             [Volvo ▼]                               │
│  Modelo:            [FH16 ▼]                                │
│  Ano:               [2020]                                   │
│                                                             │
│  DIMENSÕES:                                                 │
│  Peso Bruto Máximo: [25000] kg                              │
│  Peso Tara:         [8000] kg                                │
│  Comprimento:       [15] m                                   │
│  Altura:            [4] m                                    │
│  Volume:            [80] m³                                  │
│                                                             │
│  MOTORISTA PRINCIPAL:                                       │
│  Motorista:         [João Silva ▼]                          │
│                                                             │
│  Status:            ☑️ Ativo   ☐ Inativo                    │
│                                                             │
│  [SALVAR]  [CANCELAR]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

✅ Veículo criado!
```

### Exemplo Real:

```
Veículo 1: Volvo FH16
├─ Placa: PRF-1A23
├─ RENAVAM: 12345678901234
├─ Peso Bruto: 25.000 kg
├─ Peso Tara: 8.000 kg
├─ Volume: 80 m³
├─ Motorista: João Silva
└─ Status: Ativo ✅

Veículo 2: Scania R440
├─ Placa: PRF-4B56
├─ RENAVAM: 98765432109876
├─ Peso Bruto: 23.000 kg
├─ Peso Tara: 7.500 kg
├─ Volume: 75 m³
├─ Motorista: Maria Santos
└─ Status: Ativo ✅
```

**Próximo passo:** Cadastrar clientes

---

## 🏢 PASSO 5: Cadastrar Clientes

### O que é um Cliente?
Um cliente é uma **empresa ou pessoa** que **envia ou recebe** mercadorias. Pode ser:
- **Remetente** (quem manda)
- **Destinatário** (quem recebe)
- **Payer** (quem paga pelo frete)

### Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────┐
│                      CLIENTE (Client)                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  🏢 Informações Básicas                                         │
│     • Razão Social ou Nome                                     │
│     • CNPJ ou CPF                                              │
│     • Email                                                     │
│     • Telefone                                                  │
│                                                                  │
│  📍 Endereço                                                    │
│     • Rua, número                                              │
│     • Bairro, cidade                                           │
│     • CEP                                                       │
│     • Complemento                                              │
│                                                                  │
│  🏷️ Classificação                                               │
│     • Tipo: Pessoa Jurídica / Pessoa Física                   │
│     • Categoria: Fornecedor / Cliente / Ambos                 │
│                                                                  │
│  💳 Informações Bancárias (opcional)                           │
│     • Banco, agência, conta                                    │
│     • Para pagamentos automáticos                              │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Passo a Passo no Thiga:

```
📍 Menu: Gerenciamento → Clientes → Novo Cliente

┌─────────────────────────────────────────────────────────────┐
│  NOVO CLIENTE                                               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  TIPO:              ☑️ Jurídica   ☐ Física                  │
│  Razão Social:      [Empresa Exemplo Ltda]                  │
│  CNPJ:              [12.345.678/0001-99]                    │
│  Email:             [contato@empresa.com]                   │
│  Telefone:          [(11) 9999-9999]                        │
│                                                             │
│  ENDEREÇO:                                                  │
│  Rua:               [Av. Paulista, 1000]                    │
│  Bairro:            [Bela Vista]                            │
│  Cidade:            [São Paulo]                             │
│  Estado:            [SP ▼]                                  │
│  CEP:               [01310-100]                             │
│                                                             │
│  CATEGORIA:                                                 │
│  ☑️ Fornecedor (Remetente)                                  │
│  ☐ Cliente (Destinatário)                                   │
│  ☐ Ambos                                                     │
│                                                             │
│  Status:            ☑️ Ativo   ☐ Inativo                    │
│                                                             │
│  [SALVAR]  [CANCELAR]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

✅ Cliente criado!
```

### Exemplo Real:

```
Cliente 1: ABC Distribuição
├─ CNPJ: 12.345.678/0001-99
├─ Endereço: Av. Paulista, 1000 - São Paulo/SP
├─ Email: vendas@abc.com
├─ Telefone: (11) 9999-9999
├─ Tipo: Jurídica
└─ Categoria: Fornecedor (Remetente) ✅

Cliente 2: XYZ Varejo
├─ CNPJ: 98.765.432/0001-00
├─ Endereço: Rua Central, 500 - Rio de Janeiro/RJ
├─ Email: recebimento@xyz.com
├─ Telefone: (21) 8888-8888
├─ Tipo: Jurídica
└─ Categoria: Cliente (Destinatário) ✅
```

**Próximo passo:** Criar rotas

---

## 🗺️ PASSO 6: Criar Rotas

### O que é uma Rota?
Uma rota é um **trajeto planejado** que um veículo fará, visitando múltiplos pontos de coleta e entrega.

### Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────┐
│                    ROTA (Route)                                  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  📍 Informações Básicas                                         │
│     • Nome/Número da rota                                      │
│     • Data da rota                                             │
│     • Status (Planejada, Em progresso, Concluída)             │
│                                                                  │
│  🚛 Veículo & Motorista                                        │
│     • Veículo designado                                        │
│     • Motorista(s)                                             │
│                                                                  │
│  📦 Paradas (múltiplas)                                        │
│     Para cada parada:                                          │
│     • Sequência (1º, 2º, 3º...)                              │
│     • Tipo: Coleta ou Entrega                                 │
│     • Cliente                                                  │
│     • Endereço                                                 │
│     • Janela de tempo                                          │
│     • Documentos (CT-es ou MDFes)                             │
│                                                                  │
│  📊 Resumo da Rota                                             │
│     • Peso total                                               │
│     • Volume total                                             │
│     • Número de coletas                                        │
│     • Número de entregas                                       │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Passo a Passo no Thiga:

```
📍 Menu: Operações → Rotas → Nova Rota

┌─────────────────────────────────────────────────────────────┐
│  NOVA ROTA                                                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  Nome/Número:       [ROTA-001-2024]                         │
│  Data:              [21/05/2024]                            │
│  Veículo:           [PRF-1A23 (Volvo FH16) ▼]              │
│  Motorista:         [João Silva ▼]                          │
│                                                             │
│  ┌───────────────────────────────────────────────────┐     │
│  │ PARADAS (Clique para adicionar)                   │     │
│  ├───────────────────────────────────────────────────┤     │
│  │ Parada 1: COLETA - ABC Distribuição              │     │
│  │  └─ Av. Paulista, 1000 - São Paulo/SP           │     │
│  │  └─ Horário: 08:00-09:00                         │     │
│  │                                                   │     │
│  │ Parada 2: ENTREGA - XYZ Varejo                   │     │
│  │  └─ Rua Central, 500 - Rio de Janeiro/RJ        │     │
│  │  └─ Horário: 14:00-15:00                         │     │
│  │                                                   │     │
│  │ Parada 3: ENTREGA - Varejo Metropolitano         │     │
│  │  └─ Rua das Flores, 200 - Duque de Caxias/RJ   │     │
│  │  └─ Horário: 16:30-17:30                         │     │
│  └───────────────────────────────────────────────────┘     │
│                                                             │
│  [+ ADICIONAR PARADA]                                       │
│                                                             │
│  RESUMO:                                                    │
│  Peso Total: 18.500 kg  |  Volume: 65 m³                   │
│  Coletas: 1  |  Entregas: 2  |  Distância: 450 km          │
│                                                             │
│  Status: ☑️ Planejada   ☐ Em Progresso   ☐ Concluída       │
│                                                             │
│  [SALVAR] [CANCELAR]                                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘

✅ Rota criada!
```

### Exemplo Real:

```
ROTA-001-2024: São Paulo → Rio de Janeiro

🚛 Veículo: PRF-1A23 (Volvo FH16)
👤 Motorista: João Silva
📅 Data: 21/05/2024

Parada 1: COLETA
├─ Empresa: ABC Distribuição
├─ Endereço: Av. Paulista, 1000 - São Paulo/SP
├─ Horário: 08:00-09:00
└─ CTes: 3 documentos (500 kg cada = 1.500 kg)

Parada 2: ENTREGA
├─ Empresa: XYZ Varejo
├─ Endereço: Rua Central, 500 - Rio de Janeiro/RJ
├─ Horário: 14:00-15:00
└─ Documentos: CT-001, CT-002 (2.000 kg total)

Parada 3: ENTREGA
├─ Empresa: Varejo Metropolitano
├─ Endereço: Rua das Flores, 200 - Duque de Caxias/RJ
├─ Horário: 16:30-17:30
└─ Documentos: CT-003 (500 kg)

📊 RESUMO:
Total: 4.000 kg | Volume: 65 m³ | Distância: 450 km
```

**Próximo passo:** Cadastrar coletas

---

## 📦 PASSO 7: Cadastrar Coletas (Shipments)

### O que é uma Coleta?
Uma coleta é um **carregamento de mercadoria** que será transportado de um **remetente** para um **destinatário**.

### Estrutura de Dados:

```
┌─────────────────────────────────────────────────────────────────┐
│                 COLETA/ENVIO (Shipment)                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  📋 Informações Básicas                                         │
│     • Número da coleta (única)                                 │
│     • Data de coleta                                           │
│     • Status (Pendente, Coletada, Entregue, Devolvida)        │
│                                                                  │
│  👥 Partes Envolvidas                                           │
│     • Remetente (quem manda)                                   │
│     • Destinatário (quem recebe)                               │
│     • Payer (quem paga o frete)                                │
│                                                                  │
│  📦 Mercadorias                                                 │
│     Para cada item:                                            │
│     • Descrição                                                │
│     • Quantidade                                               │
│     • Peso                                                      │
│     • Volume                                                    │
│     • Natureza (carga perigosa? Refrigerada?)                 │
│                                                                  │
│  💰 Valores                                                     │
│     • Peso total                                               │
│     • Volume total                                             │
│     • Valor da mercadoria                                      │
│     • Valor do frete                                           │
│     • Valor total                                              │
│                                                                  │
│  🗺️ Rota                                                        │
│     • Rota designada (opcional no cadastro)                   │
│     • Será atribuída após planejamento                         │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Passo a Passo no Thiga:

```
📍 Menu: Operações → Coletas → Nova Coleta

┌─────────────────────────────────────────────────────────────┐
│  NOVA COLETA                                                │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  IDENTIFICAÇÃO:                                             │
│  Número Coleta:    [COLETA-2024-001]                        │
│  Data Coleta:      [21/05/2024]                             │
│                                                             │
│  PARTES ENVOLVIDAS:                                         │
│  Remetente:        [ABC Distribuição ▼]                    │
│  Destinatário:     [XYZ Varejo ▼]                           │
│  Payer:            [ABC Distribuição ▼]                    │
│                                                             │
│  MERCADORIAS:                                               │
│  ┌─────────────────────────────────────────────────┐       │
│  │ Item 1: Camisetas (Azul) - 100 unidades        │       │
│  │  └─ Peso: 50 kg  |  Volume: 0,5 m³             │       │
│  │                                                  │       │
│  │ Item 2: Camisetas (Vermelho) - 150 unidades    │       │
│  │  └─ Peso: 75 kg  |  Volume: 0,75 m³            │       │
│  │                                                  │       │
│  │ Item 3: Camisetas (Preto) - 80 unidades        │       │
│  │  └─ Peso: 40 kg  |  Volume: 0,4 m³             │       │
│  └─────────────────────────────────────────────────┘       │
│  [+ ADICIONAR ITEM]                                         │
│                                                             │
│  TOTAIS:                                                    │
│  Peso Total:       [165 kg]                                 │
│  Volume Total:     [1,65 m³]                                │
│  Qtd Total Itens:  [330 unidades]                           │
│                                                             │
│  VALORES:                                                   │
│  Valor Mercadoria: [R$ 33.000,00]                           │
│  Valor Frete:      [R$ 825,00]                              │
│  Valor Total:      [R$ 33.825,00]                           │
│                                                             │
│  Status:           ☑️ Pendente   ☐ Coletada                │
│                                                             │
│  [SALVAR]  [CANCELAR]                                       │
│                                                             │
└─────────────────────────────────────────────────────────────┘

✅ Coleta criada!
```

### Exemplo Real:

```
COLETA-2024-001: ABC → XYZ

📍 REMETENTE: ABC Distribuição
   └─ Av. Paulista, 1000 - São Paulo/SP

📍 DESTINATÁRIO: XYZ Varejo
   └─ Rua Central, 500 - Rio de Janeiro/RJ

📦 MERCADORIAS:
   • Camisetas Azuis - 100 un - 50 kg
   • Camisetas Vermelhas - 150 un - 75 kg
   • Camisetas Pretas - 80 un - 40 kg

📊 TOTAIS:
   • Peso: 165 kg
   • Volume: 1,65 m³
   • Quantidade: 330 unidades

💰 VALORES:
   • Mercadoria: R$ 33.000,00
   • Frete: R$ 825,00
   • Total: R$ 33.825,00

🚛 ROTA: (será definida após planejamento)
   Atribuída à: ROTA-001-2024
```

**Próximo passo:** Gerar CT-e

---

## 📄 PASSO 8: Gerar CT-es (Conhecimento de Transporte Eletrônico)

### O que é um CT-e?
CT-e é um documento fiscal **eletrônico** emitido pela Sefaz (governo) que **comprova o transporte** de uma mercadoria.

### Fluxo Automático:

```
┌────────────────────────────────────────────────────────────┐
│             GERAÇÃO AUTOMÁTICA DE CT-E                      │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  1. Coleta criada e atribuída a uma rota                 │
│         ↓                                                  │
│  2. Sistema verifica: rota tem vehicle + motorista?       │
│         ↓                                                  │
│  3. SIM → Gera CT-e automaticamente                       │
│         ↓                                                  │
│  4. CT-e é enviado para WuzAPI                            │
│         ↓                                                  │
│  5. WuzAPI envia para Sefaz                               │
│         ↓                                                  │
│  6. Sefaz valida e aprova (ou rejeita)                   │
│         ↓                                                  │
│  7. Status é atualizado no Thiga                          │
│         ↓                                                  │
│  ✅ CT-e gerado e autorizado!                            │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

### Status do CT-e:

```
CT-e passa por vários status:

🟡 PENDENTE
   └─ Aguardando ser enviado para Sefaz

🔵 VALIDANDO
   └─ Validação de dados em andamento

🟣 PROCESSANDO
   └─ Enviado para Sefaz, aguardando resposta

🟢 AUTORIZADO ✅
   └─ Aprovado pela Sefaz
   └─ Válido para uso

🔴 REJEITADO ❌
   └─ Sefaz rejeitou
   └─ Motivo: dados inválidos ou erro

⚪ CANCELADO
   └─ CT-e cancelado após aprovação

🔴 ERRO
   └─ Erro de sistema/API
   └─ Requer ação manual
```

### Visualizar CT-es:

```
📍 Menu: Fiscal → Documentos Fiscais

┌──────────────────────────────────────────────────────────┐
│ DOCUMENTOS FISCAIS (CT-es e MDF-es)                      │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ Filtros: [Tipo ▼] [Status ▼] [Data ▼] [Buscar]        │
│                                                          │
│ ┌──────────────────────────────────────────────────────┐ │
│ │ Tipo │ Número │ Access Key    │ Status │ Data       │ │
│ ├──────────────────────────────────────────────────────┤ │
│ │ CT-e │ 001    │ 123456...     │ 🟢 AUTORIZADO      │ │
│ │      │        │               │  (21/05/24)        │ │
│ ├──────────────────────────────────────────────────────┤ │
│ │ CT-e │ 002    │ 789012...     │ 🟡 PENDENTE        │ │
│ │      │        │               │  (21/05/24)        │ │
│ ├──────────────────────────────────────────────────────┤ │
│ │ CT-e │ 003    │ 345678...     │ 🔴 REJEITADO       │ │
│ │      │        │               │  (21/05/24)        │ │
│ └──────────────────────────────────────────────────────┘ │
│                                                          │
│ Ações: [👁️ Ver] [📄 PDF] [📝 XML] [❌ Cancelar]          │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Detalhes do CT-e:

```
┌──────────────────────────────────────────────────────────┐
│ CT-e #001 - DETALHES                                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ 📋 DOCUMENTO                                             │
│    Número: 001                                          │
│    Chave de Acesso: 12345678901234567890123456789012   │
│    Status: 🟢 AUTORIZADO                                │
│    Emitido em: 21/05/2024 às 10:30                     │
│    Autorizado em: 21/05/2024 às 10:32                  │
│                                                          │
│ 🚛 TRANSPORTE                                           │
│    Veículo: PRF-1A23 (Volvo FH16)                       │
│    Motorista: João Silva (CPF: 123.456.789-00)         │
│    Placa: PRF-1A23  |  RENAVAM: 12345678901234         │
│                                                          │
│ 👥 PARTES                                               │
│    Remetente: ABC Distribuição (CNPJ: 12.345.678/0001) │
│    Destinatário: XYZ Varejo (CNPJ: 98.765.432/0001)    │
│                                                          │
│ 📦 CARGA                                                │
│    Descrição: Camisetas variadas                        │
│    Peso: 165 kg  |  Volume: 1,65 m³  |  Qtd: 330 un   │
│                                                          │
│ 💰 VALORES                                              │
│    Valor Mercadoria: R$ 33.000,00                       │
│    Valor Frete: R$ 825,00                               │
│    Valor ICMS: R$ 0,00                                  │
│    Valor Total: R$ 33.825,00                            │
│                                                          │
│ 📥 DOWNLOAD                                             │
│    [📄 Baixar PDF] [📝 Baixar XML]                     │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## 🚚 PASSO 9: Gerar MDF-e (Manifesto de Documento Fiscal)

### O que é um MDF-e?
MDF-e é um documento que **consolida múltiplos CT-es** em uma única rota, indicando que todos estão sendo transportados juntos pelo mesmo veículo.

### Quando é Gerado?
```
Após CT-es serem criados e rota estar completa:

1. Sistema identifica todos CT-es da rota
2. Verifica se rota tem veículo e motorista
3. Consolida os CT-es em um MDF-e
4. Envia para WuzAPI
5. WuzAPI envia para Sefaz
6. Sefaz autoriza o MDF-e
```

### Estrutura:

```
┌──────────────────────────────────────────────────────────┐
│ MDF-e ROTA-001-2024 - DETALHES                           │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ 📋 DOCUMENTO                                             │
│    Número: 001                                          │
│    Chave de Acesso: 98765432109876543210987654321098   │
│    Status: 🟢 AUTORIZADO                                │
│                                                          │
│ 🚛 TRANSPORTE                                           │
│    Veículo: PRF-1A23 (Volvo FH16)                       │
│    Motorista: João Silva                                │
│    Data: 21/05/2024                                     │
│                                                          │
│ 📦 CT-es CONSOLIDADOS (3)                               │
│    ├─ CT-e #001 - ABC → XYZ (165 kg)                  │
│    ├─ CT-e #002 - ABC → Varejo Met. (180 kg)          │
│    └─ CT-e #003 - Distribuidor → Loja (150 kg)        │
│                                                          │
│ 📊 TOTAIS                                               │
│    Peso: 495 kg  |  Volume: 5 m³                       │
│    Documentos: 3 CT-es  |  Valor: R$ 99.750,00         │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Visualizar MDF-es:

```
📍 Menu: Fiscal → Documentos Fiscais → Filtro: MDF-e

Similar aos CT-es, aparece na mesma tela com filtro!
```

---

## 🎯 RESUMO DO FLUXO COMPLETO

### Visão Geral Integrada:

```
┌──────────────────────────────────────────────────────────┐
│                    FLUXO COMPLETO                         │
├──────────────────────────────────────────────────────────┤
│                                                          │
│ 1. CONTA CRIADA                                          │
│    └─ Admin user + Tenant criados                        │
│                                                          │
│ 2. WUZAPI CONECTADO                                      │
│    └─ Sistema pode emitir documentos fiscais             │
│                                                          │
│ 3. MOTORISTAS CADASTRADOS                                │
│    └─ Podem ser atribuídos a rotas                      │
│                                                          │
│ 4. VEÍCULOS CADASTRADOS                                  │
│    └─ Associados a motoristas                           │
│                                                          │
│ 5. CLIENTES CRIADOS                                      │
│    └─ Remetentes e destinatários disponíveis             │
│                                                          │
│ 6. COLETAS CADASTRADAS                                   │
│    └─ Com informações de mercadoria                      │
│                                                          │
│ 7. ROTAS PLANEJADAS                                      │
│    └─ Com coletas e paradas definidas                   │
│                                                          │
│ 8. CT-es GERADOS AUTOMATICAMENTE                         │
│    └─ Um para cada coleta da rota                        │
│    └─ Enviados e autorizados pela Sefaz                │
│                                                          │
│ 9. MDF-es GERADOS AUTOMATICAMENTE                        │
│    └─ Consolidam todos CT-es da rota                    │
│    └─ Enviados e autorizados pela Sefaz                │
│                                                          │
│ ✅ OPERAÇÃO PRONTA PARA EXECUÇÃO!                       │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

---

## 📊 Diagrama de Dependências

```
DEPENDÊNCIAS ENTRE RECURSOS:

┌─────────────┐
│   Conta     │ (início)
└──────┬──────┘
       │
       ├──────────────────────────┐
       │                          │
       v                          v
   ┌─────────┐            ┌──────────┐
   │ WuzAPI  │ (integração)│Motoristas│
   └─────────┘            └────┬─────┘
       │                       │
       │                    ┌──v──┐
       │                    │Veíc.│
       │                    └─────┘
       │
       ├─────────────┐
       │             │
       v             v
   ┌────────┐   ┌────────┐
   │Clientes│   │ Coleta │
   └────────┘   └───┬────┘
                    │
                    v
                ┌────────┐
                │ Rotas  │
                └───┬────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        v                       v
    ┌──────┐              ┌────────┐
    │CT-es │ ────────→   │MDF-es  │
    └──────┘              └────────┘
       │                      │
       v                      v
  [Autorizado]          [Autorizado]
  pela Sefaz            pela Sefaz
```

---

## ✅ CHECKLIST DE ONBOARDING

Use este checklist para garantir que tudo foi feito:

```
┌─────────────────────────────────────────────────────────┐
│          CHECKLIST DE ONBOARDING COMPLETO               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ☐ 1. Conta criada no Thiga                             │
│ ☐ 2. Admin user configurado                            │
│ ☐ 3. WuzAPI cadastrado e conectado                     │
│ ☐ 4. Teste de conexão com WuzAPI bem-sucedido          │
│ ☐ 5. Pelo menos 1 motorista cadastrado                 │
│ ☐ 6. Pelo menos 1 veículo cadastrado                   │
│ ☐ 7. Motorista associado ao veículo                    │
│ ☐ 8. Pelo menos 1 cliente remetente criado             │
│ ☐ 9. Pelo menos 1 cliente destinatário criado          │
│ ☐ 10. Primeira coleta criada                           │
│ ☐ 11. Primeira rota criada com coleta                  │
│ ☐ 12. CT-e gerado e autorizado ✅                      │
│ ☐ 13. MDF-e gerado e autorizado ✅                     │
│ ☐ 14. Documentos fiscais visíveis no menu              │
│ ☐ 15. PDFs dos documentos baixáveis                    │
│                                                         │
│ 🎉 PARABÉNS! Seu tenant está 100% operacional!         │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🆘 Troubleshooting Rápido

### CT-e com Status REJEITADO?
```
❌ Possível causa: Dados incompletos ou inválidos

Verifique:
✓ Motorista tem CNH válida?
✓ Veículo tem RENAVAM válido?
✓ Cliente tem CNPJ válido?
✓ Endereços preenchidos corretamente?
✓ Mercadoria tem descrição e peso?

📞 Se persistir: Contate suporte do WuzAPI
```

### CT-e com Status PENDENTE por muito tempo?
```
⏳ Possível causa: Não foi enviado para Sefaz ainda

Solução:
1. Ir para Fiscal → Documentos Fiscais
2. Clicar no CT-e PENDENTE
3. Clicar em "Reenviar"
4. Aguardar 5-10 minutos
5. Atualizar página

Se ainda PENDENTE: Contate suporte Thiga
```

### MDF-e não está sendo gerado?
```
❓ Possível causa: CT-es não autorizado ainda

Verifique:
✓ Todos CT-es da rota estão 🟢 AUTORIZADO?
✓ Rota tem veículo designado?
✓ Rota tem motorista designado?
✓ Coletas estão na rota?

Se tudo OK: MDF-e será gerado automaticamente em poucos minutos
```

---

## 📞 Contatos de Suporte

```
┌─────────────────────────────────────────────────────────┐
│           SUPORTE E CONTATOS IMPORTANTES                │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ 🎯 THIGA SUPORTE                                        │
│    Email: suporte@thiga.com.br                          │
│    Telefone: 0800-THIGA-1                               │
│    Chat: Dentro do dashboard                           │
│                                                         │
│ 🔌 WUZAPI SUPORTE                                       │
│    Email: contato@wuzapi.com.br                         │
│    Telefone: (11) 9999-9999                             │
│    Documentação: https://docs.wuzapi.com.br             │
│                                                         │
│ 🏛️ SEFAZ (Assuntos Fiscais)                             │
│    Portal: https://www.sefaz.rs.gov.br                 │
│    Dúvidas CT-e/MDF-e                                   │
│    Email/Chat conforme estado                           │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🎓 Próximos Passos Recomendados

Depois que o onboarding básico estiver completo:

```
1. ✅ Configurar permissões de usuários
   └─ Criar usuários para motoristas, atendimento, etc
   └─ Definir papéis e acessos

2. ✅ Integrar com sistemas de contabilidade
   └─ Opcional: exportar para ERP/Sistema fiscal

3. ✅ Configurar alertas e notificações
   └─ Alertas de CT-e rejeitado
   └─ Notificação quando coleta é entregue

4. ✅ Treinar equipe
   └─ Motoristas usam app mobile
   └─ Atendimento: criar coletas
   └─ Gerente: acompanhar rotas em tempo real

5. ✅ Começar a usar relatórios
   └─ Análise de custos de frete
   └─ Performance de rotas
   └─ Documentos fiscais

6. ✅ Otimizar rotas
   └─ Usar IA para planejar rotas melhores
   └─ Reduzir custos e tempo
```

---

**Versão:** 1.0  
**Última atualização:** Maio 2024  
**Tempo de leitura:** 15-20 minutos  
**Status:** ✅ Completo e Pronto para Uso

Para suporte detalhado de qualquer etapa, consulte o suporte Thiga ou a documentação específica no dashboard!
