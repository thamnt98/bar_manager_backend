<?php

namespace App\Mail;

use App\Models\RegisterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $registerMail;

    /**
     * Create a new message instance.
     *
     * @param RegisterMail $registerMail
     */
    public function __construct(RegisterMail $registerMail)
    {
        $this->registerMail = $registerMail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.register.mail')
                    ->subject(trans("mail.register.request"))
                    ->with([
                        'url' => env('FRONTEND_URL', 'http://localhost'),
                        'code' => $this->registerMail->generated_code
                    ]);
    }
}
