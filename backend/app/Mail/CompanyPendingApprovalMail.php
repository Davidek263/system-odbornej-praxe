<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyPendingApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $company;
    public $user;
    public $address;

    public function __construct(Company $company, User $user)
    {
        $this->company = $company;
        $this->user = $user;
        $this->address = $company->address;
    }

    public function build()
    {
        return $this->subject('Nová firma čaká na schválenie')
                    ->view('emails.company.pending_approval');
    }
}
