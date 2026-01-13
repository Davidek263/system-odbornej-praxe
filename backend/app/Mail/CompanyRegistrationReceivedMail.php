<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    public function build()
    {
        return $this->subject('Registrácia prijatá - čaká na schválenie')
                    ->view('emails.company.registration_received');
    }
}
