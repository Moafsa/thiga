<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email de Teste</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4caf50;
        }
        .header h1 {
            color: #4caf50;
            margin: 0;
            font-size: 24px;
        }
        .success-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        .content {
            text-align: center;
            margin-bottom: 30px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">✅</div>
            <h1>Email de Teste</h1>
        </div>
        
        <div class="content">
            <p>Parabéns! Seu email de teste foi enviado com sucesso.</p>
            <p>Se você recebeu este email, significa que sua configuração de email está funcionando corretamente.</p>
            
            <div style="margin-top: 30px; padding: 20px; background-color: #f9f9f9; border-radius: 5px; text-align: left;">
                <strong>Informações da Configuração:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Provedor:</strong> {{ ucfirst($tenant->email_provider ?? 'Não configurado') }}</li>
                    <li><strong>Tenant:</strong> {{ $tenant->name }}</li>
                    <li><strong>Data/Hora:</strong> {{ now()->format('d/m/Y H:i:s') }}</li>
                </ul>
            </div>
        </div>
        
        <div class="footer">
            <p>Este é um email de teste enviado pelo sistema TMS SaaS.</p>
        </div>
    </div>
</body>
</html>
