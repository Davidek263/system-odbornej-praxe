<?php

namespace App\Mail;

use App\Models\Internship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternshipConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $company;

    /**
     * Create a new message instance.
     */
    public function __construct(Internship $internship)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->company = $internship->company;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Odborná prax potvrdená firmou')
                    ->view('emails.internship.confirmed');
    }
}
