<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TMS LOG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
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
            padding: 20px;
        }

        .register-container {
            width: 100%;
            max-width: 500px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 600ms ease-out;
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 10px;
        }

        .register-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
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
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: all 200ms ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        input::placeholder {
            color: var(--text-secondary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-register {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 200ms ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .login-link {
            text-align: center;
            font-size: 14px;
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            color: var(--accent-dark);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            transition: color 200ms ease;
        }

        .back-link:hover {
            color: var(--accent);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
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
            <i class="fas fa-arrow-left"></i> Voltar ao inicio
        </a>

        <div class="register-header">
            <div class="logo">📦</div>
            <div class="logo-text">TMS SaaS</div>
            <p class="register-subtitle">Crie sua Conta</p>
        </div>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="company_name">Nome da Empresa / Transportadora</label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    placeholder="Sua Transportadora Ltda"
                    value="{{ old('company_name') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="company_cnpj">CNPJ da Empresa</label>
                <input
                    type="text"
                    id="company_cnpj"
                    name="company_cnpj"
                    placeholder="00.000.000/0000-00"
                    value="{{ old('company_cnpj') }}"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Nome</label>
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
                    <label for="last_name">Sobrenome</label>
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

            <div class="form-group">
                <label for="email">Email</label>
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
                <label for="phone">Telefone (opcional)</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    placeholder="(11) 99999-9999"
                    value="{{ old('phone') }}"
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirme a Senha</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="Confirme sua senha"
                    required
                >
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>

            <div class="login-link">
                Já tem uma conta? <a href="{{ route('login') }}">Faça login aqui</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('company_cnpj')?.addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
            if (!x[2]) {
                e.target.value = x[1];
            } else {
                e.target.value = x[1] + '.' + x[2] + '.' + x[3] + '/' + x[4] + (x[5] ? '-' + x[5] : '');
            }
        });
    </script>
</body>
</html>
