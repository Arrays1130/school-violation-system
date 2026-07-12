<?php

namespace Tests\Unit;

use App\Support\SchoolMailer;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SchoolMailerTest extends TestCase
{
    public function test_uses_google_apps_script_when_smtp_is_not_configured(): void
    {
        Config::set('mail.default', 'log');
        Config::set('school.google_apps_script_url', 'https://script.google.com/macros/s/example/exec');

        $this->assertFalse(SchoolMailer::usesSmtp());
        $this->assertTrue(SchoolMailer::usesGoogleAppsScript());
    }

    public function test_uses_smtp_when_credentials_are_configured(): void
    {
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp.host', 'smtp.example.com');
        Config::set('mail.mailers.smtp.username', 'user');
        Config::set('mail.mailers.smtp.password', 'secret');
        Config::set('mail.from.address', 'noreply@example.com');

        $this->assertTrue(SchoolMailer::usesSmtp());
    }

    public function test_can_send_when_any_transport_is_configured(): void
    {
        Config::set('mail.default', 'log');
        Config::set('school.google_apps_script_url', 'https://script.google.com/macros/s/example/exec');

        $this->assertTrue(SchoolMailer::canSend());
    }
}
