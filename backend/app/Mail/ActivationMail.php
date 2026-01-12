<?php

namespace App\Mail;

/**
 * ===================================
 * IMPORTS
 * ===================================
 */
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * ===================================
 * CLASS: ActivationMail
 * ===================================
 *
 * Mailable class for sending account activation emails to newly registered users.
 *
 * This email contains:
 * - User's first name for personalization
 * - Activation link that redirects to the password setup page
 *
 * The activation URL points to the Laravel API endpoint which validates the token
 * and redirects to the Vue frontend for password setup.
 *
 * @package App\Mail
 * @author System
 */
class ActivationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * ===================================
     * PROPERTIES
     * ===================================
     */

    /**
     * The user being activated
     *
     * @var mixed
     */
    public $user;

    /**
     * The activation token for account verification
     *
     * @var string
     */
    public $activationToken;

    /**
     * ===================================
     * CONSTRUCTOR
     * ===================================
     */

    /**
     * Create a new message instance.
     *
     * @param mixed $user The user object containing user details
     * @param string $activationToken The unique activation token
     * @return void
     */
    public function __construct($user, $activationToken)
    {
        $this->user = $user;
        $this->activationToken = $activationToken;
    }

    /**
     * ===================================
     * BUILD METHOD
     * ===================================
     */

    /**
     * Build the message.
     *
     * Constructs the activation email with a link to the API endpoint.
     * The URL format: /api/activate-account?token={token}
     * This triggers the backend validation and redirects to the frontend password setup page.
     *
     * @return $this
     */
    public function build()
    {
        // Generate activation URL pointing to the API endpoint
        $activationUrl = url('/api/activate-account?token=' . $this->activationToken);

        return $this->subject('Activate Your Account')
                    ->markdown('emails.activation')
                    ->with([
                        'name' => $this->user->first_name,
                        'activationUrl' => $activationUrl,
                    ]);
    }
}
