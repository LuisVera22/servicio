<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordMail extends Mailable
{
    use Queueable,SerializesModels;

    public $username;
    public $newpassword;

    public function __construct($username,$newpassword)
    {
        $this->username    = $username;
        $this->newpassword = $newpassword;
    }

    public function build()
    {
        return $this->view('emails.email')->subject('RBCLab - Cambio de contraseña');
    }
}