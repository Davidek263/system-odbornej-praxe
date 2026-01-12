<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InternshipStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $oldStatus;
    public $newStatus;
    public $changedBy;
    public $notes;

    public function __construct($internship, $oldStatus, $newStatus, $changedBy, $notes = null)
    {
        $this->internship = $internship;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->changedBy = $changedBy;
        $this->notes = $notes;
    }

    public function build()
    {
        return $this->subject('Zmena stavu odbornej praxe')
                    ->view('emails.internship_status_changed')
                    ->with([
                        'studentName' => $this->internship->student->first_name . ' ' . $this->internship->student->last_name,
                        'companyName' => $this->internship->company->company_name,
                        'oldStatus' => $this->oldStatus,
                        'newStatus' => $this->newStatus,
                        'changedBy' => $this->changedBy,
                        'notes' => $this->notes,
                        'academicYear' => $this->internship->academic_year,
                        'semester' => $this->internship->semester == 1 ? 'Zimný' : 'Letný',
                    ]);
    }
}
