<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyApprovedWithCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $temporaryPassword;
    public $activationUrl;

    public function __construct(Company $company, $temporaryPassword, $activationToken)
    {
        $this->company = $company;
        $this->temporaryPassword = $temporaryPassword;
        $this->activationUrl = url('/api/activate-account?token=' . $activationToken);
    }

    public function build()
    {
        return $this->subject('Registrácia schválená - Aktivujte svoj účet')
                    ->view('emails.company.approved_with_credentials');
    }
}
