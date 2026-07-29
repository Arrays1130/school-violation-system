<?php

namespace App\Mail\Transport;

use App\Support\GoogleAppsScriptMailer;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GoogleAppsScriptTransport extends AbstractTransport
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $to = collect($email->getTo())
            ->map(fn ($address) => $address->getAddress())
            ->filter()
            ->values()
            ->all();

        if ($to === []) {
            throw new \RuntimeException('Google Apps Script mail requires at least one recipient.');
        }

        $subject = $email->getSubject() ?? '(no subject)';
        $body = $email->getHtmlBody() ?? nl2br(e($email->getTextBody() ?? ''));

        foreach ($to as $recipient) {
            if (! GoogleAppsScriptMailer::send($recipient, $subject, $body)) {
                throw new \RuntimeException('Google Apps Script mail relay failed for '.$recipient);
            }
        }
    }

    public function __toString(): string
    {
        return 'google_apps_script';
    }
}
