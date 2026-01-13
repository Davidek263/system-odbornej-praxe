<?php

namespace App\Mail;

use App\Models\Internship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternshipCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $company;

    public function __construct(Internship $internship)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->company = $internship->company;
    }

    public function build()
    {
        return $this->subject('Nová žiadosť o odbornú prax')
                    ->view('emails.internship.created');
    }
}
