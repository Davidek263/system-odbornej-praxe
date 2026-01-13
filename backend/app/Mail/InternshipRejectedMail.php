<?php

namespace App\Mail;

use App\Models\Internship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternshipRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $company;
    public $rejectedBy;
    public $notes;

    /**
     * Create a new message instance.
     */
    public function __construct(Internship $internship, $rejectedBy = null, $notes = null)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->company = $internship->company;
        $this->rejectedBy = $rejectedBy;
        $this->notes = $notes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Odborná prax zamietnutá')
                    ->view('emails.internship.rejected');
    }
}
