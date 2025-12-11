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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --cor-principal: #245a49;
            --cor-secundaria: #1a3d33;
            --cor-acento: #FF6B35;
            --cor-texto-claro: #F5F5F5;
            --cor-texto-escuro: #333;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--cor-principal) 0%, var(--cor-secundaria) 100%);
            color: var(--cor-texto-claro);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background-color: var(--cor-secundaria);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .logo {
            font-size: 32px;
            font-weight: 700;
            color: var(--cor-acento);
            margin-bottom: 30px;
        }

        .logo i {
            margin-right: 10px;
        }

        h1 {
            color: var(--cor-texto-claro);
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .subtitle {
            color: rgba(245, 245, 245, 0.7);
            font-size: 14px;
            margin-bottom: 30px;
        }

        .phone-display {
            background-color: var(--cor-principal);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
            color: var(--cor-acento);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--cor-texto-claro);
            font-weight: 600;
        }

        .code-input {
            width: 100%;
            padding: 15px;
            border: 2px solid #444;
            border-radius: 8px;
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 8px;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--cor-acento);
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.3);
        }

        .code-input::placeholder {
            letter-spacing: 4px;
            color: #999;
        }

        .btn-login {
            width: 100%;
            background-color: var(--cor-acento);
            color: var(--cor-principal);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background-color: #FF885A;
        }

        .btn-login:disabled {
            background-color: #666;
            cursor: not-allowed;
        }

        .links {
            margin-top: 20px;
        }

        .links a {
            color: var(--cor-acento);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: var(--cor-texto-claro);
        }

        .error-message {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-message {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--cor-acento);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: var(--cor-texto-claro);
        }

        .back-link i {
            margin-right: 5px;
        }

        .whatsapp-info {
            background-color: rgba(37, 211, 102, 0.1);
            border: 1px solid rgba(37, 211, 102, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: rgba(245, 245, 245, 0.9);
        }

        .whatsapp-info i {
            color: #25D366;
            margin-right: 8px;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <a href="{{ route('driver.login.phone') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>

    <div class="login-container">
        <div class="logo">
            <i class="fas fa-truck"></i> TMS SaaS
        </div>
        
        <h1>Verificar Código</h1>
        <p class="subtitle">Digite o código enviado</p>

        <div class="phone-display">
            <i class="fas fa-phone"></i> {{ $phone }}
        </div>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif

        <div class="whatsapp-info">
            <i class="fab fa-whatsapp"></i>
            Verifique suas mensagens do WhatsApp e digite o código de 6 dígitos
        </div>

        <form method="POST" action="{{ route('driver.login.verify-code') }}">
            @csrf
            
            <input type="hidden" name="phone" value="{{ $phone }}">
            
            <div class="form-group">
                <label for="code">Código de Verificação</label>
                <input 
                    type="text" 
                    id="code" 
                    name="code" 
                    class="code-input"
                    required 
                    autofocus 
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    inputmode="numeric"
                >
                <small style="color: rgba(245, 245, 245, 0.6); font-size: 12px; margin-top: 5px; display: block;">
                    O código expira em 5 minutos
                </small>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-check"></i> Verificar e Entrar
            </button>
        </form>

        <div class="links">
            <p><a href="{{ route('driver.login.phone') }}">Solicitar novo código</a></p>
        </div>
    </div>

    <script>
        // Auto-format code input
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 6);
        });
    </script>
</body>
</html>





