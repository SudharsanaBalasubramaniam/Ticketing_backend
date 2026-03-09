<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    /**
     * Create a new message instance.
     *
     * @param string $ddd
     * @return void
     */
    public $ddd;

     public function __construct($ddd)
    {
        $this->user = $ddd;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
 
    public function build()
    {
        $subject = "Reset Password jkjkjkj";
        return $this->subject($subject)->view('adminscore_mail', ['result' => $subject,'data'=>$this->user]);
    }

}