<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Potvrdenie zmeny emailu</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #42b883 0%, #2c3e50 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
            <h1 style="color: white; margin: 0;">Potvrdenie zmeny emailu</h1>
        </div>

        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px;">
            <p>Dobrý deň {{ $name }},</p>

            <p>Dostali sme žiadosť o zmenu vášho @if($emailType === 'student') študentského @else firemného @endif emailu:</p>

            <div style="background: white; padding: 15px; border-left: 4px solid #42b883; margin: 20px 0;">
                <strong>Starý email:</strong> {{ $oldEmail }}<br>
                <strong>Nový email:</strong> {{ $newEmail }}
            </div>

            <p><strong>Ak ste túto zmenu nevyžiadali vy, ignorujte tento email.</strong> Váš email nebude zmenený.</p>

            <p>Pre potvrdenie zmeny kliknite na tlačidlo nižšie:</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $verificationUrl }}"
                   style="background: #42b883; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                    Potvrdiť zmenu emailu
                </a>
            </div>

            <p style="font-size: 12px; color: #666;">Ak tlačidlo nefunguje, skopírujte tento odkaz do prehliadača:<br>
            <a href="{{ $verificationUrl }}" style="color: #42b883; word-break: break-all;">{{ $verificationUrl }}</a></p>

            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 6px; margin-top: 20px;">
                <strong>⚠️ Dôležité:</strong> Tento odkaz je platný 24 hodín.
            </div>

            <p style="margin-top: 30px; font-size: 14px; color: #666;">
                S pozdravom,<br>
                Systém odbornej praxe
            </p>
        </div>
    </div>
</body>
</html>
