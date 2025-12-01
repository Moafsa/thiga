# Design – Endpoint de Orquestração MCP (`POST /api/mcp/workflows/order`)

**Data**: 10/11/2025  
**Responsável**: Agente 1 (GPT-5 Codex)  
**Status**: Implementado em 10/11/2025  
**Objetivo**: Permitir que fluxos externos (ex.: n8n + IA) acionem o TMS SaaS via uma chamada MCP/REST única, cobrindo cálculo de frete, criação/atualização de cliente, geração de proposta, criação de shipment e (opcionalmente) rota.

---

## 1. Contexto

- O MCP atual (`McpFreightController`) já calcula frete, mas o fluxo completo ainda exige múltiplas chamadas manuais para criar clientes, propostas e cargas.
- O fluxo desejado: **n8n → IA → TMS** recebe dados do frete (origem/destino, peso, valor NF) + informações de cliente; o sistema:
  1. Resolve o tenant via token.
  2. Localiza ou cria cliente e endereços.
  3. Calcula frete com `FreightCalculationService`.
  4. Gera proposta comercial.
  5. Cria shipment (status `pending`) e agenda rota opcional.
  6. Dispara eventos (notificações, WuzAPI, Mitt quando aplicável).
- Resposta deve devolver todos os IDs/links úteis para que a IA informe o cliente (tracking, link da proposta, valores detalhados).

---

## 2. Endpoint

| Método | Caminho | Autenticação |
|--------|---------|--------------|
| `POST` | `/api/mcp/workflows/order` | Header `X-Tenant-Token` ou `Authorization: Bearer <token>` com token de API do tenant |

### Headers Requeridos
```
X-Tenant-Token: <token_do_tenant>
Content-Type: application/json
Idempotency-Key: <uuid> (opcional, recomendado)
```

### Autorização
- Reutiliza `Tenant::api_token` (hash SHA-256). Token plaintext fornecido ao integrador.
- Se `Idempotency-Key` for enviado, registrar hash `${tenant_id}:${key}` na tabela `api_requests` (nova) para evitar duplicidades.

---

## 3. Payload de Requisição

```json
{
  "customer": {
    "name": "ACME LTDA",
    "document": "12.345.678/0001-99",
    "email": "contato@acme.com",
    "phone": "+5511999999999",
    "salesperson_id": 42,
    "addresses": [
      {
        "type": "pickup",
        "name": "Centro de Distribuição",
        "street": "Rua das Flores",
        "number": "123",
        "complement": "Galpão 5",
        "neighborhood": "Industrial",
        "city": "São Paulo",
        "state": "SP",
        "zip_code": "01000-000",
        "is_default": true
      }
    ]
  },
  "freight": {
    "destination": "BELO HORIZONTE - MG",
    "weight": 55.5,
    "cubage": 0.5,
    "invoice_value": 1500.0,
    "options": {
      "tde_markets": false,
      "pallets": 2,
      "is_weekend_or_holiday": false
    }
  },
  "shipment": {
    "title": "Paletes linha premium",
    "pickup": {
      "address": "Rua das Flores, 123",
      "city": "São Paulo",
      "state": "SP",
      "zip_code": "01000-000",
      "latitude": -23.55052,
      "longitude": -46.63331,
      "date": "2025-11-15",
      "time": "08:00"
    },
    "delivery": {
      "address": "Av. Afonso Pena, 1000",
      "city": "Belo Horizonte",
      "state": "MG",
      "zip_code": "30130-003",
      "latitude": -19.91668,
      "longitude": -43.93449,
      "date": "2025-11-17",
      "time": "18:00"
    },
    "items": [
      {
        "description": "Paletes premium",
        "quantity": 10,
        "weight": 5.55,
        "volume": 0.05,
        "value": 1500.0,
        "nfe_key": "35190801234567000190550010000012341000012340"
      }
    ],
    "notes": "Agendar com antecedência de 24h"
  },
  "proposal": {
    "title": "Cálculo Frete Belo Horizonte",
    "valid_until": "2025-11-30",
    "discount_percentage": 0,
    "notes": "Frete sujeito a confirmações adicionais."
  },
  "route": {
    "auto_assign": true,
    "driver_id": null,
    "scheduled_date": "2025-11-16"
  },
  "notifications": {
    "send_whatsapp": true,
    "customer_phone": "+5531999999999"
  },
  "metadata": {
    "source": "n8n-flow-abc",
    "conversation_id": "CHATGPT-XYZ"
  }
}
```

### Campos importantes
- `customer.document`: aceitar CPF/CNPJ. Busca `clients` por `cnpj` ou `tax_id` (se existir). Se vazio, usar combinação nome+email.
- `shipment.items`: se múltiplos itens, somar `quantity`, `weight` e `volume` para cálculo de frete; armazenar itens em `metadata`.
- `route.auto_assign`: se `true` e houver `driver_id`, cria/associa rota. Se `false`, gera shipment sem rota (status `pending`).
- `notifications.send_whatsapp`: se `true`, aciona job para enviar mensagem (depende do módulo Agente 2).

---

## 4. Fluxo Interno

1. **Autenticação Tenant**
   - Usar método `authenticateTenant` (similar ao `McpFreightController`).
   - Lançar HTTP 401 se token ausente/inválido.
2. **Validação**
   - Nova `FormRequest` (`OrchestrateOrderRequest`).
   - Validar datas, valores numéricos, CEP, opções booleans, arrays.
3. **Cliente**
   - Buscar por `cnpj` (campo `cnpj`). Se não encontrar e doc vazio, buscar por `email`.
   - Se não existir, criar `Client` e `ClientAddress` (usar transaction).
   - Atualizar dados básicos se cliente já existir (opt-in via `customer.allow_update` default `true`).
4. **Cálculo de Frete**
   - Invocar `FreightCalculationService::calculate`.
   - Guardar breakdown em `$calculation`.
5. **Proposta**
   - Criar `Proposal` (status `draft`), campo `base_value` = `$calculation['total']`.
   - Aplicar `discount_percentage` se informado.
   - Gerar `proposal_number` via helper (igual `ProposalController::store`).
6. **Shipment**
   - Criar `Shipment` com dados do payload + `metadata` contendo itens, `calculation`, `source`.
   - Status inicial `pending`.
   - `freight_value` armazenado em `metadata['freight_value']`.
7. **Rota (opcional)**
   - Se `route.auto_assign`:
     - Procurar rota existente `scheduled` para mesma data+driver; se existir, agregar shipment.
     - Caso contrário, criar `Route` (nome sugerido `Rota <data> <cliente>`).
8. **Eventos/Jobs**
   - Disparar evento `OrderOrchestrated` com payload completo.
   - Inscrever `WhatsApp`/`Notifications`/`Mitt` jobs (dependente dos módulos 2 e 3).
9. **Resposta**
   - Estruturar `OrchestratedOrderResource` com IDs, valores e links (rotas `route('...')`).

---

## 5. Resposta (HTTP 201)

```json
{
  "success": true,
  "data": {
    "tenant_id": 10,
    "customer": {
      "id": 221,
      "name": "ACME LTDA",
      "document": "12.345.678/0001-99",
      "link": "https://app.thiga.io/clients/221"
    },
    "proposal": {
      "id": 890,
      "number": "PROP-9F4A3C1D",
      "status": "draft",
      "base_value": 1875.35,
      "final_value": 1875.35,
      "valid_until": "2025-11-30",
      "link": "https://app.thiga.io/proposals/890"
    },
    "shipment": {
      "id": 1337,
      "tracking_number": "THG8XZ3A1P",
      "status": "pending",
      "freight_value": 1875.35,
      "pickup": {
        "address": "Rua das Flores, 123",
        "scheduled_for": "2025-11-15 08:00:00"
      },
      "delivery": {
        "address": "Av. Afonso Pena, 1000",
        "scheduled_for": "2025-11-17 18:00:00"
      },
      "link": "https://app.thiga.io/shipments/1337",
      "public_tracking_url": "https://track.thiga.io/THG8XZ3A1P"
    },
    "route": {
      "created": true,
      "id": 501,
      "name": "Rota 16/11 – ACME LTDA",
      "status": "scheduled",
      "link": "https://app.thiga.io/routes/501"
    },
    "freight_breakdown": {
      "chargeable_weight": 62.5,
      "freight_weight": 1500.0,
      "ad_valorem": 6.0,
      "gris": 15.0,
      "toll": 35.0,
      "additional_services": [],
      "minimum_applied": false
    },
    "notifications": {
      "whatsapp_enqueued": true,
      "channel_reference": "msg-20251110-abcdef"
    },
    "metadata": {
      "idempotency_key": "uuid-1234",
      "source": "n8n-flow-abc"
    }
  }
}
```

### Outros status
- `202 Accepted`: quando processamento assíncrono (ex.: criação de rota ou mensagem WhatsApp) é enfileirado.
- `422 Unprocessable Entity`: validação com erros detalhados.
- `409 Conflict`: requisição repetida com mesma `Idempotency-Key`.

---

## 6. Tratamento de Erros

| Código | Cenário | Ação |
|--------|---------|------|
| 400 | Payload malformado | Mensagem `Invalid JSON payload` |
| 401 | Token inválido/ausente | `Unauthorized` |
| 403 | Tenant inativo ou sem assinatura | `Tenant not allowed` |
| 404 | `salesperson_id` ou `driver_id` inexistente | `Related resource not found` |
| 422 | Validação | Informar campo e regra |
| 500 | Erro inesperado | Logar com `order_orchestrator` tag; resposta genérica |

**Logs**: usar `Log::channel('stack')->info('order.orchestrated', [...])` com correlation ID (`request_id`).

---

## 7. Integração com Serviços Existentes

| Serviço | Uso |
|---------|-----|
| `FreightCalculationService` | Cálculo de frete com breakdown (já pronto). |
| `AsaasService` | Sem uso direto aqui; integração financeira acontece depois (faturamento). |
| `MittService` | Após criação do shipment, eventos futuros de emissão CT-e (Agente 1/Fiscal). |
| `WhatsAppAiService` | Recebe evento `OrderOrchestrated` se `send_whatsapp` = true. |
| `Notifications` | Enfileirar `ShipmentStatusChanged`, `ProposalCreated` etc. |

---

## 8. Requisitos Técnicos

- Nova request class `OrchestrateOrderRequest`.
- Novo controller `AutomationController` (`app/Http/Controllers/Api/AutomationController.php`).
- Nova resource `OrchestratedOrderResource`.
- Nova migration `create_api_requests_table` (idempotência).
- Rotas: agrupar endpoint em `routes/api.php`, namespace `Api`.
- Testes feature (`tests/Feature/Api/OrchestrateOrderTest.php`) cobrindo cenários sucesso, cliente existente, sem frete, validação.
- Listener `SendOrderOrchestrationNotification` envia resumo ao cliente via WhatsApp quando solicitado (integração WuzAPI).

---

## 9. Roadmap Pós-Entrega

1. Expandir para múltiplos shipments por requisição (array `shipments[]`).
2. Suporte a anexos (upload de XML NF-e) via presigned URLs.
3. Versionamento do endpoint (`/api/mcp/v2/...`).
4. Métricas Prometheus (`order_orchestrated_total`, latência).

---

## 10. Referências de Código

- `app/Http/Controllers/Api/AutomationController.php`
- `app/Http/Requests/OrchestrateOrderRequest.php`
- `app/Services/OrderOrchestrationService.php`
- `app/Http/Resources/OrchestratedOrderResource.php`
- `app/Events/OrderOrchestrated.php`
- `app/Models/ApiRequest.php` / `database/migrations/2025_11_10_120000_create_api_requests_table.php`
- Testes: `tests/Feature/Api/OrchestrateOrderTest.php`

---

Documento pronto para desenvolvimento. Próximo passo: iniciar implementação seguindo esta especificação.  

