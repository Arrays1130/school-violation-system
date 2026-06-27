<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Hearing;

class HearingScheduled extends Notification
{
    use Queueable;

    public function __construct(public Hearing $hearing)
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->hearing->case->student->full_name;
        $date = $this->hearing->scheduled_at->format('F d, Y');
        $time = $this->hearing->scheduled_at->format('g:i A');

        return (new MailMessage)
            ->subject("Hearing Scheduled - I-Link CST")
            ->greeting("Dear $studentName,")
            ->line("A hearing has been scheduled regarding your violation case.")
            ->line("**Hearing Details:**")
            ->line("- **Date:** $date")
            ->line("- **Time:** $time")
            ->line("- **Venue:** {$this->hearing->venue}")
            ->when($this->hearing->notes, function ($mail) {
                return $mail->line("- **Notes:** {$this->hearing->notes}");
            })
            ->line('Your presence is required. Failure to appear may result in further sanctions.')
            ->line('Please contact the Guidance Office if you have any questions.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Hearing Scheduled',
            'message' => "A hearing has been scheduled for {$this->hearing->scheduled_at->format('F d, Y g:i A')}",
            'hearing_id' => $this->hearing->id,
            'case_id' => $this->hearing->case_id,
            'hearing_date' => $this->hearing->scheduled_at->format('F d, Y g:i A'),
            'hearing_venue' => $this->hearing->venue,
        ];
    }
}
