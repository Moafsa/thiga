<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TMS LOG</title>
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

        .login-container {
            width: 100%;
            max-width: 450px;
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            animation: slideUp 600ms ease-out;
        }

        .login-header {
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

        .login-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 25px;
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

        .btn-login {
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
            margin-bottom: 15px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .register-link {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 200ms ease;
        }

        .register-link a:hover {
            color: var(--accent-dark);
        }

        .phone-login {
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }

        .phone-login-title {
            font-size: 12px;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 15px;
            text-align: center;
            letter-spacing: 0.05em;
        }

        .phone-login-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-phone {
            padding: 10px 16px;
            background: transparent;
            color: var(--text-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 200ms ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-phone:hover {
            border-color: var(--accent);
            color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
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

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .logo-text {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <a href="{{ '/' }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Voltar ao inicio
        </a>

        <div class="login-header">
            <div class="logo">📦</div>
            <div class="logo-text">TMS SaaS</div>
            <p class="login-subtitle">Entrar na Plataforma</p>
        </div>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email ou Telefone</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="seu@email.com ou (11) 99999-9999"
                    value="{{ old('email') }}"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Sua senha"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>

            <div class="register-link">
                Não tem uma conta? <a href="{{ route('register') }}">Cadastre-se aqui</a>
            </div>

            <div class="phone-login">
                <div class="phone-login-title">Entrar por telefone:</div>
                <div class="phone-login-options">
                    <a href="{{ route('client.login.phone') }}" class="btn-phone">
                        <i class="fas fa-user"></i> Sou cliente - Entrar por telefone
                    </a>
                    <a href="{{ route('salesperson.login.phone') }}" class="btn-phone">
                        <i class="fas fa-store"></i> Sou vendedor - Entrar por telefone
                    </a>
                    <a href="{{ route('driver.login.phone') }}" class="btn-phone">
                        <i class="fas fa-truck"></i> Sou motorista - Entrar por telefone
                    </a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
