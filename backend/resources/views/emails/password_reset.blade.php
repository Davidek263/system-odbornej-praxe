<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obnovenie hesla</title>
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
                                <h1 style="margin: 0; color: white;">🔑 Obnovenie hesla</h1>
                            </div>

                            <!-- Content -->
                            <div style="background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                                <p style="margin: 0 0 15px 0;">Dobrý deň,</p>

                                <p style="margin: 0 0 15px 0;">Dostali sme požiadavku na obnovenie hesla pre váš účet v systéme odbornej praxe.</p>

                                <!-- Info Box -->
                                <div style="background-color: #e3f2fd; padding: 15px; margin: 15px 0; border-left: 4px solid #2196F3;">
                                    <p style="margin: 0 0 10px 0;"><strong>Ako nastaviť nové heslo:</strong></p>
                                    <ol style="margin: 0; padding-left: 20px;">
                                        <li style="margin: 5px 0;">Kliknite na tlačidlo nižšie</li>
                                        <li style="margin: 5px 0;">Zadajte nové heslo (minimálne 8 znakov)</li>
                                        <li style="margin: 5px 0;">Potvrďte nové heslo</li>
                                    </ol>
                                </div>

                                <!-- Button -->
                                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin: 20px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ $url }}" style="display: inline-block; padding: 12px 30px; background-color: #2196F3; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Obnoviť heslo</a>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Warning Box -->
                                <div style="background-color: #fff3e0; padding: 15px; margin: 15px 0; border-left: 4px solid #FF9800;">
                                    <p style="margin: 0;"><strong>⏱️ Dôležité:</strong> Tento link je platný <strong>24 hodín</strong>. Po uplynutí tejto doby budete musieť požiadať o nový link na obnovenie hesla.</p>
                                </div>

                                <!-- Alternative Link -->
                                <div style="background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #2196F3;">
                                    <h3 style="margin: 0 0 10px 0; color: #333;">Problém s tlačidlom?</h3>
                                    <p style="margin: 0 0 10px 0;">Ak nefunguje tlačidlo vyššie, skopírujte a vložte tento link do prehliadača:</p>
                                    <p style="word-break: break-all; background: #f5f5f5; padding: 10px; font-size: 12px; margin: 0;">{{ $url }}</p>
                                </div>

                                <!-- Security Notice -->
                                <p style="background-color: #ffebee; padding: 15px; border-left: 4px solid #f44336; margin: 15px 0;">
                                    <strong>🔒 Bezpečnostné upozornenie:</strong> Ak ste o obnovenie hesla nežiadali, <strong>ignorujte tento email</strong>. Vaše heslo zostane nezmenené a nikto iný k nemu nebude mať prístup.
                                </p>

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
