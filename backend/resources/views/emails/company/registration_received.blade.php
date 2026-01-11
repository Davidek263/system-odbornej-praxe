<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrácia prijatá</title>
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
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 15px 0;
        }
        .next-steps {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #4CAF50;
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
        <h1>Registrácia prijatá!</h1>
    </div>

    <div class="content">
        <p>Dobrý deň {{ $company->contact_person_name }},</p>

        <p>Ďakujeme za registráciu spoločnosti <strong>{{ $company->company_name }}</strong> do systému odbornej praxe UKF.</p>

        <div class="info-box">
            <h3>📋 Stav registrácie</h3>
            <p>Vaša registrácia bola <strong>úspešne prijatá</strong> a momentálne čaká na schválenie garantom odbornej praxe.</p>
        </div>

        <div class="next-steps">
            <h3>Ďalšie kroky:</h3>
            <ol>
                <li><strong>Čakanie na schválenie:</strong> Garant praxe skontroluje Vaše údaje a rozhodne o schválení registrácie.</li>
                <li><strong>Notifikácia emailom:</strong> Po rozhodnutí garanta Vám príde email s výsledkom.</li>
                <li><strong>Ak bude schválené:</strong> Dostanete dočasné heslo a aktivačný link pre vstup do systému.</li>
                <li><strong>Ak bude zamietnuté:</strong> Dostanete email s dôvodom zamietnutia a možnosťou opätovnej registrácie.</li>
            </ol>
        </div>

        <div class="info-box">
            <h3>📝 Registrované údaje:</h3>
            <ul>
                <li><strong>Názov firmy:</strong> {{ $company->company_name }}</li>
                <li><strong>Kontaktná osoba:</strong> {{ $company->contact_person_name }}</li>
                <li><strong>Email:</strong> {{ $company->contact_person_email }}</li>
                <li><strong>Telefón:</strong> {{ $company->contact_person_phone }}</li>
            </ul>
        </div>

        <p>V prípade akýchkoľvek otázok nás neváhajte kontaktovať.</p>

        <p>S pozdravom,<br><strong>Systém odbornej praxe UKF</strong></p>
    </div>

    <div class="footer">
        <p>Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
    </div>
    </div>
</body>
</html>
