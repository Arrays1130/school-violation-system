<?php

namespace App\Notifications\Channels;

use App\Services\FcmPushService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(private FcmPushService $fcm) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (empty($notifiable->fcm_token)) {
            return;
        }

        $data = $notification->toArray($notifiable);
        $title = $data['title'] ?? 'VioTrack Alert';
        $body = $data['message'] ?? 'You have a new update.';

        $payload = [];
        if (isset($data['case_id'])) {
            $payload['case_id'] = (string) $data['case_id'];
        }
        if (isset($data['hearing_id'])) {
            $payload['hearing_id'] = (string) $data['hearing_id'];
        }

        $badge = null;
        if (method_exists($notifiable, 'unreadNotifications')) {
            $badge = (int) $notifiable->unreadNotifications()->count();
        }

        $this->fcm->send($notifiable->fcm_token, $title, $body, $payload, $badge);
    }
}
