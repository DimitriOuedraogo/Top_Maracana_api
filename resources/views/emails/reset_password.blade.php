<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Réinitialisation mot de passe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; padding: 40px 20px; }
        .container {
            max-width: 480px; margin: 0 auto; background: #ffffff;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: #DC2626; padding: 32px;
            text-align: center; color: white;
        }
        .header h1 { font-size: 22px; font-weight: 700; }
        .header p { font-size: 14px; opacity: 0.85; margin-top: 6px; }
        .body { padding: 36px 32px; }
        .greeting { font-size: 16px; color: #374151; margin-bottom: 16px; }
        .text { font-size: 14px; color: #6B7280; line-height: 1.6; margin-bottom: 28px; }
        .code-label { font-size: 13px; color: #6B7280; text-align: center; margin-bottom: 12px; }
        .code-box {
            background: #FEF2F2; border: 2px dashed #DC2626;
            border-radius: 10px; padding: 20px;
            text-align: center; margin-bottom: 24px;
        }
        .code {
            font-size: 42px; font-weight: 800; letter-spacing: 14px;
            color: #DC2626; font-family: 'Courier New', monospace;
        }
        .expiry {
            background: #FEF3C7; border-left: 4px solid #F59E0B;
            border-radius: 6px; padding: 12px 16px;
            font-size: 13px; color: #92400E; margin-bottom: 24px;
        }
        .warning {
            background: #FEF2F2; border-left: 4px solid #DC2626;
            border-radius: 6px; padding: 12px 16px;
            font-size: 13px; color: #991B1B;
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
            <h1>🔑 Réinitialisation</h1>
            <p>Demande de nouveau mot de passe</p>
        </div>

        <div class="body">
            <p class="greeting">Bonjour <strong>{{ $user->name }}</strong> 👋</p>
            <p class="text">
                Vous avez demandé la réinitialisation de votre mot de passe.
                Entrez le code ci-dessous dans l'application pour continuer.
            </p>

            <p class="code-label">Votre code de réinitialisation</p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>

            <div class="expiry" style="margin-bottom: 16px;">
                ⏱ Ce code expire dans <strong>10 minutes</strong>.
            </div>

            <div class="warning">
                ⚠️ Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                Votre mot de passe ne sera <strong>pas modifié</strong>.
            </div>
        </div>

        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
        </div>
    </div>
</body>
</html>