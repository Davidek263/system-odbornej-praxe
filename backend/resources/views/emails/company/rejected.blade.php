<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrácia zamietnutá</title>
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
            background-color: #f44336;
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
            border-left: 4px solid #f44336;
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
        <h1>Registrácia nebola schválená</h1>
    </div>

    <div class="content">
        <p>Dobrý deň {{ $company->contact_person_name }},</p>

        <p>Bohužiaľ, Vaša registrácia spoločnosti <strong>{{ $company->company_name }}</strong> nebola schválená garantom odbornej praxe.</p>

        @if(isset($reason) && $reason)
        <div class="details">
            <h3>Dôvod zamietnutia:</h3>
            <p>{{ $reason }}</p>
        </div>
        @endif

        <div class="details">
            <h3>Ďalšie kroky:</h3>
            <ul>
                <li>V prípade potreby kontaktujte garanta odbornej praxe</li>
                <li>Po vyriešení problémov sa môžete zaregistrovať znovu</li>
            </ul>
        </div>

        <center>
            <a href="{{ config('app.frontend_url') }}/register" class="button">Zaregistrovať sa znovu</a>
        </center>

        <p>S pozdravom,<br>Systém odbornej praxe UKF</p>
    </div>

    <div class="footer">
        <p>Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
    </div>
</body>
</html>
