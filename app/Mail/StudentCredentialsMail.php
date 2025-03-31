<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $studentEmail;
    public $personalEmail;
    public $password;
    public $major;
    public $departmentName;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $firstName,
        string $studentEmail,
        string $personalEmail,
        string $password,
        string $major,
        string $departmentName
    ) {
        $this->firstName = $firstName;
        $this->studentEmail = $studentEmail;
        $this->personalEmail = $personalEmail;
        $this->password = $password;
        $this->major = $major;
        $this->departmentName = $departmentName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your University Account Credentials',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'firstName' => $this->firstName,
                'studentEmail' => $this->studentEmail,
                'personalEmail' => $this->personalEmail,
                'password' => $this->password,
                'major' => $this->major,
                'departmentName' => $this->departmentName,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}