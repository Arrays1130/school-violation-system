<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\SchoolSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WeeklyDeanReportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_dean_report_command_skips_when_email_disabled(): void
    {
        SchoolSettings::set('email_enabled', false);
        User::factory()->dean('CCE')->create();

        Artisan::call('reports:weekly-dean-email');

        $this->assertStringContainsString(
            'Email notifications are disabled',
            Artisan::output()
        );
    }
}
