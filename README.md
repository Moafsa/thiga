# TMS SaaS - Sistema de Gestão de Transportes

Uma plataforma SaaS completa para gestão de transportadoras, desenvolvida com Laravel, PostgreSQL e integração WhatsApp com IA.

## 🚀 Características Principais

- **Arquitetura Multi-Tenant**: Isolamento completo de dados entre transportadoras
- **Integração WhatsApp**: Atendimento automatizado com IA (OpenAI)
- **Gestão Fiscal**: Integração com sistema Mitt para CT-e e MDF-e
- **Billing**: Sistema de assinaturas com Asaas
- **Interface Moderna**: Desenvolvida com Livewire e Tailwind CSS
- **Docker**: Ambiente de desenvolvimento containerizado

## 🏗️ Arquitetura

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Backend       │    │   WhatsApp      │
│   (Livewire)    │◄──►│   (Laravel)     │◄──►│   (WuzAPI)      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
                                │
                                ▼
                       ┌─────────────────┐
                       │   Database      │
                       │   (PostgreSQL)  │
                       └─────────────────┘
```

## 🛠️ Tecnologias

- **Backend**: Laravel 10, PHP 8.2
- **Database**: PostgreSQL 15
- **Cache**: Redis 7
- **Frontend**: Livewire, Tailwind CSS
- **WhatsApp**: WuzAPI (Go)
- **IA**: OpenAI GPT-3.5
- **Billing**: Asaas API
- **Fiscal**: Mitt API
- **Container**: Docker, Docker Compose

## 📋 Pré-requisitos

- Docker e Docker Compose
- Git
- Navegador web moderno

## 🚀 Instalação e Execução

### 1. Clone o repositório
```bash
git clone <repository-url>
cd thiga-transportes
```

### 2. Configure as variáveis de ambiente
```bash
cp env.example .env
```

Edite o arquivo `.env` com suas configurações:
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=tms_saas
DB_USERNAME=tms_user
DB_PASSWORD=tms_password

# WuzAPI
WUZAPI_BASE_URL=http://wuzapi:8080
WUZAPI_ADMIN_TOKEN=admin_token_123
WUZAPI_USER_TOKEN=tms_whatsapp_token_123

# OpenAI
OPENAI_API_KEY=your_openai_api_key

# Asaas
ASAAS_API_KEY=your_asaas_api_key

# Mitt
MITT_API_KEY=your_mitt_api_key
```

### 3. Execute o ambiente de desenvolvimento
```bash
# Windows
start-servers.bat

# Linux/Mac
chmod +x scripts/init-wuzapi.sh
./scripts/init-wuzapi.sh
```

### 4. Acesse a aplicação
- **Aplicação**: http://localhost:8080
- **WhatsApp**: http://localhost:8081/login?token=tms_whatsapp_token_123
- **API Docs**: http://localhost:8081/api

## 📱 Integração WhatsApp

O sistema inclui integração completa com WhatsApp usando WuzAPI:

### Funcionalidades
- **Rastreamento**: Clientes podem consultar status das cargas
- **Notificações**: Atualizações automáticas de status
- **IA**: Respostas inteligentes com OpenAI
- **Multi-dispositivo**: Suporte a múltiplas sessões

### Configuração
1. Acesse http://localhost:8081/login?token=tms_whatsapp_token_123
2. Escaneie o QR Code com seu WhatsApp
3. Configure o webhook para receber mensagens

## 🏢 Módulos do Sistema

### 1. **Multi-Tenant**
- Isolamento de dados por transportadora
- Configurações independentes
- Billing por tenant

### 2. **CRM**
- Gestão de clientes
- Vendedores
- Endereços múltiplos

### 3. **Operacional**
- Cadastro de cargas
- Rastreamento
- Status de entrega

### 4. **Fiscal**
- Emissão de CT-e
- Emissão de MDF-e
- Integração com Sefaz

### 5. **Financeiro**
- Faturamento
- Contas a receber/pagar
- Fluxo de caixa

### 6. **Rotas**
- Otimização de rotas
- App do motorista
- Rastreamento em tempo real

## 🔧 Comandos Úteis

```bash
# Ver logs
docker-compose logs -f

# Parar serviços
docker-compose down

# Reiniciar serviços
docker-compose restart

# Executar comandos Laravel
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed

# Acessar container
docker-compose exec app bash
```

## 📊 API Endpoints

### Rastreamento Público
```http
GET /api/v1/track-shipment?tracking_code=ABC123
GET /api/v1/shipment-history?tracking_code=ABC123
```

### Webhooks
```http
POST /api/webhooks/whatsapp
POST /api/webhooks/asaas
POST /api/webhooks/mitt
```

## 🔒 Segurança

- Autenticação com Sanctum
- Isolamento multi-tenant
- Validação de webhooks
- Rate limiting
- Criptografia de dados sensíveis

## 📈 Monitoramento

- Logs centralizados
- Métricas de performance
- Alertas de erro
- Dashboard de status

## 🚀 Deploy

### Desenvolvimento
```bash
docker-compose up -d
```

### Produção
```bash
docker-compose -f docker-compose.prod.yml up -d
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Suporte

- **Documentação**: [docs/](docs/)
- **Issues**: [GitHub Issues](https://github.com/your-repo/issues)
- **Email**: suporte@thiga.com.br

## 🎯 Roadmap

- [ ] App mobile para motoristas
- [ ] Integração com GPS
- [ ] Dashboard de analytics
- [ ] API para terceiros
- [ ] Integração com ERPs
- [ ] Sistema de notificações push

---

**Desenvolvido com ❤️ pela equipe Thiga**