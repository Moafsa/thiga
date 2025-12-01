<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assinatura Criada - TMS SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Variáveis de cores */
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
        }

        /* Estilos globais */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-container {
            background-color: var(--cor-secundaria);
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 600px;
            text-align: center;
        }

        .success-icon {
            font-size: 5em;
            color: var(--cor-acento);
            margin-bottom: 30px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .success-title {
            font-size: 2.5em;
            font-weight: 700;
            color: var(--cor-acento);
            margin-bottom: 20px;
        }

        .success-message {
            font-size: 1.2em;
            color: var(--cor-texto-claro);
            margin-bottom: 40px;
        }

        .trial-info {
            background-color: var(--cor-principal);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 40px;
        }

        .trial-info h3 {
            color: var(--cor-acento);
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .trial-info p {
            color: var(--cor-texto-claro);
            margin-bottom: 10px;
        }

        .trial-countdown {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--cor-acento);
        }

        .next-steps {
            text-align: left;
            margin-bottom: 40px;
        }

        .next-steps h3 {
            color: var(--cor-acento);
            margin-bottom: 20px;
            font-size: 1.3em;
        }

        .step-list {
            list-style: none;
            padding: 0;
        }

        .step-list li {
            padding: 10px 0;
            color: var(--cor-texto-claro);
            display: flex;
            align-items: center;
        }

        .step-list li i {
            color: var(--cor-acento);
            margin-right: 15px;
            width: 20px;
        }

        .btn-dashboard {
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-dashboard:hover {
            background-color: #FF885A;
        }

        .features-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .feature-card {
            background-color: var(--cor-principal);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }

        .feature-card i {
            font-size: 2em;
            color: var(--cor-acento);
            margin-bottom: 10px;
        }

        .feature-card h4 {
            color: var(--cor-texto-claro);
            margin-bottom: 5px;
        }

        .feature-card p {
            color: #999;
            font-size: 0.9em;
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 20px;
                padding: 40px 20px;
            }
            
            .success-title {
                font-size: 2em;
            }
            
            .features-preview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="success-title">Assinatura Criada com Sucesso!</h1>
        
        <p class="success-message">
            Parabéns! Sua assinatura foi criada e você já pode começar a usar o TMS SaaS.
        </p>

        <div class="trial-info">
            <h3><i class="fas fa-gift"></i> Teste Grátis Ativo</h3>
            <p>Você tem <strong>30 dias</strong> para experimentar todos os recursos do sistema.</p>
            <p>Não será cobrado nada durante o período de teste.</p>
            <div class="trial-countdown">
                <i class="fas fa-clock"></i> 30 dias restantes
            </div>
        </div>

        <div class="next-steps">
            <h3><i class="fas fa-arrow-right"></i> Próximos Passos</h3>
            <ul class="step-list">
                <li><i class="fas fa-user-cog"></i> Configure os dados da sua empresa</li>
                <li><i class="fas fa-users"></i> Adicione usuários à sua conta</li>
                <li><i class="fas fa-truck"></i> Comece a cadastrar suas cargas</li>
                <li><i class="fas fa-route"></i> Configure suas rotas</li>
                <li><i class="fab fa-whatsapp"></i> Ative o WhatsApp para seus clientes</li>
            </ul>
        </div>

        <div class="features-preview">
            <div class="feature-card">
                <i class="fas fa-truck-loading"></i>
                <h4>Gestão de Cargas</h4>
                <p>Controle completo do ciclo de vida das suas cargas</p>
            </div>
            <div class="feature-card">
                <i class="fab fa-whatsapp"></i>
                <h4>WhatsApp IA</h4>
                <p>Atendimento automatizado para seus clientes</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-file-invoice"></i>
                <h4>Integração Fiscal</h4>
                <p>Emissão automática de CT-e e MDF-e</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h4>Relatórios</h4>
                <p>Dashboards e análises completas</p>
            </div>
        </div>

        <a href="{{ route('dashboard') }}" class="btn-dashboard">
            <i class="fas fa-tachometer-alt"></i> Ir para o Dashboard
        </a>
    </div>
</body>
</html>























