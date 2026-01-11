<?php

namespace App\Mail;

use App\Models\Internship;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimesheetRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $document;
    public $notes;

    /**
     * Create a new message instance.
     */
    public function __construct(Internship $internship, Document $document = null, $notes = null)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->document = $document;
        $this->notes = $notes;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Výkaz hodín zamietnutý')
                    ->view('emails.internship.timesheet_rejected');
    }
}
