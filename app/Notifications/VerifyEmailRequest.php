<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 1/22/20
 * Time: 8:27 AM
 */

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyEmailRequest extends VerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $prefix = config('frontend.url') . config('frontend.email_verify_url');
        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify', Carbon::now()->addMinutes(30), ['id' => $notifiable->getKey()]
        );

        return $prefix . urlencode($temporarySignedURL);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return (new MailMessage)
            ->subject(trans("mail.staff.verify"))
            ->view('emails.register.staff.verify',
                [
                    'verificationUrl' => $verificationUrl
                ]);
    }
}