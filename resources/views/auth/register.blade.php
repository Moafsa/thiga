<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TMS LOG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-primary: #0d1b2a;
            --bg-secondary: #162840;
            --accent: #FF6B35;
            --accent-dark: #E55A2B;
            --text-primary: #e2e8f0;
            --text-secondary: #cbd5e1;
            --border: #2a3f52;
            --input-bg: rgba(255, 255, 255, 0.05);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .register-container {
            width: 100%;
            max-width: 620px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            animation: slideUp 500ms ease-out;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 25px;
            transition: color 200ms ease;
        }

        .back-link:hover {
            color: var(--accent-dark);
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo {
            font-size: 44px;
            margin-bottom: 10px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 6px;
        }

        .register-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
            color: var(--accent);
            font-size: 15px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 13px;
        }

        label .required {
            color: var(--accent);
        }

        input[type="email"],
        input[type="password"],
        input[type="text"],
        input[type="tel"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 200ms ease;
        }

        select option {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 107, 53, 0.08);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.15);
        }

        input::placeholder {
            color: #64748b;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-register {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 200ms ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            margin-top: 25px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.45);
        }

        .login-link {
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            color: var(--accent-dark);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .input-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 580px) {
            .register-container {
                padding: 25px 18px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .logo-text {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <a href="{{ '/' }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao início
        </a>

        <div class="register-header">
            <div class="logo">📦</div>
            <div class="logo-text">TMS SaaS</div>
            <p class="register-subtitle">Crie a conta da sua Transportadora</p>
        </div>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div><i class="fas fa-exclamation-circle"></i> {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- SESSÃO: DADOS DA EMPRESA -->
            <div class="section-header">
                <i class="fas fa-building"></i> Dados da Empresa / Transportadora
            </div>

            <div class="form-group">
                <label for="company_name">Nome da Empresa / Razão Social <span class="required">*</span></label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    placeholder="Ex: Translog Transportes Ltda"
                    value="{{ old('company_name') }}"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="company_cnpj">CNPJ <span class="required">*</span></label>
                    <input
                        type="text"
                        id="company_cnpj"
                        name="company_cnpj"
                        placeholder="00.000.000/0001-00"
                        value="{{ old('company_cnpj') }}"
                        maxlength="18"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="company_domain">Subdomínio Desejado</label>
                    <input
                        type="text"
                        id="company_domain"
                        name="company_domain"
                        placeholder="ex: translog"
                        value="{{ old('company_domain') }}"
                    >
                    <div class="input-hint">Ex: minhamarca (.thiga.app)</div>
                </div>
            </div>

            <!-- SESSÃO: DADOS DO ADMINISTRADOR -->
            <div class="section-header">
                <i class="fas fa-user-shield"></i> Dados do Administrador
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Nome <span class="required">*</span></label>
                    <input
                        type="text"
                        id="first_name"
                        name="first_name"
                        placeholder="Seu nome"
                        value="{{ old('first_name') }}"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="last_name">Sobrenome <span class="required">*</span></label>
                    <input
                        type="text"
                        id="last_name"
                        name="last_name"
                        placeholder="Seu sobrenome"
                        value="{{ old('last_name') }}"
                        required
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">E-mail <span class="required">*</span></label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="seu@email.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="phone">Telefone / WhatsApp <span class="required">*</span></label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        placeholder="(11) 99999-9999"
                        value="{{ old('phone') }}"
                        required
                    >
                </div>
            </div>

            <!-- SESSÃO: PLANO SELECIONADO -->
            <div class="section-header">
                <i class="fas fa-crown"></i> Plano de Assinatura
            </div>

            <div class="form-group">
                <label for="plan_id">Plano Escolhido <span class="required">*</span></label>
                <select id="plan_id" name="plan_id" required>
                    @if(isset($plans) && count($plans) > 0)
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (old('plan_id', $selectedPlanId ?? 2) == $plan->id) ? 'selected' : '' }}>
                                {{ $plan->name }} - R$ {{ number_format($plan->price, 2, ',', '.') }}/mês
                            </option>
                        @endforeach
                    @else
                        <option value="1">Plano Básico - R$ 99,00/mês</option>
                        <option value="2" selected>Plano Profissional - R$ 199,00/mês</option>
                        <option value="3">Plano Empresarial - R$ 399,00/mês</option>
                    @endif
                </select>
            </div>

            <!-- SESSÃO: SEGURANÇA -->
            <div class="section-header">
                <i class="fas fa-lock"></i> Senha de Acesso
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Senha <span class="required">*</span></label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Mínimo 8 caracteres"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirme a Senha <span class="required">*</span></label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirme sua senha"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Criar Conta da Empresa
            </button>

            <div class="login-link">
                Já tem uma conta? <a href="{{ route('login') }}">Faça login aqui</a>
            </div>
        </form>
    </div>

    <script>
        // Máscara para CNPJ
        document.getElementById('company_cnpj')?.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + '.' + x[3] + (x[4] ? '/' + x[4] : '') + (x[5] ? '-' + x[5] : '');
        });

        // Máscara para Telefone
        document.getElementById('phone')?.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    </script>
</body>
</html>
