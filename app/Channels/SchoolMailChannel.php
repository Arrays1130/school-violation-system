<?php

namespace App\Channels;

use App\Support\SchoolMailer;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SchoolMailChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toMail')) {
            return;
        }

        $email = $notifiable->routeNotificationFor('school_mail', $notification)
            ?? $notifiable->routeNotificationFor('mail', $notification);

        if (! $email) {
            return;
        }

        if (! SchoolMailer::canSend()) {
            Log::warning('School mail skipped: no mail transport configured.', [
                'recipient' => $email,
                'notification' => $notification::class,
            ]);

            return;
        }

        try {
            $message = $notification->toMail($notifiable);
            $subject = $message->subject ?? config('app.name').' Notification';

            SchoolMailer::send($email, $subject, $message->render());
        } catch (\Throwable $e) {
            Log::error('School mail notification failed: '.$e->getMessage(), [
                'recipient' => $email,
                'notification' => $notification::class,
            ]);
            report($e);
        }
    }
}
