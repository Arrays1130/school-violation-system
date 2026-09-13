<?php

namespace App\Notifications;

use App\Models\SanctionAssignment;
use App\Support\NotificationChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class GsoSanctionAssignedNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('[GSO] New community service assignment')
            ->greeting('Hello '.$notifiable->name.',')
            ->line("{$student} was assigned {$this->assignment->required_hours} hour(s) of community service.")
            ->line('Monitor their DTR in the GSO mobile app, then mark Complete when requirements are met.');
    }

    public function toArray(object $notifiable): array
    {
        $case = $this->assignment->studentCase;
        $student = $case?->student?->full_name ?? 'Student';

        return [
            'title' => 'New GSO sanction assignment',
            'message' => "{$student} needs {$this->assignment->required_hours} service hour(s).",
            'case_id' => $this->assignment->case_id,
            'sanction_assignment_id' => $this->assignment->id,
            'type' => 'gso_sanction_assigned',
        ];
    }
}
