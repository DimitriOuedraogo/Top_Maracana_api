<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Code de vérification</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 40px 20px; }
        .container {
            max-width: 480px; margin: 0 auto; background: #ffffff;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: #4F46E5; padding: 32px;
            text-align: center; color: white;
        }
        .header h1 { font-size: 22px; font-weight: 700; }
        .header p { font-size: 14px; opacity: 0.85; margin-top: 6px; }
        .body { padding: 36px 32px; }
        .greeting { font-size: 16px; color: #374151; margin-bottom: 16px; }
        .text { font-size: 14px; color: #6B7280; line-height: 1.6; margin-bottom: 28px; }
        .code-label { font-size: 13px; color: #6B7280; text-align: center; margin-bottom: 12px; }
        .code-box {
            background: #F3F4F6; border: 2px dashed #4F46E5;
            border-radius: 10px; padding: 20px;
            text-align: center; margin-bottom: 24px;
        }
        .code {
            font-size: 42px; font-weight: 800; letter-spacing: 14px;
            color: #4F46E5; font-family: 'Courier New', monospace;
        }
        .expiry {
            background: #FEF3C7; border-left: 4px solid #F59E0B;
            border-radius: 6px; padding: 12px 16px;
            font-size: 13px; color: #92400E; margin-bottom: 24px;
        }
        .footer {
            background: #F9FAFB; padding: 20px 32px;
            text-align: center; border-top: 1px solid #E5E7EB;
        }
        .footer p { font-size: 12px; color: #9CA3AF; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Vérification de compte</h1>
            <p>Confirmez votre adresse email</p>
        </div>

        <div class="body">
            <p class="greeting">Bonjour <strong>{{ $user->name }}</strong> 👋</p>
            <p class="text">
                Merci pour votre inscription ! Pour activer votre compte,
                entrez le code ci-dessous dans l'application.
            </p>

            <p class="code-label">Votre code de vérification</p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>

            <div class="expiry">
                ⏱ Ce code expire dans <strong>10 minutes</strong>.
                Ne le partagez avec personne.
            </div>

            <p class="text" style="margin-bottom:0;">
                Si vous n'avez pas créé de compte, vous pouvez ignorer cet email en toute sécurité.
            </p>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>