<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;

class LogSentEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $message = $event->message;

            $recipients = [];
            foreach ($message->getTo() as $address) {
                $recipients[] = $address->getAddress();
            }

            \App\Models\EmailLog::create([
                'recipient' => implode(', ', $recipients),
                'subject' => $message->getSubject() ?? '(no subject)',
                'content' => $message->getHtmlBody() ?: $message->getTextBody(),
                'status' => 'sent',
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
