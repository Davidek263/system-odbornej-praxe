<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prax potvrdená firmou</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    <tr>
                        <td>
                            <!-- Header -->
                            <div style="background-color: #2196F3; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                                <h1 style="margin: 0; color: white;">✓ Prax potvrdená firmou</h1>
                            </div>

                            <!-- Content -->
                            <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                                <p style="margin: 0 0 15px 0;">Dobrý deň,</p>

                                <p style="margin: 0 0 15px 0;">Oznamujeme Vám, že odborná prax bola <strong>potvrdená firmou</strong> {{ $company->company_name }}.</p>

                                <!-- Details Box -->
                                <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #2196F3;">
                                    <h3 style="margin: 0 0 10px 0; color: #333;">Detaily praxe:</h3>
                                    <ul style="margin: 10px 0; padding-left: 20px;">
                                        <li style="margin: 5px 0;"><strong>Študent:</strong> {{ $student->first_name }} {{ $student->last_name }}</li>
                                        <li style="margin: 5px 0;"><strong>Firma:</strong> {{ $company->company_name }}</li>
                                        <li style="margin: 5px 0;"><strong>Akademický rok:</strong> {{ $internship->academic_year }}</li>
                                        <li style="margin: 5px 0;"><strong>Semester:</strong> {{ $internship->semester }}</li>
                                        <li style="margin: 5px 0;"><strong>Obdobie:</strong> {{ $internship->date_start ? \Carbon\Carbon::parse($internship->date_start)->format('d.m.Y') : 'Neurčený' }} - {{ $internship->date_end ? \Carbon\Carbon::parse($internship->date_end)->format('d.m.Y') : 'Neurčený' }}</li>
                                    </ul>

                                    <p style="margin: 10px 0 0 0;"><strong>Aktuálny stav:</strong> {{ $internship->currentStatus->internship_status_name ?? 'Potvrdená' }}</p>
                                </div>

                                <!-- Button -->
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 20px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ config('app.frontend_url') }}/internships" style="display: inline-block; padding: 12px 30px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Zobraziť prax</a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 15px 0 0 0;">S pozdravom,<br><strong>Systém odbornej praxe UKF</strong></p>
                            </div>

                            <!-- Footer -->
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
