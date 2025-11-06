@component('mail::message')
# Welcome, {{ $name }} 🎉

To activate your account, click the button below:

@component('mail::button', ['url' => $activationUrl])
Activate My Account
@endcomponent

If you did not create this account, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
