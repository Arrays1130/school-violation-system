<?php

namespace App\Notifications;

use App\Models\StudentCase;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class ParentViolationNotification extends Notification implements ShouldQueue
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
        $studentName = $this->case->student->full_name;
        $guardianName = $this->case->student->guardian_name ?: 'Parent/Guardian';
        $violationTitle = $this->case->violation->title;
        $date = $this->case->occurred_at->format('F j, Y g:i A');
        $sanction = $this->case->sanction ?: 'To be determined';

        return (new MailMessage)
            ->subject("School Notice: Violation Report for {$studentName}")
            ->greeting("Dear {$guardianName},")
            ->line('This is an automated notification from the I-Link CST Student Discipline Office.')
            ->line("We are writing to inform you that a violation has been recorded for your student, **{$studentName}**.")
            ->line('**Violation Details:**')
            ->line("- **Offense:** {$violationTitle}")
            ->line("- **Date/Time:** {$date}")
            ->line("- **Status:** {$this->case->status}")
            ->line("- **Sanction:** {$sanction}")
            ->line('Please contact the Guidance Office at (064) 229-8472 if you have questions.')
            ->line('Thank you for your cooperation.');
    }

    public static function smsText(StudentCase $case): string
    {
        $studentName = $case->student->full_name;
        $violationTitle = $case->violation->title;
        $sanction = $case->sanction ?: 'TBD';

        return "SVS Notice: Your student {$studentName} has a recorded violation: {$violationTitle}. Sanction: {$sanction}. Please contact the school.";
    }
}
