<?php

namespace App\Notifications;

use App\Models\SanctionAssignment;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class GsoSanctionCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public SanctionAssignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        $channels = NotificationChannels::withEmail(['database']);
        if (config('services.fcm.server_key')) {
            $channels[] = 'fcm';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $case = $this->assignment->studentCase;
        $student = $case?->student?->full_name ?? 'Student';
        $code = $case?->display_code ?? ('#'.$this->assignment->case_id);

        return (new MailMessage)
            ->subject('[OSA] GSO completed community service — '.$code)
            ->greeting('Hello '.$notifiable->name.',')
            ->line("GSO marked community service complete for {$student} ({$code}).")
            ->line(sprintf(
                'Hours served: %.2f / %.2f',
                $this->assignment->hoursServed(),
                $this->assignment->required_hours
            ))
            ->action('Open case', route('cases.show', $this->assignment->case_id))
            ->line('Proceed with the next OSA step when ready.');
    }

    public function toArray(object $notifiable): array
    {
        $case = $this->assignment->studentCase;
        $student = $case?->student?->full_name ?? 'Student';

        return [
            'title' => 'GSO completed — ready for OSA',
            'message' => "{$student}: community service hours completed and forwarded to OSA.",
            'case_id' => $this->assignment->case_id,
            'sanction_assignment_id' => $this->assignment->id,
            'type' => 'gso_sanction_completed',
            'url' => route('cases.show', $this->assignment->case_id),
        ];
    }
}
