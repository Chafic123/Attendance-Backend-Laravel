<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
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
    public $studentId;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $firstName,
        string $studentEmail,
        string $personalEmail,
        string $password,
        string $major,
        string $departmentName,
        string $student_id
        ) {
        $this->firstName = $firstName;
        $this->studentEmail = $studentEmail;
        $this->personalEmail = $personalEmail;
        $this->password = $password;
        $this->major = $major;
        $this->departmentName = $departmentName;
        $this->studentId = $student_id;
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
            view: 'emails.Studentwelcome',
            with: [
                'firstName' => $this->firstName,
                'studentEmail' => $this->studentEmail,
                'personalEmail' => $this->personalEmail,
                'password' => $this->password,
                'major' => $this->major,
                'departmentName' => $this->departmentName,
                'studentId' => $this->studentId,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}