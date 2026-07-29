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
        // Prefer Google Apps Script on hosts that block SMTP (Render free).
        // Same Gmail account — no Resend/SendGrid.
        if (self::usesGoogleAppsScript()) {
            if (! GoogleAppsScriptMailer::send($to, $subject, $htmlBody)) {
                throw new \RuntimeException('Google Apps Script mail relay failed.');
            }

            self::logSentEmail($to, $subject, $htmlBody);

            return;
        }

        if (self::usesSmtp()) {
            Mail::html($htmlBody, function ($message) use ($to, $subject) {
                $message->to($to)
                    ->subject($subject)
                    ->from(
                        config('mail.from.address'),
                        config('mail.from.name'),
                    );
            });

            self::logSentEmail($to, $subject, $htmlBody);

            return;
        }

        throw new \RuntimeException(
            'Email is not configured. Set GOOGLE_APPS_SCRIPT_URL (recommended on Render free) or MAIL_* SMTP variables.'
        );
    }

    public static function sendMailable(string $to, Mailable $mailable): void
    {
        if (self::usesGoogleAppsScript()) {
            $subject = $mailable->envelope()->subject ?? '(no subject)';
            self::send($to, $subject, $mailable->render());

            return;
        }

        if (self::usesSmtp()) {
            Mail::to($to)->send($mailable);
            self::logSentEmail(
                $to,
                $mailable->envelope()->subject ?? '(no subject)',
                $mailable->render()
            );

            return;
        }

        throw new \RuntimeException(
            'Email is not configured. Set GOOGLE_APPS_SCRIPT_URL (recommended on Render free) or MAIL_* SMTP variables.'
        );
    }

    public static function usesSmtp(): bool
    {
        $mailer = config('mail.default');

        if (in_array($mailer, ['log', 'array', 'google_apps_script'], true)) {
            return false;
        }

        if ($mailer !== 'smtp' && $mailer !== 'failover') {
            return filled(config('mail.mailers.smtp.host'));
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
        return self::usesGoogleAppsScript() || self::usesSmtp();
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
