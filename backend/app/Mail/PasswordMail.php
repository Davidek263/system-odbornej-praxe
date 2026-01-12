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
 * CLASS: PasswordMail
 * ===================================
 *
 * Mailable class for sending temporary password emails to users.
 *
 * This email is used when:
 * - A temporary password is generated for a user
 * - Password reset requires sending a new temporary password
 * - Administrative password reset is performed
 *
 * The email contains the temporary password which should be changed
 * by the user upon first login for security purposes.
 *
 * @package App\Mail
 * @author System
 */
class PasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * ===================================
     * PROPERTIES
     * ===================================
     */

    /**
     * The temporary password to send to the user
     *
     * @var string
     */
    public $password;

    /**
     * ===================================
     * CONSTRUCTOR
     * ===================================
     */

    /**
     * Create a new message instance.
     *
     * @param string $password The temporary password
     * @return void
     */
    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * ===================================
     * BUILD METHOD
     * ===================================
     */

    /**
     * Build the message.
     *
     * Constructs the temporary password email using a Blade view template.
     * The password is made available to the view automatically via the public property.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Vaše dočasné heslo')
                    ->view('emails.password');
    }
}