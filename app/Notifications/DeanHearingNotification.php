<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Hearing;

class DeanHearingNotification extends Notification
{
    use Queueable;

    public function __construct(public Hearing $hearing)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->hearing->case->student->full_name;
        $department = $this->hearing->case->student->department;
        $date = $this->hearing->scheduled_at->format('F d, Y');
        $time = $this->hearing->scheduled_at->format('g:i A');

        return (new MailMessage)
            ->subject("[ALL DEANS ALERT] Hearing Scheduled: $studentName ($department)")
            ->greeting("Dear Dean,")
            ->line("An official hearing has been scheduled for a student violation.")
            ->line("**Case Information:**")
            ->line("- **Student:** $studentName")
            ->line("- **Department:** $department")
            ->line("- **Violation:** {$this->hearing->case->violation->title}")
            ->line("**Hearing Details:**")
            ->line("- **Date:** $date")
            ->line("- **Time:** $time")
            ->line("- **Venue:** {$this->hearing->venue}")
            ->when($this->hearing->notes, function ($mail) {
                return $mail->line("- **Notes:** {$this->hearing->notes}");
            })
            ->line('This notification is sent to all Deans for awareness of ongoing disciplinary hearings.')
            ->action('View Hearing Details', route('hearings.show', $this->hearing->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Hearing Scheduled (System-wide)',
            'message' => "A hearing has been scheduled for {$this->hearing->case->student->full_name} ({$this->hearing->case->student->department}).",
            'case_id' => $this->hearing->case_id,
            'hearing_id' => $this->hearing->id,
            'venue' => $this->hearing->venue,
            'schedule' => $this->hearing->scheduled_at->format('F d, Y - g:i A'),
            'student_name' => $this->hearing->case->student->full_name,
            'department' => $this->hearing->case->student->department,
        ];
    }
}
