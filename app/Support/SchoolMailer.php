<?php

namespace App\Support;

use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SchoolMailer
{
    public static function send(string $to, string $subject, string $htmlBody): void
    {
        if (self::usesSmtp()) {
            Mail::html($htmlBody, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address'),
                        config('mail.from.name'),
                    );
            });

            return;
        }

        if (self::usesGoogleAppsScript()) {
            if (! GoogleAppsScriptMailer::send($to, $subject, $htmlBody)) {
                throw new \RuntimeException('Google Apps Script mail relay failed.');
            }

            self::logSentEmail($to, $subject, $htmlBody);

            return;
        }

        throw new \RuntimeException(
            'Email is not configured. Set MAIL_* SMTP variables or GOOGLE_APPS_SCRIPT_URL on the server.'
        );
    }

    public static function sendMailable(string $to, Mailable $mailable): void
    {
        $subject = $mailable->envelope()->subject ?? '(no subject)';
        self::send($to, $subject, $mailable->render());
    }

    public static function usesSmtp(): bool
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array'], true)) {
            return false;
        }

        if ($mailer !== 'smtp') {
            return true;
        }

        $host = config('mail.mailers.smtp.host');
        $username = config('mail.mailers.smtp.username');
        $password = config('mail.mailers.smtp.password');
        $from = config('mail.from.address');

        return filled($host)
            && $host !== '127.0.0.1'
            && filled($username)
            && filled($password)
            && filled($from)
            && $from !== 'hello@example.com';
    }

    public static function usesGoogleAppsScript(): bool
    {
        return filled(config('school.google_apps_script_url'));
    }

    public static function canSend(): bool
    {
        return self::usesSmtp() || self::usesGoogleAppsScript();
    }

    private static function logSentEmail(string $to, string $subject, string $htmlBody): void
    {
        try {
            EmailLog::create([
                'recipient' => $to,
                'subject' => $subject,
                'content' => $htmlBody,
                'status' => 'sent',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not write email log: '.$e->getMessage());
        }
    }
}
