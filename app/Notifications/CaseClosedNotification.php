<?php

namespace App\Notifications;

use App\Models\StudentCase;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class CaseClosedNotification extends Notification implements ShouldQueue
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
        return NotificationChannels::withEmail(['database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->case->student->full_name;
        $violationTitle = $this->case->violation->title;
        $sanction = $this->case->sanction ?: 'None recorded';

        return (new MailMessage)
            ->subject("Case Closed - {$violationTitle} | I-Link CST")
            ->greeting("Dear {$studentName},")
            ->line('Your violation case has been officially closed.')
            ->line('**Case Details:**')
            ->line("- **Offense:** {$violationTitle}")
            ->line("- **Sanction:** {$sanction}")
            ->line("- **Closed:** ".($this->case->closed_at?->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A')))
            ->line('Please contact the Guidance Office if you have any questions.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Case Closed',
            'message' => "Your case for {$this->case->violation->title} has been closed.",
            'case_id' => $this->case->id,
            'violation' => $this->case->violation->title,
            'sanction' => $this->case->sanction,
        ];
    }

    public static function smsText(StudentCase $case): string
    {
        $studentName = explode(' ', trim($case->student->full_name))[0];
        $violationTitle = $case->violation->title;

        return "I-Link CST: Hi {$studentName}, your case for {$violationTitle} has been closed. Sanction: ".($case->sanction ?: 'N/A').'.';
    }
}
