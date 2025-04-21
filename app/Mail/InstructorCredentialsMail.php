<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InstructorCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $instructorEmail,
        public string $personalEmail,
        public string $password,
        public string $departmentName,
        // public string $loginUrl,
        // public string $privacyPolicyUrl,
        // public string $supportUrl
    ) {
        $this->firstName = $firstName;
        $this->instructorEmail = $instructorEmail;
        $this->personalEmail = $personalEmail;
        $this->password = $password;
        $this->departmentName = $departmentName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Instructor Account Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.Instructorwelcome',
            with: [
                'firstName' => $this->firstName,
                'studentEmail' => $this->instructorEmail,
                'personalEmail' => $this->personalEmail,
                'password' => $this->password,
                'departmentName' => $this->departmentName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
