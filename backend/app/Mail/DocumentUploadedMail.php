<?php

namespace App\Mail;

use App\Models\Internship;
use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $internship;
    public $student;
    public $company;
    public $document;

    public function __construct(Internship $internship, Document $document)
    {
        $this->internship = $internship;
        $this->student = $internship->student;
        $this->company = $internship->company;
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('Nový dokument nahraný študentom')
                    ->view('emails.internship.document_uploaded');
    }
}
