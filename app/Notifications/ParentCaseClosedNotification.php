<?php

namespace App\Notifications;

use App\Models\StudentCase;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ParentCaseClosedNotification extends Notification implements ShouldQueue
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
        return NotificationChannels::withEmail([]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->case->student;
        $studentName = $student->full_name;
        $guardianName = $student->guardian_name ?: 'Parent/Guardian';
        $violationTitle = $this->case->violation->title;
        $sanction = $this->case->sanction ?: 'None recorded';

        return (new MailMessage)
            ->subject("Case Closed for {$studentName} | I-Link CST")
            ->greeting("Dear {$guardianName},")
            ->line("The violation case for your student, **{$studentName}**, has been officially closed.")
            ->line('**Case Details:**')
            ->line("- **Offense:** {$violationTitle}")
            ->line("- **Sanction:** {$sanction}")
            ->line("- **Closed:** ".($this->case->closed_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A')))
            ->line('Please contact the Guidance Office at (064) 229-8472 if you have questions.');
    }

    public static function smsText(StudentCase $case): string
    {
        $studentName = $case->student->full_name;
        $violationTitle = $case->violation->title;
        $sanction = $case->sanction ?: 'N/A';

        return "SVS Notice: The case for your student {$studentName} ({$violationTitle}) has been closed. Sanction: {$sanction}.";
    }
}
