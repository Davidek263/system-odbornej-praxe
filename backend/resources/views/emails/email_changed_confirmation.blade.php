<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email bol zmenený</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
            <h1 style="color: white; margin: 0;">✓ Email úspešne zmenený</h1>
        </div>

        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;">
            <p>Dobrý deň {{ $name }},</p>

            <p>Váš email bol úspešne zmenený.</p>

            <div style="background: white; padding: 15px; border-left: 4px solid #42b883; margin: 20px 0;">
                <strong>Pôvodný email:</strong> {{ $oldEmail }}<br>
                <strong>Nový email:</strong> {{ $newEmail }}
            </div>

            <p>Od tejto chvíle sa prihlasujte pomocou nového emailu: <strong>{{ $newEmail }}</strong></p>

            <div style="background: #d1ecf1; border: 1px solid #0c5460; padding: 15px; border-radius: 6px; margin-top: 20px;">
                <strong>ℹ️ Poznámka:</strong> Ak ste aktuálne prihlásený, vaša session zostáva aktívna. Nový email budete používať pri ďalšom prihlásení.
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                S pozdravom,<br>
                Systém odbornej praxe
            </p>
        </div>
    </div>
</body>
</html>
