<?php

namespace App\Channels;

use App\Support\SmsGateway;
use Illuminate\Notifications\Notification;

class SmsChannel
{
    /**
     * Send the given notification via the configured SMS gateway.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (! method_exists($notifiable, 'routeNotificationForSms')) {
            return;
        }

        $to = $notifiable->routeNotificationForSms($notification);

        if (! $to || ! method_exists($notification, 'toSms')) {
            return;
        }

        $message = $notification->toSms($notifiable);

        if (! $message) {
            return;
        }

        SmsGateway::dispatch($to, $message);
    }
}
