<?php

namespace App\Mail;

use App\Models\Internship;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TimesheetApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $document;

    /**
     * Create a new message instance.
     */
    public function __construct(Internship $internship, Document $document = null)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->document = $document;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Výkaz hodín schválený')
                    ->view('emails.internship.timesheet_approved');
    }
}
