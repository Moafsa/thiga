<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - TMS SaaS</title>
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

        .register-container {
            background-color: var(--cor-secundaria);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo-mark {
            font-size: 32px;
            font-weight: 700;
            color: var(--cor-acento);
            margin-bottom: 30px;
        }

        .logo-mark i {
            margin-right: 10px;
        }

        h1 {
            color: var(--cor-texto-claro);
            font-size: 24px;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-section {
            margin-bottom: 24px;
            text-align: left;
        }

        .form-section h2 {
            color: var(--cor-texto-claro);
            font-size: 18px;
            margin-bottom: 8px;
        }

        .form-section p {
            color: rgba(245, 245, 245, 0.7);
            font-size: 14px;
            margin-bottom: 16px;
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

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #444;
            border-radius: 8px;
            background-color: var(--cor-principal);
            color: var(--cor-texto-claro);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--cor-acento);
            box-shadow: 0 0 10px rgba(255, 107, 53, 0.3);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .btn-primary {
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

        .btn-primary:hover {
            background-color: #FF885A;
        }

        .btn-primary:disabled {
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

        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--cor-acento);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link i {
            margin-right: 5px;
        }

        @media (max-width: 480px) {
            .register-container {
                margin: 20px;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <a href="/" class="back-link">
        <i class="fas fa-arrow-left"></i> Go Back
    </a>

    <div class="register-container">
        <div class="logo-mark">
            <i class="fas fa-truck"></i> TMS SaaS
        </div>
        
        <h1>Create your tenant</h1>

        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-section">
                <h2>Tenant information</h2>
                <p>We use these details to create your trial environment.</p>
                <div class="form-group">
                    <label for="company_name">Company name</label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="Example Logistics Ltd.">
                </div>
                <div class="form-group">
                    <label for="company_cnpj">CNPJ</label>
                    <input type="text" id="company_cnpj" name="company_cnpj" value="{{ old('company_cnpj') }}" required placeholder="00.000.000/0000-00">
                </div>
                <div class="form-group">
                    <label for="company_domain">Custom domain (optional)</label>
                    <input type="text" id="company_domain" name="company_domain" value="{{ old('company_domain') }}" placeholder="logistics.example.com">
                </div>
            </div>

            <div class="form-section">
                <h2>Administrator account</h2>
                <p>This user will receive the Admin Tenant role automatically.</p>
                <div class="form-group">
                    <label for="name">Full name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="you@company.com">
                </div>
                <div class="form-group">
                    <label for="phone">Phone (optional)</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+55 11 90000-0000">
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="At least 8 characters">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat your password">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary">
                <i class="fas fa-user-plus"></i> Start free trial
            </button>
        </form>

        <div class="links">
            <p>Already registered? <a href="{{ route('login') }}">Sign in</a></p>
        </div>
    </div>
</body>
</html>










