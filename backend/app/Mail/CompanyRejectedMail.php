<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $reason;

    public function __construct(Company $company, $reason = null)
    {
        $this->company = $company;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Registrácia zamietnutá - Systém odbornej praxe UKF')
                    ->view('emails.company.rejected');
    }
}
