<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Superadmin TMS Conext</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;background:#0d1b2a;min-height:100vh;display:flex;align-items:center;justify-content:center;}
        .login-wrap{width:100%;max-width:420px;padding:20px;}
        .login-card{background:#112236;border-radius:16px;border:1px solid rgba(255,107,53,.15);padding:40px;}
        .login-logo{text-align:center;margin-bottom:32px;}
        .login-logo .badge{display:inline-block;background:#FF6B35;color:#fff;font-size:10px;font-weight:700;letter-spacing:2px;padding:3px 12px;border-radius:20px;margin-bottom:12px;}
        .login-logo h1{font-size:26px;font-weight:700;color:#fff;}
        .login-logo p{color:#7f9ab3;font-size:13px;margin-top:4px;}

        .form-group{margin-bottom:18px;}
        .form-label{display:block;font-size:12px;font-weight:600;color:#7f9ab3;margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px;}
        .input-wrap{position:relative;}
        .input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7f9ab3;font-size:14px;}
        .form-control{width:100%;padding:11px 14px 11px 40px;border-radius:8px;background:rgba(255,255,255,.05);border:1px solid rgba(255,107,53,.15);color:#e2e8f0;font-size:14px;font-family:'Poppins',sans-serif;outline:none;transition:border .2s;}
        .form-control:focus{border-color:#FF6B35;}
        .form-control::placeholder{color:#7f9ab3;}

        .remember{display:flex;align-items:center;gap:8px;font-size:13px;color:#7f9ab3;margin-bottom:22px;}
        .remember input{accent-color:#FF6B35;}

        .btn-login{width:100%;padding:13px;border:none;border-radius:8px;background:#FF6B35;color:#fff;font-size:15px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;transition:background .2s;letter-spacing:.3px;}
        .btn-login:hover{background:#e5602d;}

        .alert-error{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#fca5a5;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;}

        .back-link{display:block;text-align:center;margin-top:20px;color:#7f9ab3;font-size:12px;text-decoration:none;}
        .back-link:hover{color:#FF6B35;}
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-logo">
                <div class="badge">🔐 SUPERADMIN</div>
                <h1>TMS Conext</h1>
                <p>Acesso restrito ao painel de controle</p>
            </div>

            @if($errors->any())
                <div class="alert-error"><i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('superadmin.login.post') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="superadmin@conext.click" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <label class="remember">
                    <input type="checkbox" name="remember"> Lembrar-me
                </label>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Entrar</button>
            </form>
            <a href="{{ route('login') }}" class="back-link">← Voltar ao sistema principal</a>
        </div>
    </div>
</body>
</html>
