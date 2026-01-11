@component('mail::message')
# Vitajte, {{ $name }} 🎉

Pre aktiváciu účtu kliknite na tlačidlo nižšie. Po aktivácii budete automaticky presmerovaní na stránku pre nastavenie hesla:

@component('mail::button', ['url' => $activationUrl])
Aktivovať účet a nastaviť heslo
@endcomponent

Ak ste si účet nevytvorili vy, ignorujte tento email.

S pozdravom,<br>
{{ config('app.name') }}
@endcomponent
