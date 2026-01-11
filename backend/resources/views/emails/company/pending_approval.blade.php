<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nová firma čaká na schválenie</title>
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
            margin: 10px 5px;
        }
        .button.reject {
            background-color: #f44336;
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
        <h1>Nová firma čaká na schválenie</h1>
    </div>

    <div class="content">
        <p>Dobrý deň,</p>

        <p>V systéme sa zaregistrovala nová firma, ktorá čaká na Vaše schválenie.</p>

        <div class="details">
            <h3>Údaje o firme:</h3>
            <ul>
                <li><strong>Názov firmy:</strong> {{ $company->company_name }}</li>
                <li><strong>Kontaktná osoba:</strong> {{ $company->contact_person_name }} {{ $user->last_name }}</li>
                <li><strong>Email:</strong> {{ $company->contact_person_email }}</li>
                <li><strong>Telefón:</strong> {{ $company->contact_person_phone }}</li>
            </ul>

            <h3>Adresa firmy:</h3>
            <ul>
                <li><strong>Ulica a číslo:</strong> {{ $address->street }} {{ $address->street_number }}</li>
                <li><strong>Mesto:</strong> {{ $address->city }}</li>
                <li><strong>PSČ:</strong> {{ $address->postal_code }}</li>
                <li><strong>Krajina:</strong> {{ $address->country }}</li>
            </ul>
        </div>

        <center>
            <a href="{{ config('app.frontend_url') }}/guarantor/pending-companies" class="button">Zobraziť čakajúce firmy</a>
        </center>

        <p>Prosím, prihláste sa do systému a schváľte alebo zamietnite registráciu tejto firmy.</p>

        <p>S pozdravom,<br>Systém odbornej praxe UKF</p>
    </div>

    <div class="footer">
        <p>Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
    </div>
</body>
</html>
