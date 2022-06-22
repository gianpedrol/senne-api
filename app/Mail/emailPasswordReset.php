<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class emailPasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    private $user;
    private $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $url)
    {
        $this->user = $user;
        $this->url = $url;

        //dd($this->url);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Alteração de senha');
        $this->to($this->user->email, $this->user->name);
        return $this->markdown('mail.emailResetPassword')->with(['user' => $this->user, 'url' => $this->url]);
    }
}
