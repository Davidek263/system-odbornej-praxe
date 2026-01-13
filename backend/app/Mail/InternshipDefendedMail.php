<?php

namespace App\Mail;

use App\Models\Internship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternshipDefendedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;

    /**
     * Create a new message instance.
     */
    public function __construct(Internship $internship)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Odborná prax úspešne obhájená')
                    ->view('emails.internship.defended');
    }
}
