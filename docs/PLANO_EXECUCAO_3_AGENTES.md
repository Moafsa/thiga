# Plano de Execução Coordenado – Integração n8n, WuzAPI e PWA do Motorista

**Data**: 10/11/2025  
**Coordenador (Agente 1)**: GPT-5 Codex  
**Objetivo**: Concluir a orquestração MCP/REST para o fluxo n8n, consolidar integrações WhatsApp (WuzAPI) e finalizar o PWA do motorista com rastreamento em tempo real, repartindo o trabalho entre três agentes trabalhando em paralelo.

---

## 1. Visão Geral

- **Contexto**: O TMS SaaS está com módulos core operacionais (frete, CRM, financeiro, dashboard motorista, etc.) e expõe cálculos via API MCP. Falta um fluxo automatizado para que o n8n/IA consuma o sistema fim a fim, um painel multi-tenant da WuzAPI e o hardening do PWA.
- **Entrega Esperada**: Em 2 sprints (≈10 dias corridos) com 3 agentes em paralelo, entregar:
  1. Endpoint orquestrador chamado pelo n8n que cria/atualiza cliente, calcula frete, gera proposta, shipment e rota (quando aplicável), devolvendo payload completo para a IA.
  2. Módulo de integrações WhatsApp baseado no Dockerfile oficial da WuzAPI, com Instâncias por tenant, QR code, status e templates de mensagens.
  3. PWA do motorista sólido (manifest + service worker), tokens dedicados, telemetria em tempo real e evidências (fotos, timeline) acessíveis à IA e clientes.

---

## 2. Arquitetura de Referência e Dependências

| Área | Repositório/Serviço | Observações |
|------|----------------------|-------------|
| TMS Laravel | `app/*`, `routes/*` | Multi-tenant com `spatie/laravel-multitenancy`; API MCP pronta (`McpFreightController`). |
| Integração WhatsApp | `app/Services/WuzApiService.php` | Já implementados métodos principais; falta UI multi-tenant e provisioning. |
| PWA Motorista | `resources/views/driver/*`, `app/Http/Controllers/Api/DriverController.php` | Dashboard pronto; precisa hardening PWA, autenticação e telemetria. |
| Automação/IA | n8n + fluxo IA | Requer novo endpoint orquestrador e payloads padronizados. |

**Dependências externas**:
- WuzAPI (Dockerfile fornecido) – backend Go + frontend React.
- Mitt Service (já configurado) para CT-e/MDF-e (apenas garantir eventos).

---

## 3. Divisão de Trabalho

### 🔶 Agente 1 – Orquestração & APIs (GPT-5 Codex)

**Escopo**  
1. Criar `AutomationController` com endpoint `POST /api/mcp/workflows/order`.
   - Entradas: token tenant (`X-Tenant-Token` ou Bearer), dados de cliente, carga, rota e opções de cálculo.
   - Passos: identificar/gerar cliente > acionar `FreightCalculationService` > criar proposta > criar shipment(s) > opcional rota (com driver padrão ou aguardando) > devolver payload com links (proposta, shipment, tracking, CT-e quando disponível).
2. Publicar documentação API (Swagger ou coleção Postman) e guias n8n com exemplos (folder `docs/api`).
3. Harden de autenticação MCP: rate limiting, logs estruturados, métricas (health/ping).
4. Ajustar `WhatsAppAiService` para consumir novo endpoint e gerar mensagens padrão (status frete, proposta, entrega).
5. Coordenar integração de eventos (shipment criado, CT-e autorizado, entrega) com notificações e WuzAPI (em parceria com Agente 2).

**Entregáveis**
- Código Laravel (controllers, requests, resources, testes feature).
- `docs/api/orchestration.md` + Swagger/collection.
- Scripts de seed/dados fictícios para QA.

### 🔷 Agente 2 – WuzAPI & Comunicação

**Escopo**  
1. Subir WuzAPI conforme Dockerfile oficial; adicionar ao `docker-compose.yml` com variáveis e instruções.
2. Criar módulo em `/settings/integrations/whatsapp`:
   - CRUD de instâncias (nome, webhook, tokens, status).
   - Botão “Gerar QR”, exibir estado (desconectado/conectado), logs básicos.
   - Salvamento seguro dos tokens (hash/criptografia).
3. Ajustar webhooks `/api/webhooks/whatsapp` para roteamento por tenant, emitindo eventos Laravel (para notificações, IA, atualização de status).
4. Criar templates parametrizados de mensagens (frete, proposta, status) e armazenar preferências por tenant.

**Entregáveis**
- Views Blade + controllers + migrations (tabela `whatsapp_integrations`).
- Atualização do `WuzApiService` (suporte multi-tenant, caching).
- Documentação de deploy (`docs/integracoes/wuzapi.md`) e runbook de operações.

### 🔷 Agente 3 – PWA Motorista & Telemetria

**Escopo**  
1. Preparar PWA completo:
   - `manifest.json`, `sw.js` (cache, offline fallback, push placeholders).
   - Build otimizada (resources bundler) e testes em dispositivos móveis.
2. Implementar autenticação específica (Sanctum guard “drivers”), tokens JWT/SPA com refresh, revogação, reset.
3. Enriquecer dashboard motorista:
   - Timeline dos shipments (status + fotos).
   - Upload de `DeliveryProof` com preview offline e retry.
   - Histórico de localização em tempo real (polling ou broadcast usando Laravel Echo/Pusher).
4. Criar API pública para acompanhamento do cliente (com código de rastreio) reaproveitando dados do motorista e fiscal.
5. Expor eventos de localização/status (para IA responder tracking).

**Entregáveis**
- Atualizações em controllers/API, assets PWA, middlewares e documentação `docs/pwa-motorista.md`.
- Testes feature e manuais de QA (cenários offline, upload, token expiração).

---

## 4. Cronograma Proposto (10 dias corridos)

| Dia | Agente 1 | Agente 2 | Agente 3 |
|-----|----------|----------|----------|
| 1 | Design endpoint orquestrador + contratos | Provisionar WuzAPI no Docker | Revisar estado atual PWA e autenticação |
| 2 | Implementar fluxo cliente→frete→proposta (mocks) | Criar migrations/models instância WhatsApp | Definir arquitetura tokens motorista |
| 3 | Criar recursos/requests + testes iniciais | Views CRUD integrações + QR | Implementar Sanctum guard driver |
| 4 | Integrar com shipments/rotas | Webhook handler + templates mensagens | Manifest + SW + caching básico |
| 5 | Documentação API + guida n8n | Documentar deploy WuzAPI | Upload comprovantes + timeline |
| 6 | Ajustar WhatsAppAiService, eventos | Ajustes multi-tenant e logs | Localização em tempo real |
| 7 | Hardening (rate limit, logs) | Testes E2E e QA módulo | API acompanhamento público |
| 8 | Testes automação (Postman/newman) | Integração com notificações/IA | Tests e bugfix PWA |
| 9 | Preparar release notes | Revisar segurança tokens | Ensaios offline, fallback |
|10 | UAT conjunto + handoff | UAT conjunto + handoff | UAT conjunto + handoff |

> **Importante**: Daily sync rápida entre agentes ao final de cada dia para alinhar contratos e detectar dependências/orquestração.

---

## 5. Requisitos de Qualidade e Aceite

1. **Segurança**  
   - Tokens tenant hash/rotacionáveis; rotas protegidas com middleware custom `tenant.token`.
   - Tokens motorista com revogação e logs.
   - Sanitização/validação de payloads (Request classes).

2. **Confiabilidade**  
   - Testes feature para principal fluxo do endpoint orquestrador (feliz + falha).
   - Testes manuais documentados (checklists) para WuzAPI e PWA.
   - Logs estruturados (`JsonFormatter`) com correlation ID por requisição MCP.

3. **Observabilidade**  
   - Health endpoint `GET /api/mcp/freight/health` atualizado com status extra (orquestrador, WuzAPI, PWA).
   - Métricas básicas (contagem requisições, tempos, falhas) prontas para futura integração Prometheus.

4. **Documentação**  
   - Cada agente mantém README/guia em `docs/` com setup, exemplos e troubleshooting.
   - Diagramas simples (PlantUML ou Excalidraw exportado) para os novos fluxos.

---

## 6. Comunicação e Alinhamento

- **Stand-up Assíncrono**: Mensagem diária no canal de projeto (agentes postam progresso, bloqueios, próximos passos).
- **Revisões**: Pull requests com checklist de QA e testes executados. Cada PR deve referenciar esta doc.
- **Integração Contínua**: GitHub Actions (ou pipeline equivalente) rodando testes e lint; merges via PR revisado por outro agente.
- **Handoff**: Ao finalizar, agente registra no doc do módulo (ex.: `docs/api/orchestration.md`, `docs/integracoes/wuzapi.md`, `docs/pwa-motorista.md`) o estado final, endpoints, variáveis de ambiente e próximo passo sugerido.

---

## 7. Próximos Passos Imediatos

1. **Agente 1** (Codex): Criar branch `feature/orchestration-endpoint`, iniciar implementação do `AutomationController` com testes base e draft de manifesto API.
2. **Agente 2**: Montar ambiente WuzAPI via Docker Compose e validar conexão com Laravel (`services.wuzapi`).
3. **Agente 3**: Mapear gaps de autenticação atual do driver e preparar plano de migração para tokens Sanctum.

Documento pronto para orientar a equipe simultaneamente. Vamos iniciar conforme cronograma.  

