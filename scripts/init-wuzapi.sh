#!/bin/bash

set -euo pipefail

# Script utilitário para provisionar manualmente uma instância do WuzAPI
# Uso:
#   ./scripts/init-wuzapi.sh <token> [nome]
#
# Variáveis de ambiente opcionais:
#   WUZAPI_URL (default: http://localhost:${WUZAPI_HTTP_PORT:-8081})
#   WUZAPI_ADMIN_TOKEN (default: admin_token_123)
#   WUZAPI_INSTANCE_TOKEN
#   WUZAPI_INSTANCE_NAME
#   WUZAPI_WEBHOOK_URL (default: http://app:9000/api/webhooks/whatsapp)
#   WUZAPI_EVENTS (default: Message,ReadReceipt,Presence)

WUZAPI_URL="${WUZAPI_URL:-http://localhost:${WUZAPI_HTTP_PORT:-8081}}"
ADMIN_TOKEN="${WUZAPI_ADMIN_TOKEN:-admin_token_123}"
INSTANCE_TOKEN="${1:-${WUZAPI_INSTANCE_TOKEN:-}}"
INSTANCE_NAME="${2:-${WUZAPI_INSTANCE_NAME:-TMS Local QA}}"
WEBHOOK_URL="${WUZAPI_WEBHOOK_URL:-http://app:9000/api/webhooks/whatsapp}"
EVENTS="${WUZAPI_EVENTS:-Message,ReadReceipt,Presence}"

if [[ -z "$INSTANCE_TOKEN" ]]; then
  echo "[erro] Informe o token da instância como primeiro argumento ou defina WUZAPI_INSTANCE_TOKEN."
  exit 1
fi

echo "[info] Provisionando instância no WuzAPI: $INSTANCE_NAME"
echo "[info] Endpoint: $WUZAPI_URL"

sleep 3

CREATE_PAYLOAD=$(jq -n \
  --arg name "$INSTANCE_NAME" \
  --arg token "$INSTANCE_TOKEN" \
  --arg webhook "$WEBHOOK_URL" \
  --arg events "$EVENTS" \
  '{name: $name, token: $token, webhook: $webhook, events: $events}')

CREATE_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$WUZAPI_URL/admin/users" \
  -H "Authorization: $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d "$CREATE_PAYLOAD")

CREATE_BODY=$(echo "$CREATE_RESPONSE" | head -n1)
CREATE_STATUS=$(echo "$CREATE_RESPONSE" | tail -n1)

if [[ "$CREATE_STATUS" -ge 400 ]]; then
  echo "[warn] Não foi possível criar usuário (status $CREATE_STATUS)."
  echo "$CREATE_BODY"
else
  echo "[ok] Usuário criado ou já existente."
fi

WEBHOOK_PAYLOAD=$(jq -n \
  --arg webhook "$WEBHOOK_URL" \
  --arg events "$EVENTS" \
  '{webhook: $webhook, events: $events}')

WEBHOOK_RESPONSE=$(curl -s -w "\n%{http_code}" -X POST "$WUZAPI_URL/webhook/set" \
  -H "Token: $INSTANCE_TOKEN" \
  -H "Content-Type: application/json" \
  -d "$WEBHOOK_PAYLOAD")

WEBHOOK_BODY=$(echo "$WEBHOOK_RESPONSE" | head -n1)
WEBHOOK_STATUS=$(echo "$WEBHOOK_RESPONSE" | tail -n1)

if [[ "$WEBHOOK_STATUS" -ge 400 ]]; then
  echo "[warn] Falha ao configurar webhook (status $WEBHOOK_STATUS)."
  echo "$WEBHOOK_BODY"
else
  echo "[ok] Webhook configurado."
fi

echo "[done] Integração pronta. Abra $WUZAPI_URL/login?token=$INSTANCE_TOKEN para ler o QR Code."









