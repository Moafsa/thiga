<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - TMS SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E📦%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --surface: #0d2923;
            --panel: #0f1f1a;
            --accent: #ff7a4a;
            --text: #f6fbfb;
            --muted: rgba(255, 255, 255, 0.75);
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #071414, #0d2923);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px;
        }

        .page-wrapper {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: min(420px, 100%);
            background: var(--panel);
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 35px 70px rgba(0, 0, 0, 0.6);
            position: relative;
        }

        .back-link {
            position: absolute;
            top: 18px;
            left: 18px;
            font-size: 14px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 10px;
        }

        h1 {
            font-size: 26px;
            text-align: center;
            margin: 0;
        }

        .subtitle {
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            margin: 6px 0 24px;
        }

        .message-card {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .message-card.error {
            background: rgba(208, 2, 27, 0.2);
            border: 1px solid rgba(208, 2, 27, 0.45);
        }

        .message-card.success {
            background: rgba(23, 139, 93, 0.2);
            border: 1px solid rgba(23, 139, 93, 0.5);
        }

        .info-box {
            background: rgba(37, 211, 102, 0.12);
            border: 1px solid rgba(37, 211, 102, 0.4);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
        }

        .info-box i {
            color: #25d366;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 18px;
            font-size: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--muted);
        }

        .form-group input {
            width: 100%;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            padding: 12px 14px;
            font-size: 16px;
            background: rgba(15, 45, 38, 0.8);
            color: #fff;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(255, 122, 74, 0.35);
        }

        .form-group input[readonly] {
            opacity: 0.9;
        }

        .code-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 14px;
            background: linear-gradient(120deg, #ff7a4a, #ff945f);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(255, 122, 74, 0.5);
        }

        .links {
            margin-top: 18px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 520px) {
            .login-container {
                padding: 30px 20px;
            }

            .back-link {
                position: static;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="login-container">
            <a class="back-link" href="{{ route('client.login.phone') }}">
                <i class="fas fa-arrow-left"></i> Voltar ao telefone
            </a>

            <div class="logo">
                <i class="fas fa-lock"></i> Verificação
            </div>

            <h1>Digite o código</h1>
            <p class="subtitle">O código vale por 5 minutos e chega via WhatsApp</p>

            @if(isset($tenantName))
                <div class="message-card success">
                    Acessando empresa: {{ $tenantName }}
                </div>
            @endif

            @if(session('success'))
                <div class="message-card success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('code_sent'))
                <div class="message-card success">
                    Já enviamos um código para {{ session('phone', $phone) }}.
                </div>
            @endif

            @if($errors->any())
                <div class="message-card error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="info-box">
                <i class="fab fa-whatsapp"></i>
                Confira a conversa do WhatsApp e insira o código de 6 dígitos.
            </div>

            <form method="POST" action="{{ route('client.login.verify-code') }}">
                @csrf

                <div class="form-group">
                    <label>Telefone</label>
                    <input type="text" value="{{ $phone }}" readonly>
                    <input type="hidden" name="phone" value="{{ $phone }}">
                </div>

                <div class="form-group">
                    <label for="code">Código</label>
                    <input
                        class="code-input"
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        placeholder="000000"
                        value="{{ old('code') }}"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-check"></i> Verificar e entrar
                </button>
            </form>

            <div class="links">
                <p>Não recebeu? <a href="{{ route('client.login.phone') }}">Peça outro código</a></p>
            </div>
        </div>
    </div>
</body>
</html>
