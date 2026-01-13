<?php

namespace App\Services;

use App\Models\User;
use App\Models\Internship;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    /**
     * Send email notification and log to database
     *
     * @param User|array $recipients Single user or array of users
     * @param \Illuminate\Mail\Mailable $mailable Mail class instance
     * @param string $type Notification type (e.g., 'internship_created', 'internship_confirmed')
     * @param mixed $relatedModel Related model (Internship, etc.)
     * @return bool Success status
     */
    public function sendEmail($recipients, $mailable, string $type, $relatedModel = null): bool
    {
        // Ensure recipients is an array
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        // Remove duplicate recipients based on email
        $uniqueRecipients = [];
        $seenEmails = [];

        foreach ($recipients as $recipient) {
            $email = $recipient->email ?? null;
            if ($email && !in_array($email, $seenEmails)) {
                $uniqueRecipients[] = $recipient;
                $seenEmails[] = $email;
            }
        }

        // Log recipients count for debugging
        Log::info("EmailNotificationService: Sending emails", [
            'type' => $type,
            'original_count' => count($recipients),
            'unique_count' => count($uniqueRecipients),
            'recipients_emails' => array_map(fn($r) => $r->email ?? 'unknown', $uniqueRecipients)
        ]);

        $allSuccess = true;

        foreach ($uniqueRecipients as $recipient) {
            if (!$recipient || !$recipient->email) {
                Log::warning("EmailNotificationService: Recipient has no email", [
                    'user_id' => $recipient->id ?? null,
                    'type' => $type
                ]);
                continue;
            }

            try {
                // Send email - clone mailable to avoid state issues
                $mailableClone = clone $mailable;
                Mail::to($recipient->email)->send($mailableClone);

                // Log successful send to database
                $this->logNotification(
                    user: $recipient,
                    type: $type,
                    subject: $mailable->subject ?? 'Notifikácia o zmene stavu praxe',
                    relatedModel: $relatedModel,
                    isSent: true
                );

                Log::info("Email sent successfully", [
                    'recipient' => $recipient->email,
                    'type' => $type
                ]);

            } catch (\Exception $e) {
                $allSuccess = false;

                // Log failed send to database
                $this->logNotification(
                    user: $recipient,
                    type: $type,
                    subject: $mailable->subject ?? 'Notifikácia o zmene stavu praxe',
                    relatedModel: $relatedModel,
                    isSent: false,
                    errorMessage: $e->getMessage()
                );

                Log::error("Failed to send email", [
                    'recipient' => $recipient->email,
                    'type' => $type,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $allSuccess;
    }

    /**
     * Log email notification to database
     */
    private function logNotification(
        $user,
        string $type,
        string $subject,
        $relatedModel = null,
        bool $isSent = true,
        ?string $errorMessage = null
    ): void {
        // Skip logging if recipient is not a User (e.g., company stdClass object)
        if (!($user instanceof User)) {
            Log::info("Skipping notification log for non-User recipient", [
                'recipient_email' => $user->email ?? 'unknown',
                'type' => $type
            ]);
            return;
        }

        $notificationData = [
            'user_id' => $user->id,
            'type' => $type,
            'subject' => $subject,
            'body' => '', // Can be populated if needed
            'is_sent' => $isSent,
            'sent_at' => $isSent ? now() : null,
            'error_message' => $errorMessage,
            'retry_count' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Add polymorphic relationship if model provided
        if ($relatedModel) {
            $notificationData['related_type'] = get_class($relatedModel);
            $notificationData['related_id'] = $relatedModel->id;
        }

        DB::table('email_notifications')->insert($notificationData);
    }

    /**
     * Get recipients for internship creation (company only)
     */
    public function getRecipientsForCreation(Internship $internship): array
    {
        $recipients = [];

        // Send to company
        if ($internship->company && $internship->company->contact_person_email) {
            $recipients[] = (object)[
                'id' => $internship->company->id,
                'email' => $internship->company->contact_person_email,
                'first_name' => $internship->company->contact_person_name,
                'last_name' => ''
            ];
        }

        return $recipients;
    }

    /**
     * Get recipients for internship confirmation (student + guarantor)
     */
    public function getRecipientsForConfirmation(Internship $internship): array
    {
        $recipients = [];

        // Send to student
        if ($internship->student) {
            $recipients[] = $internship->student;
        }

        // Send to all guarantors
        $guarantors = User::whereHas('role', function($query) {
            $query->where('role_name', 'guarantor');
        })->get();

        foreach ($guarantors as $guarantor) {
            $recipients[] = $guarantor;
        }

        return $recipients;
    }

    /**
     * Get recipients for rejection (student + company OR guarantor, but NOT who rejected)
     *
     * @param Internship $internship
     * @param int|null $rejectedByUserId User who rejected (null if company rejected)
     * @param bool $rejectedByCompany True if company rejected, false if user rejected
     */
    public function getRecipientsForRejection(Internship $internship, ?int $rejectedByUserId, bool $rejectedByCompany = false): array
    {
        $recipients = [];

        // Send to student (always, unless student rejected it themselves)
        if ($internship->student && $internship->student->id !== $rejectedByUserId) {
            $recipients[] = $internship->student;
        }

        // Send to company ONLY if company did NOT reject it
        if (!$rejectedByCompany && $internship->company && $internship->company->contact_person_email) {
            $recipients[] = (object)[
                'id' => $internship->company->id,
                'email' => $internship->company->contact_person_email,
                'first_name' => $internship->company->contact_person_name,
                'last_name' => ''
            ];
        }

        // Send to all guarantors EXCEPT the one who rejected
        $guarantors = User::whereHas('role', function($query) {
            $query->where('role_name', 'guarantor');
        })->get();

        foreach ($guarantors as $guarantor) {
            if ($guarantor->id !== $rejectedByUserId) {
                $recipients[] = $guarantor;
            }
        }

        return $recipients;
    }

    /**
     * Get recipients for approval (student + company)
     */
    public function getRecipientsForApproval(Internship $internship): array
    {
        $recipients = [];

        // Send to student
        if ($internship->student) {
            $recipients[] = $internship->student;
        }

        // Send to company
        if ($internship->company && $internship->company->contact_person_email) {
            $recipients[] = (object)[
                'id' => $internship->company->id,
                'email' => $internship->company->contact_person_email,
                'first_name' => $internship->company->contact_person_name,
                'last_name' => ''
            ];
        }

        return $recipients;
    }

    /**
     * Get recipients for defended status (student only)
     */
    public function getRecipientsForDefended(Internship $internship): array
    {
        $recipients = [];

        // Send to student only
        if ($internship->student) {
            $recipients[] = $internship->student;
        }

        return $recipients;
    }

    /**
     * Get recipients for timesheet actions (student only)
     */
    public function getRecipientsForTimesheet(Internship $internship): array
    {
        $recipients = [];

        // Send to student only
        if ($internship->student) {
            $recipients[] = $internship->student;
        }

        return $recipients;
    }

    /**
     * Get recipients for document upload (company + guarantors)
     */
    public function getRecipientsForDocumentUpload(Internship $internship, $document = null): array
    {
        $recipients = [];

        // Send to company
        if ($internship->company && $internship->company->contact_person_email) {
            $recipients[] = (object)[
                'id' => $internship->company->id,
                'email' => $internship->company->contact_person_email,
                'first_name' => $internship->company->contact_person_name,
                'last_name' => ''
            ];
        }

        // Send to all guarantors
        $guarantors = User::whereHas('role', function($query) {
            $query->where('role_name', 'guarantor');
        })->get();

        foreach ($guarantors as $guarantor) {
            $recipients[] = $guarantor;
        }

        return $recipients;
    }
}
