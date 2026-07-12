<?php

namespace App\Support;

class NotificationChannels
{
    /**
     * @param  array<int, string>  $channels
     * @return array<int, string>
     */
    public static function withEmail(array $channels = ['database']): array
    {
        if (SchoolMailer::canSend()) {
            $channels[] = 'school_mail';
        }

        return $channels;
    }
}
