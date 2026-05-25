<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Thiga TMS - Sistema de Gestão de Transportes SaaS">
    <title>Thiga TMS - Sistema Inteligente de Transportes</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E📦%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* SuperAdmin Dark Theme - Variables */
        :root {
            --superadmin-bg-primary: #0d1b2a;
            --superadmin-bg-secondary: #162840;
            --superadmin-bg-tertiary: #1a3244;
            --superadmin-accent: #FF6B35;
            --superadmin-accent-light: #FFB347;
            --superadmin-accent-dark: #E55A2B;
            --superadmin-text-primary: #e2e8f0;
            --superadmin-text-secondary: #cbd5e1;
            --superadmin-border: #2a3f52;
            --superadmin-shadow: rgba(0, 0, 0, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--superadmin-bg-primary);
            color: var(--superadmin-text-primary);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar */
        nav {
            background-color: rgba(13, 27, 42, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--superadmin-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--superadmin-accent);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
        }

        .nav-links a {
            color: var(--superadmin-text-primary);
            text-decoration: none;
            transition: color 200ms ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            color: var(--superadmin-accent);
        }

        .cta-nav {
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-dark) 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 200ms ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .cta-nav:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        /* Hero Section */
        .hero {
            padding: 100px 40px;
            text-align: center;
            background: linear-gradient(135deg, var(--superadmin-bg-primary) 0%, var(--superadmin-bg-secondary) 100%);
            animation: fadeIn 500ms ease-out;
        }

        .hero h1 {
            font-size: 56px;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: slideUp 600ms ease-out;
        }

        .hero p {
            font-size: 18px;
            color: var(--superadmin-text-secondary);
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            animation: slideUp 700ms ease-out;
        }

        .hero-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            animation: slideUp 800ms ease-out;
        }

        .btn {
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 200ms ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-dark) 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--superadmin-accent);
            border: 2px solid var(--superadmin-accent);
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.2);
        }

        .btn-secondary:hover {
            background: var(--superadmin-accent);
            color: white;
            transform: translateY(-3px);
        }

        /* Features Section */
        .features {
            padding: 80px 40px;
            background-color: var(--superadmin-bg-primary);
        }

        .section-title {
            font-size: 42px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 60px;
            color: var(--superadmin-text-primary);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: linear-gradient(135deg, var(--superadmin-bg-secondary) 0%, var(--superadmin-bg-tertiary) 100%);
            padding: 30px;
            border-radius: 12px;
            border: 1px solid var(--superadmin-border);
            transition: all 300ms ease;
            animation: slideUp 500ms ease-out;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: var(--superadmin-accent);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.2);
        }

        .feature-icon {
            font-size: 48px;
            color: var(--superadmin-accent);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--superadmin-text-primary);
        }

        .feature-card p {
            font-size: 14px;
            color: var(--superadmin-text-secondary);
            line-height: 1.6;
        }

        /* Benefits Section */
        .benefits {
            padding: 80px 40px;
            background: linear-gradient(135deg, var(--superadmin-bg-secondary) 0%, var(--superadmin-bg-tertiary) 100%);
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .benefit-item {
            text-align: center;
            animation: slideUp 600ms ease-out;
        }

        .benefit-number {
            font-size: 48px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
        }

        .benefit-item h4 {
            font-size: 18px;
            font-weight: 600;
            color: var(--superadmin-text-primary);
            margin-bottom: 8px;
        }

        .benefit-item p {
            color: var(--superadmin-text-secondary);
            font-size: 14px;
        }

        /* Pricing Section */
        .pricing {
            padding: 80px 40px;
            background-color: var(--superadmin-bg-primary);
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-card {
            background: linear-gradient(135deg, var(--superadmin-bg-secondary) 0%, var(--superadmin-bg-tertiary) 100%);
            padding: 40px;
            border-radius: 12px;
            border: 1px solid var(--superadmin-border);
            text-align: center;
            transition: all 300ms ease;
            animation: slideUp 500ms ease-out;
            position: relative;
        }

        .pricing-card.featured {
            border-color: var(--superadmin-accent);
            box-shadow: 0 0 30px rgba(255, 107, 53, 0.2);
            transform: scale(1.05);
        }

        .pricing-card:hover {
            transform: translateY(-8px) scale(1);
            border-color: var(--superadmin-accent);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.2);
        }

        .pricing-card .badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-dark) 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pricing-card h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--superadmin-text-primary);
        }

        .pricing-card .price {
            font-size: 36px;
            font-weight: 700;
            color: var(--superadmin-accent);
            margin-bottom: 10px;
        }

        .pricing-card .period {
            color: var(--superadmin-text-secondary);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 30px;
            text-align: left;
        }

        .pricing-features li {
            padding: 10px 0;
            color: var(--superadmin-text-secondary);
            font-size: 14px;
            border-bottom: 1px solid var(--superadmin-border);
        }

        .pricing-features li:before {
            content: '✓ ';
            color: var(--superadmin-accent);
            font-weight: 700;
            margin-right: 8px;
        }

        /* CTA Section */
        .cta {
            padding: 80px 40px;
            text-align: center;
            background: linear-gradient(135deg, var(--superadmin-accent) 0%, var(--superadmin-accent-dark) 100%);
        }

        .cta h2 {
            font-size: 42px;
            font-weight: 700;
            color: white;
            margin-bottom: 20px;
        }

        .cta p {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
        }

        .cta-button {
            background-color: white;
            color: var(--superadmin-accent);
            padding: 14px 32px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 200ms ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Footer */
        footer {
            background-color: var(--superadmin-bg-secondary);
            padding: 40px;
            border-top: 1px solid var(--superadmin-border);
            text-align: center;
            color: var(--superadmin-text-secondary);
        }

        footer p {
            margin: 0;
            font-size: 14px;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            nav {
                flex-direction: column;
                gap: 20px;
                padding: 15px 20px;
            }

            .nav-links {
                flex-direction: column;
                gap: 15px;
                width: 100%;
            }

            .hero {
                padding: 60px 20px;
            }

            .hero h1 {
                font-size: 36px;
            }

            .hero p {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
            }

            .features,
            .benefits,
            .pricing,
            .cta {
                padding: 60px 20px;
            }

            .section-title {
                font-size: 32px;
                margin-bottom: 40px;
            }

            .pricing-card.featured {
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="logo">
            <i class="fas fa-cube"></i>
            Thiga TMS
        </div>
        <ul class="nav-links">
            <li><a href="#features">Recursos</a></li>
            <li><a href="#benefits">Benefícios</a></li>
            <li><a href="#pricing">Preços</a></li>
            <li><a href="/login" class="cta-nav">Entrar</a></li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Gerencie suas Transportes Inteligentemente</h1>
        <p>Solução completa de TMS SaaS para otimizar rotas, reduzir custos e aumentar eficiência operacional</p>
        <div class="hero-buttons">
            <a href="/register" class="btn btn-primary">Começar Gratuitamente</a>
            <a href="#features" class="btn btn-secondary">Saiba Mais</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">Recursos Principais</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-map-location-dot"></i></div>
                <h3>Otimização de Rotas</h3>
                <p>Algoritmos inteligentes que calculam as melhores rotas, reduzindo tempo e custos de combustível</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3>Análise em Tempo Real</h3>
                <p>Dashboard com métricas e KPIs atualizados em tempo real para tomada de decisão rápida</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-mobile"></i></div>
                <h3>App para Motorista</h3>
                <p>Aplicativo PWA otimizado para motoristas gerenciarem suas entregas em qualquer lugar</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-users"></i></div>
                <h3>Gestão de Usuários</h3>
                <p>Controle total de acessos e permissões para diferentes roles (Admin, Motorista, Cliente)</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-file-invoice"></i></div>
                <h3>Faturamento Automático</h3>
                <p>Geração automática de propostas, faturas e controle financeiro integrado</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-paint-palette"></i></div>
                <h3>Temas Customizáveis</h3>
                <p>Personalize as cores e identidade visual da plataforma para sua marca</p>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits" id="benefits">
        <h2 class="section-title">Por que Escolher Thiga?</h2>
        <div class="benefits-grid">
            <div class="benefit-item">
                <div class="benefit-number">30%</div>
                <h4>Redução de Custos</h4>
                <p>Economia média em combustível e operações com otimização de rotas</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-number">45%</div>
                <h4>Aumento de Eficiência</h4>
                <p>Mais entregas em menos tempo com melhor planejamento logístico</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-number">99.9%</div>
                <h4>Disponibilidade</h4>
                <p>Plataforma confiável e sempre disponível para seu negócio crescer</p>
            </div>
            <div class="benefit-item">
                <div class="benefit-number">24/7</div>
                <h4>Suporte Premium</h4>
                <p>Equipe dedicada pronta para ajudar a qualquer momento</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <h2 class="section-title">Planos de Preços</h2>
        <div class="pricing-grid">
            <div class="pricing-card">
                <h3>Básico</h3>
                <div class="price">R$ 299</div>
                <div class="period">por mês</div>
                <ul class="pricing-features">
                    <li>Até 10 motoristas</li>
                    <li>Otimização de rotas básica</li>
                    <li>Dashboard essencial</li>
                    <li>Suporte por email</li>
                </ul>
                <a href="/register" class="btn btn-primary">Selecionar Plano</a>
            </div>
            <div class="pricing-card featured">
                <div class="badge">Recomendado</div>
                <h3>Profissional</h3>
                <div class="price">R$ 799</div>
                <div class="period">por mês</div>
                <ul class="pricing-features">
                    <li>Até 50 motoristas</li>
                    <li>Otimização avançada</li>
                    <li>Analytics completo</li>
                    <li>Faturamento automático</li>
                    <li>Suporte prioritário</li>
                </ul>
                <a href="/register" class="btn btn-primary">Selecionar Plano</a>
            </div>
            <div class="pricing-card">
                <h3>Enterprise</h3>
                <div class="price">Customizado</div>
                <div class="period">sob demanda</div>
                <ul class="pricing-features">
                    <li>Motoristas ilimitados</li>
                    <li>Integração API completa</li>
                    <li>Suporte 24/7 dedicado</li>
                    <li>Customizações avançadas</li>
                    <li>SLA garantido</li>
                </ul>
                <a href="mailto:contato@thiga.com" class="btn btn-secondary">Fale Conosco</a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <h2>Transforme sua Logística Hoje</h2>
        <p>Junte-se a centenas de empresas que já confiam no Thiga TMS</p>
        <a href="/register" class="cta-button">Começar Gratuitamente</a>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Thiga TMS. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
