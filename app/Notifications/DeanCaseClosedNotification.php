<?php

namespace App\Notifications;

use App\Models\StudentCase;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DeanCaseClosedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public StudentCase $case)
    {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        $channels = NotificationChannels::withEmail(['database']);
        if (config('services.fcm.server_key')) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->case->student?->full_name ?? 'Unknown Student';
        $department = $this->case->student?->department ?? 'N/A';
        $violationTitle = $this->case->violation?->title ?? 'Unknown Violation';
        $sanction = $this->case->sanction ?: 'None recorded';

        return (new MailMessage)
            ->subject("[DEAN NOTICE] Case Closed: {$studentName} ({$department})")
            ->greeting('Dear Dean,')
            ->line('A violation case in your department has been officially closed.')
            ->line('**Student:** '.$studentName)
            ->line('**Offense:** '.$violationTitle)
            ->line('**Sanction:** '.$sanction)
            ->line('**Closed:** '.($this->case->closed_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A')))
            ->action('View Case', route('cases.show', $this->case->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $studentName = $this->case->student?->full_name ?? 'Unknown Student';

        return [
            'title' => 'Case Closed',
            'message' => "Case for {$studentName} ({$this->case->violation?->title}) has been closed.",
            'case_id' => $this->case->id,
            'student_name' => $studentName,
            'violation_title' => $this->case->violation?->title,
        ];
    }
}
