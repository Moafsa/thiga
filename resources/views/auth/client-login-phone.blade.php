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
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #102527, #0c1a18);
            color: var(--cor-texto-claro);
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
            background-color: rgba(29, 60, 54, 0.95);
            border-radius: 20px;
            padding: 40px 32px;
            max-width: 420px;
            width: min(420px, 95%);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.55);
            position: relative;
        }

        .logo {
            font-size: 30px;
            font-weight: 700;
            color: var(--cor-acento);
            text-align: center;
            margin-bottom: 12px;
        }

        h1 {
            color: var(--cor-texto-claro);
            font-size: 26px;
            text-align: center;
            margin-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            color: rgba(245, 245, 245, 0.75);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.85);
        }

        .form-group input,
        .form-group select {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 14px;
            font-size: 16px;
            background-color: rgba(20, 57, 52, 0.8);
            color: #fff;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--cor-acento);
            box-shadow: 0 0 12px rgba(255, 107, 53, 0.5);
            outline: none;
        }

        .info-box {
            background: rgba(37, 211, 102, 0.15);
            border: 1px solid rgba(37, 211, 102, 0.4);
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .info-box i {
            color: #25D366;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 14px;
            background: linear-gradient(120deg, #ff7a4a, #ff945f);
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(255, 122, 74, 0.45);
        }

        .links {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: var(--cor-acento);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link {
            position: absolute;
            top: 14px;
            left: 14px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.85);
        }

        .message-card {
            padding: 10px 14px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .error-card {
            background: rgba(208, 2, 27, 0.15);
            border: 1px solid rgba(208, 2, 27, 0.6);
            color: #fff;
        }

        .success-card {
            background: rgba(23, 139, 93, 0.2);
            border: 1px solid rgba(23, 139, 93, 0.6);
            color: #fff;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 32px 22px;
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
                <i class="fas fa-box"></i> TMS SaaS
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
                Enviaremos um código de 6 dígitos por <strong>WhatsApp</strong> (telefone) ou por <strong>e-mail</strong>, conforme o que você informar.
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
                    <small style="display:block; margin-top:4px; color: rgba(255,255,255,0.6); font-size:12px;">
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
