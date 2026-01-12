<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrácia schválená</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .credentials-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
        }
        .password-display {
            background-color: #f5f5f5;
            border: 2px dashed #666;
            padding: 15px;
            text-align: center;
            font-size: 24px;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .warning {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
    <div class="header">
        <h1>🎉 Registrácia bola schválená!</h1>
    </div>

    <div class="content">
        <p>Dobrý deň {{ $company->contact_person_name }},</p>

        <p>S radosťou Vám oznamujeme, že Vaša registrácia spoločnosti <strong>{{ $company->company_name }}</strong> bola <strong style="color: #4CAF50;">schválená garantom odbornej praxe</strong>.</p>

        <div class="credentials-box">
            <h3>🔑 Vaše prihlasovacie údaje</h3>
            <p><strong>Email:</strong> {{ $company->contact_person_email }}</p>
            <p><strong>Dočasné heslo:</strong></p>
            <div class="password-display">
                {{ $temporaryPassword }}
            </div>
            <p style="font-size: 12px; color: #666; margin-top: 10px;">
                ⚠️ Toto heslo si poznačte alebo skopírujte. Budete ho potrebovať pri aktivácii účtu.
            </p>
        </div>

        <div class="details">
            <h3>🚀 Aktivujte svoj účet (3 jednoduché kroky):</h3>
            <ol style="line-height: 2;">
                <li><strong>Kliknite na tlačidlo nižšie</strong> pre aktiváciu účtu</li>
                <li><strong>Nastavte si nové heslo</strong> pomocou dočasného hesla uvedeného vyššie</li>
                <li><strong>Prihláste sa</strong> a začnite pracovať so žiadosťami študentov</li>
            </ol>
        </div>

        <center>
            <a href="{{ $activationUrl }}" class="button">✓ Aktivovať účet</a>
        </center>

        <div class="warning">
            <h3>⏱️ Dôležité upozornenie:</h3>
            <p>Aktivačný link je platný <strong>48 hodín</strong>. Po uplynutí tejto doby budete musieť požiadať o nový aktivačný link.</p>
        </div>

        <div class="details">
            <h3>❓ Problémy s aktiváciou?</h3>
            <p>Ak nefunguje tlačidlo vyššie, skopírujte a vložte tento link do prehliadača:</p>
            <p style="word-break: break-all; background: #f5f5f5; padding: 10px; font-size: 12px;">
                {{ $activationUrl }}
            </p>
        </div>

        <p style="margin-top: 30px;">Tešíme sa na spoluprácu s Vami!</p>

        <p>S pozdravom,<br><strong>Systém odbornej praxe UKF</strong></p>
    </div>

    <div class="footer">
        <p>Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
        <p style="margin-top: 10px;">Ak ste si účet nevytvárali, ignorujte tento email.</p>
    </div>
    </div>
</body>
</html>
