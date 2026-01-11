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
            max-width: 600px;
            margin: 0 auto;
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
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
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
    <div class="header">
        <h1>Registrácia bola schválená!</h1>
    </div>

    <div class="content">
        <p>Dobrý deň {{ $company->contact_person_name }},</p>

        <p>S radosťou Vám oznamujeme, že Vaša registrácia spoločnosti <strong>{{ $company->company_name }}</strong> bola schválená garantom odbornej praxe.</p>

        <div class="details">
            <h3>📧 Dôležité emaily</h3>
            <p>Spolu s týmto emailom Vám boli odoslané ďalšie 2 dôležité emaily:</p>
            <ol>
                <li><strong>Dočasné heslo</strong> - email obsahujúci Vaše prvé prihlasovacie heslo</li>
                <li><strong>Aktivačný link</strong> - kliknite naň pre aktiváciu účtu</li>
            </ol>
        </div>

        <div class="details">
            <h3>🚀 Ďalšie kroky:</h3>
            <ol>
                <li><strong>Skontrolujte emailovú schránku</strong> - mali by ste mať 3 emaily (vrátane tohto)</li>
                <li><strong>Kliknite na aktivačný link</strong> v aktivačnom emaili</li>
                <li><strong>Nastavte si nové heslo</strong> pomocou dočasného hesla z emailu</li>
                <li><strong>Prihláste sa do systému</strong> a začnite spracovávať žiadosti študentov</li>
            </ol>
        </div>

        <center>
            <a href="{{ config('app.frontend_url') }}/login" class="button">Prihlásiť sa</a>
        </center>

        <p>Tešíme sa na spoluprácu s Vami!</p>

        <p>S pozdravom,<br>Systém odbornej praxe UKF</p>
    </div>

    <div class="footer">
        <p>Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
    </div>
</body>
</html>
