<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Cliente - TMS SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E📦%3C/text%3E%3C/svg%3E">
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

        .page {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 50px 40px;
            max-width: 450px;
            width: min(450px, 95%);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            animation: slideUp 600ms ease-out;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
            text-align: center;
            margin-bottom: 15px;
        }

        h1 {
            color: var(--text-primary);
            font-size: 26px;
            text-align: center;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 12px 16px;
            font-size: 14px;
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            transition: all 200ms ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
            outline: none;
        }

        .info-box {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.25);
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 25px;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-secondary);
        }

        .info-box i {
            color: var(--accent);
            margin-top: 3px;
            font-size: 16px;
            flex-shrink: 0;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 200ms ease;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(255, 107, 53, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .links a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color 200ms ease;
        }

        .links a:hover {
            color: var(--accent-dark);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 25px;
            transition: color 200ms ease;
        }

        .back-link:hover {
            color: var(--accent-dark);
        }

        .message-card {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .error-card {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }

        .success-card {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #a7f3d0;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .logo {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="login-container">
            <a href="{{ route('login') }}" class="back-link">
                <i class="fas fa-arrow-left"></i> Voltar ao login tradicional
            </a>

            <div class="logo">
                📦 TMS SaaS
            </div>

            <h1>Login Cliente</h1>
            <p class="subtitle">Digite seu telefone ou e-mail cadastrado e receba um código de acesso</p>

            @php
                $tenantOptionsData = $tenantOptions ?? session('tenantOptions', []);
            @endphp

            @if ($errors->any())
                <div class="message-card error-card">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if(isset($tenantOptionsData) && count($tenantOptionsData))
                <div class="form-group">
                    <label for="tenant_id">Empresa</label>
                    <select name="tenant_id" id="tenant_id" required>
                        <option value="">Selecione a empresa</option>
                        @foreach ($tenantOptionsData as $option)
                            <option value="{{ $option['tenant_id'] }}" {{ old('tenant_id') == $option['tenant_id'] ? 'selected' : '' }}>
                                {{ $option['tenant_name'] }}
                            </option>
                        @endforeach
                    </select>
                    @if ($errors->has('tenant_id'))
                        <small style="color: #ff7a4a; margin-top: 4px; display: block;">
                            {{ $errors->first('tenant_id') }}
                        </small>
                    @endif
                </div>
            @endif

            @if (session('success'))
                <div class="message-card success-card">
                    {{ session('success') }}
                </div>
            @endif

            <div class="info-box">
                <i class="fas fa-key"></i>
                <span>Enviaremos o código de acesso e o link de entrada direta sem senha por <strong>WhatsApp</strong> ou por <strong>e-mail</strong>.</span>
            </div>

            <form method="POST" action="{{ route('client.login.request-code') }}">
                @csrf

                <div class="form-group">
                    <label for="identifier">Telefone ou e-mail</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        placeholder="(11) 99999-9999 ou email@exemplo.com"
                        value="{{ old('identifier', session('client_login_identifier')) }}"
                        required
                        autofocus
                        autocomplete="email"
                    >
                    <small style="display:block; margin-top:6px; color: var(--text-secondary); font-size:12px;">
                        Telefone: apenas números com DDD. E-mail: o cadastrado no seu cliente.
                    </small>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-paper-plane"></i> Enviar código
                </button>
            </form>

            <div class="links">
                <p>Já recebeu o código? <a href="{{ route('client.login.code') }}">Confirme aqui</a></p>
            </div>
        </div>
    </div>
</body>
</html>
