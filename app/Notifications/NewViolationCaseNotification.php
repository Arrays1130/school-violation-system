<?php

namespace App\Notifications;

use App\Models\StudentCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewViolationCaseNotification extends Notification implements ShouldBroadcast, ShouldQueue
{
    use Queueable;

    public $studentCase;

    /**
     * Create a new notification instance.
     */
    public function __construct(StudentCase $studentCase)
    {
        $this->studentCase = $studentCase;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'case_id' => $this->studentCase->id,
            'student_name' => $this->studentCase->student->full_name ?? 'Unknown',
            'department' => $this->studentCase->student->department ?? 'N/A',
            'violation_title' => $this->studentCase->violation->title ?? 'Unknown',
            'severity' => $this->studentCase->violation->severity ?? 'Minor',
            'message' => 'A new violation case was logged.',
            'url' => route('cases.show', $this->studentCase->id),
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'case_id' => $this->studentCase->id,
            'student_name' => $this->studentCase->student->full_name ?? 'Unknown',
            'department' => $this->studentCase->student->department ?? 'N/A',
            'violation_title' => $this->studentCase->violation->title ?? 'Unknown',
            'severity' => $this->studentCase->violation->severity ?? 'Minor',
            'message' => 'A new violation case was logged.',
            'url' => route('cases.show', $this->studentCase->id),
            // Include created_at so frontend can show timestamp
            'created_at' => now()->toISOString(),
        ]);
    }
}
