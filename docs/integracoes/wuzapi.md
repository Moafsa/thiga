# Integração WuzAPI – Guia de Operações Multi-Tenant

**Última atualização:** 10/11/2025  
**Responsável:** Agente 2 (WhatsApp & Comunicação)

---

## 1. Visão Geral

O TMS SaaS suporta múltiplas instâncias do WhatsApp (uma por tenant) através do [WuzAPI](https://github.com/pedroherpeto/wuzapi). Cada instância é provisionada com token próprio, possui webhook dedicado e se conecta automaticamente ao fluxo de IA para atendimento e notificações.

### Componentes Principais

- **Container `wuzapi`** (Docker) – gateway HTTP → WhatsApp Web.
- **Tabela `whatsapp_integrations`** – metadados da instância, token criptografado, status e payload da sessão.
- **Tabela `whatsapp_message_templates`** – templates parametrizados (frete, proposta, status, falha).
- **`WhatsAppIntegrationManager`** – orquestra provisioning, webhook e sincronização com WuzAPI.
- **`WebhookController@whatsapp`** – resolve o tenant via token e delega para `WhatsAppAiService`.
- **UI (`settings/integrations/whatsapp`)** – criação, QR Code, sincronização e exclusão segura.

---

## 2. Requisitos de Ambiente

```env
# URLs e tokens base
APP_URL=https://localhost:8082
WUZAPI_BASE_URL=http://wuzapi:8080
WUZAPI_ADMIN_TOKEN=admin_token_123
WUZAPI_WEBHOOK_URL=http://app:9000/api/webhooks/whatsapp

# Porta externa do container (opcional)
WUZAPI_HTTP_PORT=8081

# Chave de IA
OPENAI_API_KEY=...
```

- `docker-compose.yml` já inclui o serviço `wuzapi`, volumes persistentes (`wuzapi_data`, `wuzapi_sessions`) e depende do container `app`.
- Em produção, configure HTTPS reverso (Nginx/Traefik) para expor o WuzAPI com TLS e IP allow list.

---

## 3. Provisionamento de uma Nova Instância

### Via Interface (recomendado)

1. Acesse `Configurações → Integrações → WhatsApp` com um usuário `Admin Tenant`.
2. Clique em **"Criar integração"** e informe:
   - Nome da instância (ex.: `Thiga Matriz`);
   - Telefone exibido (opcional, apenas informativo);
   - Webhook alternativo (opcional – padrão é `/api/webhooks/whatsapp`).
3. Guarde o token exibido após a criação (aparece uma única vez).
4. Clique em **"Ver QR Code"** e leia com o app WhatsApp do dispositivo corporativo.
5. Opcional: clique em **"Sincronizar"** para confirmar que o estado mudou para `connected`.

### Via Script (QA / Automação)

```bash
# Torne o script executável (uma vez)
chmod +x scripts/init-wuzapi.sh

# Provisiona instância com token customizado
./scripts/init-wuzapi.sh "<TOKEN_ALEATORIO_48_CHARS>" "Tenant QA"
```

Parâmetros adicionais via env:
- `WUZAPI_URL`, `WUZAPI_ADMIN_TOKEN`, `WUZAPI_WEBHOOK_URL`, `WUZAPI_EVENTS`.
- Requer `curl` e `jq` disponíveis no shell.

---

## 4. Operação Diária (Runbook)

| Situação | Ação recomendada |
|----------|------------------|
| **Checar status** | Botão **"Sincronizar"** na UI → atualiza `status`, `last_synced_at`, `last_session_payload`. |
| **Rever QR Code** | Botão **"Ver QR Code"** → GET `/settings/integrations/whatsapp/{id}/qr` retorna SVG base64. |
| **Remover instância** | Botão **"Remover"** → chama `WhatsAppIntegrationManager::disconnect` (encerra sessão) + delete. |
| **Regenerar token** | Criar nova instância (tokens não são rotacionados automaticamente). Atualize dispositivos e webhooks. |
| **Logs** | `storage/logs/laravel.log` (aplicação) e `docker-compose logs wuzapi` (gateway). |
| **Templates de mensagem** | Gerenciar via seeders ou interface administrativa futura (tabela `whatsapp_message_templates`). |

### Classificação de Status

- `connected` – sessão ativa, mensagens fluindo;
- `pending` – aguardando QR, reconectando ou aguardando leitura;
- `disconnected` – sessão encerrada, aguarda reconexão;
- `error` – payload do WuzAPI não reconhecido (consultar logs).

---

## 5. Webhooks & Mensageria

- Endpoint: `POST /api/webhooks/whatsapp` (não requer autenticação adicional, token é o segredo).
- O token pode vir nos headers `Token`, `X-Wuzapi-Token`, Authorization Bearer ou no payload (`token`, `sessionToken`).
- Eventos suportados:
  - `Message`: processado pelo `WhatsAppAiService` (somente texto por enquanto);
  - `ReadReceipt` e `Presence`: logados para futuras automações;
  - Outros eventos: logados como `Unknown` (não bloqueiam o fluxo).
- Respostas:
  - `200 {"status":"success"}` – evento tratado;
  - `202 {"status":"ignored","reason":"missing_token|unknown_token"}` – token ausente/desconhecido;
  - `500 {"status":"error"}` – falha inesperada (verificar logs).

---

## 6. Segurança e Compliance

- Tokens criptografados com `Crypt::encryptString` e hash SHA-256 para lookup;
- UI apenas exibe o token na criação; admin deve armazená-lo em cofre seguro (1Password, Vault, etc.);
- Apenas `Admin Tenant` e `Super Admin` acessam a UI;
- Recomenda-se expor o WuzAPI em produção apenas via rede privada ou VPN;
- Logs de webhook não incluem o token completo (somente prefixo). 

### Recomendações Adicionais

- Rotacione o token caso haja suspeita de vazamento;
- Configure monitoramento para `status != connected` por mais de X minutos;
- Considere limites de requisição (rate limiting) no Ingress para o WuzAPI / webhook público.

---

## 7. Troubleshooting

1. **QR Code não aparece / HTTP 500**  
   - Confirme que o container `wuzapi` está ativo;  
   - Valide `WUZAPI_ADMIN_TOKEN`;  
   - Verifique se o token enviado pertence à integração (`SELECT wuzapi_user_token_hash FROM whatsapp_integrations`).  

2. **Mensagens não saem**  
   - Checar se existe integração `connected` para o tenant;  
   - Validar número do cliente com DDI (`Client::phone`);  
   - Consultar logs: `grep "WhatsApp status update error" storage/logs/laravel.log`.  

3. **Webhook não mapeia tenant**  
   - Confirmar que o WuzAPI está enviando o token correto;  
   - Inspecionar logs com `Unknown token` (somente prefixo é exibido);  
   - Validar se o token foi recriado (nesse caso, registrar a nova integração).  

4. **Timeouts frequentes**  
   - Aumentar recursos do container `wuzapi` (CPU/RAM);  
   - Habilitar `docker stats wuzapi` para monitorar consumo;  
   - Considerar balanceamento horizontal para grandes volumes (clustering ainda não testado).  

---

## 8. Próximos Passos / Backlog

- [ ] Criar UI para edição/gestão de templates de mensagem por tenant;
- [ ] Expor API `GET /api/tenants/{id}/integrations/whatsapp` para automações externas;
- [ ] Implementar alertas (Slack/Email) quando uma instância ficar desconectada > X minutos;
- [ ] Adicionar métricas Prometheus (status e latência de envio).

---

## 9. Referências

- Código relacionado:
  - `app/Services/WhatsAppIntegrationManager.php`
  - `app/Http/Controllers/Settings/WhatsAppIntegrationController.php`
  - `app/Models/WhatsAppIntegration.php`
  - `app/Services/WhatsAppAiService.php`
- Documentação complementar: `docs/WHATSAPP_INTEGRATION.md`, `docs/PLANO_EXECUCAO_3_AGENTES.md`
- Repositório WuzAPI: https://github.com/pedroherpeto/wuzapi

