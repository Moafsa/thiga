# Como Funciona: Sistema de Coleta e Rotas

## Fluxo Completo

### 1. Proposta é Criada
- Admin ou Vendedor cria uma proposta no sistema
- Proposta aparece para Admin, Vendedor e Cliente

### 2. Proposta é Aceita
- Cliente ou Admin aceita a proposta
- Status muda para `accepted`

### 3. Solicitar Coleta
- Quando a proposta está aceita, aparece o botão **"Solicitar Coleta"**
- Ao clicar:
  - Campo `collection_requested` na tabela `proposals` vira `true`
  - Campo `collection_requested_at` é preenchido com a data/hora
  - **Um registro é criado na tabela `available_cargo`** com:
    - `proposal_id` = ID da proposta
    - `status` = `available`
    - `tenant_id` = ID do tenant

### 4. Criar Rota com Carga Disponível

#### No Formulário de Criar Rota (`/routes/create`):

1. **Seção de Cargas Disponíveis**
   - Aparece uma seção chamada **"Cargas Disponíveis para Coleta (de Propostas)"**
   - Lista todas as cargas com `status = 'available'` do tenant
   - Cada carga mostra:
     - Número da proposta
     - Título da proposta
     - Cliente
     - Destino
     - Valor

2. **Seleção de Cargas**
   - Você pode marcar uma ou mais cargas disponíveis usando checkboxes
   - As cargas selecionadas são enviadas como `available_cargo_ids[]`

3. **Processamento ao Criar Rota**
   - Quando você cria a rota com cargas selecionadas:
     - Para cada carga selecionada:
       - Sistema busca a proposta relacionada
       - **Cria automaticamente um `Shipment`** com:
         - Dados da proposta (peso, cubagem, endereços)
         - Cliente remetente = cliente da proposta
         - Cliente destinatário = criado ou encontrado baseado no endereço de destino
         - Tracking number gerado automaticamente
         - Status = `pending`
         - Tipo = `collection`
       - **Atualiza a carga disponível**:
         - `status` = `assigned`
         - `route_id` = ID da rota criada
         - `assigned_at` = data/hora atual
     - O `Shipment` criado já fica associado à rota

### 5. Resultado
- A proposta agora tem um `Shipment` associado a uma rota
- A carga disponível mostra que foi atribuída à rota
- Na página de detalhes da proposta, aparece a informação da rota se já foi atribuída

## Tabelas Envolvidas

1. **`proposals`**
   - Armazena a proposta
   - Campos de coleta: `collection_requested`, `collection_requested_at`
   - Endereços: `origin_*`, `destination_*`

2. **`available_cargo`**
   - Armazena cargas disponíveis para rotas
   - Status: `available` → `assigned` → `collected`
   - Relaciona proposta com rota

3. **`routes`**
   - Armazena as rotas criadas

4. **`shipments`**
   - Armazena as cargas/entregas
   - Criado automaticamente a partir da proposta quando a carga é incluída em uma rota

## Vantagens

- **Automático**: Não precisa criar shipment manualmente
- **Rastreável**: Proposta → Carga Disponível → Shipment → Rota
- **Organizado**: Todas as cargas disponíveis aparecem em um só lugar no formulário de criar rota
- **Multi-tenant**: Cada tenant vê apenas suas próprias cargas disponíveis
