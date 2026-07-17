<?php

namespace App\Notifications;

use App\Models\Hearing;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParentHearingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Hearing $hearing,
        public bool $isUpdate = false,
    ) {
        //
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return NotificationChannels::withEmail([]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $student = $this->hearing->case->student;
        $studentName = $student->full_name;
        $guardianName = $student->guardian_name ?: 'Parent/Guardian';
        $date = $this->hearing->scheduled_at->format('F d, Y');
        $time = $this->hearing->scheduled_at->format('g:i A');
        $subjectPrefix = $this->isUpdate ? 'Hearing Rescheduled' : 'Hearing Scheduled';
        $intro = $this->isUpdate
            ? "A hearing for your student, **{$studentName}**, has been rescheduled."
            : "A hearing has been scheduled for your student, **{$studentName}**.";

        return (new MailMessage)
            ->subject("{$subjectPrefix} - {$studentName} | I-Link CST")
            ->greeting("Dear {$guardianName},")
            ->line($intro)
            ->line('**Hearing Details:**')
            ->line("- **Date:** {$date}")
            ->line("- **Time:** {$time}")
            ->line("- **Venue:** {$this->hearing->venue}")
            ->line("- **Violation:** {$this->hearing->case->violation->title}")
            ->when($this->hearing->notes, function ($mail) {
                return $mail->line("- **Notes:** {$this->hearing->notes}");
            })
            ->line('Your presence is required. Please contact the Guidance Office if you have questions.');
    }

    public static function smsText(Hearing $hearing, bool $isUpdate = false): string
    {
        $studentName = $hearing->case->student->full_name;
        $formattedDate = $hearing->scheduled_at->format('F j, Y g:i A');
        $prefix = $isUpdate ? 'rescheduled' : 'scheduled';

        return "SVS Notice: A hearing is {$prefix} for your student {$studentName} regarding {$hearing->case->violation->title} on {$formattedDate} at {$hearing->venue}. Please be present.";
    }
}
