<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de acesso</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4; }
        .container { background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 3px solid #ff6b35; }
        .header h1 { color: #ff6b35; margin: 0; font-size: 22px; }
        .code-box { background: #f9f9f9; border: 2px dashed #ff6b35; padding: 20px; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 6px; margin: 20px 0; border-radius: 8px; }
        .footer { text-align: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 {{ $tenant->name }}</h1>
        </div>
        <p>Olá, <strong>{{ $client->name ?? 'Cliente' }}</strong>!</p>
        <p>Seu código de acesso é:</p>
        <div class="code-box">{{ $code }}</div>
        <p>Ele expira às <strong>{{ $expiresAtFormatted }}</strong>. Não compartilhe este código.</p>
        <p>Se você não solicitou, informe imediatamente o suporte.</p>
        <div class="footer">
            <p>Atenciosamente,<br><strong>{{ $tenant->name }}</strong></p>
            <p style="margin-top: 8px;">Este é um e-mail automático. Não responda.</p>
        </div>
    </div>
</body>
</html>
