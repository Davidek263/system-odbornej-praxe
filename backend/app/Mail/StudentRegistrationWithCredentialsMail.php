<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentRegistrationWithCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $temporaryPassword;
    public $activationUrl;

    public function __construct(User $user, $temporaryPassword, $activationToken)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->activationUrl = url('/api/activate-account?token=' . $activationToken);
    }

    public function build()
    {
        return $this->subject('Vitajte v systéme odbornej praxe - Aktivujte svoj účet')
                    ->view('emails.student.registration_with_credentials');
    }
}
