# Integração WhatsApp com WuzAPI

Este documento resume como o TMS SaaS consome o WuzAPI para atendimento e notificações via WhatsApp. Para o passo a passo detalhado de operação, consulte também `docs/integracoes/wuzapi.md`.

## Visão Geral

- O WuzAPI roda lado a lado com a stack Laravel via Docker Compose (`service: wuzapi`);
- Cada tenant possui uma instância própria de WhatsApp, configurada em `Configurações → Integrações → WhatsApp`;
- Os tokens das instâncias são únicos, ficam criptografados no banco e são usados para roteamento de webhooks;
- O fluxo de mensagens é automatizado pelo `WhatsAppAiService`, que acessa OpenAI para interpretar perguntas e responde com informações de carga, proposta ou rota.

### Arquitetura Simplificada

```
Cliente WhatsApp → WuzAPI (tenant instance) → Webhook /api/webhooks/whatsapp
        ↘︎ eventos → WhatsAppIntegrationManager → WhatsAppAiService → TMS / IA → resposta via WuzAPI
```

## Configuração Rápida

1. Suba os containers:
   ```bash
   docker-compose up -d app wuzapi pgsql redis
   ```

2. Gere uma integração via interface:
   - Acesse `https://app.localhost:8082/settings/integrations/whatsapp`;
   - Crie uma nova instância (um token é exibido uma única vez; guarde-o com cuidado);
   - Clique em "Ver QR Code" e faça a leitura via WhatsApp Web (smartphone) para autenticar.

3. Opcional (CLI): use `scripts/init-wuzapi.sh <token> "<Nome Instance>"` para provisionar ambientes de QA.

## Recursos Disponíveis

- **Atendimento IA**: interpreta perguntas de clientes e responde com dados de `Shipment`, `Proposal` e `Route`;
- **Notificações Automáticas**: `WhatsAppAiService::sendShipmentUpdate` envia mensagens em tempo real ao cliente;
- **Templates por Tenant**: armazenados em `whatsapp_message_templates` (feliz, falha, exceções);
- **Dashboard Operacional**: Listagem de instâncias, status em tempo real, sincronização, QR Code e remoção segura.

## Webhooks e Segurança

- Endpoint único: `POST /api/webhooks/whatsapp`;
- O token bruto da instância precisa ser enviado no header `Token`, `X-Wuzapi-Token` ou como Bearer;
- O backend calcula o hash SHA-256 do token para resolver a instância do tenant;
- Mensagens inválidas ou tokens desconhecidos retornam HTTP 202 (`ignored`) sem interromper o WuzAPI;
- Todas as respostas são registradas em log com `integration_id` e payload resumido.

## Operação / Runbook Resumido

| Ação | Passos |
|------|--------|
| **Sincronizar status** | Botão "Sincronizar" na UI; atualiza `status`, `last_synced_at` e payload da sessão |
| **Gerar novo QR** | Botão "Ver QR Code"; faz GET `settings/integrations/whatsapp/{id}/qr` |
| **Remover instância** | Botão "Remover"; desconecta via WuzAPI e apaga da tabela |
| **Reconfigurar webhook** | Editar integração (via manager) ou rodar `scripts/init-wuzapi.sh <token>` para ambiente local |
| **Logs** | `storage/logs/laravel.log` agrega eventos. Container WuzAPI utiliza `docker-compose logs wuzapi` |

## Troubleshooting Rápido

1. **QR Code não aparece**  
   - Verifique se o container `wuzapi` está de pé (`docker-compose ps`);  
   - Cheque se o token está correto (`whatsapp_integrations.wuzapi_user_token_hash`);  
   - Logs no Laravel mostrarão o motivo (timeout, token inválido, etc.).

2. **Mensagem não chega ao cliente**  
   - Confirme que existe uma integração com status `connected`;  
   - Verifique se `WhatsAppAiService` logou token ausente;  
   - Ajuste o número do cliente com DDI no cadastro (`Client::phone`).

3. **Webhook não roteia**  
   - Confira se o token enviado pelo WuzAPI é o mesmo exibido ao criar a instância;  
   - Habilite `docker-compose logs app` para ver se o evento foi ignorado (202);  
   - Revise `config/services.php` (`WUZAPI_WEBHOOK_URL`).

## Segurança & Boas Práticas

- Tokens nunca ficam em texto plano no banco; apenas a UI expõe o valor uma vez;
- Utilize HTTPS e IP allow list para o WuzAPI em produção;
- Limite o acesso ao menu "Integrações WhatsApp" apenas a `Admin Tenant` e `Super Admin`;
- Gere tokens com pelo menos 48 caracteres (padrão do manager) e evite recicle-los em múltiplos tenants.

Para detalhes pormenorizados (check-lists de operação, diagramas, exemplos de payload), veja `docs/integracoes/wuzapi.md`.









