<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prax zamietnutá</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px;">
                    <tr>
                        <td>
                            <div style="background-color: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                                <h1 style="margin: 0;">✗ Prax zamietnutá</h1>
                            </div>

                            <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                                <p style="margin: 0 0 15px 0;">Dobrý deň,</p>

                                <p style="margin: 0 0 15px 0;">Oznamujeme Vám, že odborná prax bola <strong>zamietnutá</strong>.</p>

                                <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #f44336;">
                                    <h3 style="margin-top: 0;">Detaily praxe:</h3>
                                    <ul style="margin: 10px 0; padding-left: 20px;">
                                        <li><strong>Študent:</strong> {{ $student->first_name }} {{ $student->last_name }}</li>
                                        <li><strong>Firma:</strong> {{ $company->company_name }}</li>
                                        <li><strong>Akademický rok:</strong> {{ $internship->academic_year }}</li>
                                        <li><strong>Semester:</strong> {{ $internship->semester }}</li>
                                    </ul>

                                    @if($rejectedBy)
                                    <p style="margin: 10px 0;"><strong>Zamietnuté používateľom:</strong> {{ $rejectedBy->first_name }} {{ $rejectedBy->last_name }}</p>
                                    @endif

                                    <p style="margin-bottom: 0;"><strong>Aktuálny stav:</strong> Zamietnutá</p>
                                </div>

                                @if($notes)
                                <div style="background-color: #ffebee; padding: 15px; margin: 15px 0; border-left: 4px solid #f44336;">
                                    <h3 style="margin-top: 0;">Dôvod zamietnutia:</h3>
                                    <p style="margin-bottom: 0;">{{ $notes }}</p>
                                </div>
                                @endif

                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td align="center" style="padding: 20px 0;">
                                            <a href="{{ config('app.frontend_url') }}/internships" style="display: inline-block; padding: 12px 30px; background-color: #f44336; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Zobraziť prax</a>
                                        </td>
                                    </tr>
                                </table>

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
