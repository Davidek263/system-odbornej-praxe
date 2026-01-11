<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nová žiadosť o odbornú prax</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    <tr>
                        <td>
                            <div style="background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                                <h1 style="margin: 0;">Nová žiadosť o odbornú prax</h1>
                            </div>

                            <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                                <p style="margin: 0 0 15px 0;">Dobrý deň {{ $company->contact_person_name }},</p>

                                <p style="margin: 0 0 15px 0;">Študent <strong>{{ $student->first_name }} {{ $student->last_name }}</strong> vytvoril novú žiadosť o odbornú prax vo vašej spoločnosti <strong>{{ $company->company_name }}</strong>.</p>

                                <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50;">
                                    <h3 style="margin-top: 0;">Detaily praxe:</h3>
                                    <ul style="margin: 10px 0; padding-left: 20px;">
                                        <li><strong>Akademický rok:</strong> {{ $internship->academic_year }}</li>
                                        <li><strong>Semester:</strong> {{ $internship->semester }}</li>
                                        <li><strong>Dátum začiatku:</strong> {{ $internship->date_start ? \Carbon\Carbon::parse($internship->date_start)->format('d.m.Y') : 'Neurčený' }}</li>
                                        <li><strong>Dátum ukončenia:</strong> {{ $internship->date_end ? \Carbon\Carbon::parse($internship->date_end)->format('d.m.Y') : 'Neurčený' }}</li>
                                    </ul>

                                    <h3 style="margin: 15px 0 10px 0;">Študijný odbor:</h3>
                                    <p style="margin: 0 0 15px 0;">{{ $student->studyField->study_field_name ?? 'Neuvedený' }}</p>

                                    <h3 style="margin: 15px 0 10px 0;">Kontakt na študenta:</h3>
                                    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                                        <li><strong>Email:</strong> {{ $student->email }}</li>
                                        <li><strong>Telefón:</strong> {{ $student->phone_number ?? 'Neuvedené' }}</li>
                                    </ul>
                                </div>

                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center" style="padding: 20px 0;">
                                            <a href="{{ config('app.frontend_url') }}/internships" style="display: inline-block; padding: 12px 30px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Zobraziť žiadosť</a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 15px 0;">Prosím, potvrďte alebo zamietnite túto žiadosť v systéme.</p>

                                <p style="margin: 15px 0 0 0;">S pozdravom,<br><strong>Systém odbornej praxe UKF</strong></p>
                            </div>

                            <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
                                <p style="margin: 0;">Tento email bol automaticky vygenerovaný systémom odbornej praxe.</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
