<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $studentId;
    public $message;
    public $courseId;
    public $instructorId;

    public function __construct($studentId, $message, $courseId, $instructorId)
    {
        $this->studentId = $studentId;
        $this->message = $message;
        $this->courseId = $courseId;
        $this->instructorId = $instructorId;
    }

    public function broadcastOn()
    {
        return new Channel('student.' . $this->studentId);
    }

    public function broadcastAs()
    {
        return 'student-notification';
    }
}
