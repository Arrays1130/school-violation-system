<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\StudentCase;

class ViolationRecorded extends Notification
{
    use Queueable;

    public function __construct(public StudentCase $case)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->case->student->full_name;
        $violationTitle = $this->case->violation->title;
        $date = $this->case->occurred_at->format('F j, Y g:i A');

        return (new MailMessage)
            ->subject("URGENT: Violation Report for $studentName")
            ->greeting("Dear $studentName,")
            ->line("This is an automated notification from the I-Link CST Student Discipline Office.")
            ->line("We regret to inform you that a violation has been recorded in your file.")
            ->line("**Violation Details:**")
            ->line("- **Offense:** $violationTitle")
            ->line("- **Date/Time:** $date")
            ->line("- **Status:** {$this->case->status}")
            ->line('Please contact the Guidance Office immediately at (064) 229-8472 to discuss this matter.')
            ->line('Thank you for your cooperation.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Violation Recorded',
            'message' => "A violation has been recorded: {$this->case->violation->title}",
            'case_id' => $this->case->id,
            'violation' => $this->case->violation->title,
            'occurred_at' => $this->case->occurred_at->format('F j, Y g:i A'),
        ];
    }
}
