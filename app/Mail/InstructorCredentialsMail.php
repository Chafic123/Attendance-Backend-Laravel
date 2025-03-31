<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InstructorCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $instructorEmail;
    public $personalEmail;
    public $password;
    public $departmentName;

    public function __construct($firstName, $instructorEmail, $personalEmail, $password, $departmentName)
    {
        $this->firstName = $firstName;
        $this->instructorEmail = $instructorEmail;
        $this->personalEmail = $personalEmail;
        $this->password = $password;
        $this->departmentName = $departmentName;
    }

    public function build()
    {
        return $this->subject('Instructor Credentials')
                    ->view('emails.Instructor_credentials')
                    ->with([
                        'firstName' => $this->firstName,
                        'instructorEmail' => $this->instructorEmail,
                        'personalEmail' => $this->personalEmail,
                        'password' => $this->password,
                        'departmentName' => $this->departmentName,
                    ]);
    }
    public function attachments(): array
    {
        return [];
    }
}
