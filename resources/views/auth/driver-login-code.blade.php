<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - TMS SaaS</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='.9em' font-size='90'%3E🚛%3C/text%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css    <style>
        :root {
            --bg-primary: #0d1b2a;
            --bg-secondary: #162840;
            --accent: #FF6B35;
            --accent-dark: #E55A2B;
            --text-primary: #e2e8f0;
            --text-secondary: #cbd5e1;
            --border: #2a3f52;
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
            color: var(--text-primary);
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
            width: min(450px, 100%);
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 50px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            animation: slideUp 600ms ease-out;
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

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 28px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 15px;
        }

        h1 {
            font-size: 26px;
            text-align: center;
            margin: 0 0 8px;
            color: var(--text-primary);
            font-weight: 700;
        }

        .subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 14px;
            margin: 0 0 30px;
            line-height: 1.5;
        }

        .message-card {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        .message-card.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #fca5a5;
        }

        .message-card.success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid #10b981;
            color: #a7f3d0;
        }

        .info-box {
            background: rgba(255, 107, 53, 0.1);
            border: 1px solid rgba(255, 107, 53, 0.25);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-secondary);
        }

        .info-box i {
            margin-top: 3px;
            font-size: 16px;
            flex-shrink: 0;
        }

        .info-box i.fa-whatsapp {
            color: #25d366;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 25px;
            font-size: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .form-group input {
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--border);
            padding: 12px 16px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-family: 'Poppins', sans-serif;
            transition: all 200ms ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            background: rgba(255, 107, 53, 0.1);
            box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1);
        }

        .form-group input[readonly] {
            opacity: 0.7;
            background: rgba(255, 255, 255, 0.02);
            cursor: not-allowed;
        }

        .code-input {
            letter-spacing: 12px;
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            padding-left: 24px !important;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
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

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 520px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="login-container">
            <a class="back-link" href="{{ route('driver.login.phone') }}">
                <i class="fas fa-arrow-left"></i> Voltar ao telefone
            </a>

            <div class="logo">
                🚛 TMS SaaS
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
                <span>Confira a conversa do WhatsApp e insira o código de 6 dígitos.</span>
            </div>

            <form method="POST" action="{{ route('driver.login.verify-code') }}">
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
                <p>Não recebeu? <a href="{{ route('driver.login.phone') }}">Peça outro código</a></p>
            </div>
        </div>
    </div>
</body>
</html>

