<?php

namespace Tests\Feature;

use App\Jobs\SendSmsViaGateway;
use App\Models\Hearing;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Notifications\CaseClosedNotification;
use App\Notifications\DeanCaseClosedNotification;
use App\Notifications\DeanHearingNotification;
use App\Notifications\DeanViolationNotification;
use App\Notifications\HearingScheduled;
use App\Notifications\ParentCaseClosedNotification;
use App\Notifications\ParentHearingNotification;
use App\Notifications\ParentViolationNotification;
use App\Notifications\ViolationRecorded;
use App\Support\StakeholderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StakeholderNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.sms_gateway.enabled', true);
        Config::set('school.google_apps_script_url', 'https://script.google.com/macros/s/example/exec');
    }

    public function test_violation_recorded_notifies_student_parent_and_dean(): void
    {
        Notification::fake();
        Bus::fake();

        $student = Student::factory()->create([
            'phone' => '09171234567',
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09179876543',
        ]);
        $dean = User::factory()->dean('CCE')->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
        ]);

        StakeholderNotifier::notifyViolationRecorded($case);

        Notification::assertSentTo($student, ViolationRecorded::class);
        Notification::assertSentOnDemand(ParentViolationNotification::class);
        Notification::assertSentTo($dean, DeanViolationNotification::class);

        Bus::assertDispatched(SendSmsViaGateway::class, function (SendSmsViaGateway $job) {
            return $job->phone === '09179876543';
        });
        Bus::assertDispatched(SendSmsViaGateway::class, function (SendSmsViaGateway $job) {
            return $job->phone === '09171234567';
        });

        $this->assertDatabaseHas('notification_deliveries', [
            'event' => 'student_violation_recorded',
            'channel' => 'mail',
            'status' => 'sent',
        ]);
    }

    public function test_hearing_scheduled_notifies_student_parent_and_all_deans(): void
    {
        Notification::fake();
        Bus::fake();

        $student = Student::factory()->create([
            'phone' => '09171234567',
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09179876543',
        ]);
        $deanA = User::factory()->dean('CCE')->create();
        $deanB = User::factory()->dean('CBA')->create();
        $case = StudentCase::factory()->create(['student_id' => $student->id]);
        $hearing = Hearing::create([
            'case_id' => $case->id,
            'venue' => 'Guidance Office',
            'scheduled_at' => now()->addDay(),
            'participants' => ['Student', 'Dean'],
        ]);

        StakeholderNotifier::notifyHearingScheduled($hearing);

        Notification::assertSentTo($student, HearingScheduled::class);
        Notification::assertSentOnDemand(ParentHearingNotification::class);
        Notification::assertSentTo($deanA, DeanHearingNotification::class);
        Notification::assertSentTo($deanB, DeanHearingNotification::class);

        Bus::assertDispatched(SendSmsViaGateway::class, function (SendSmsViaGateway $job) {
            return $job->phone === '09179876543' && str_contains($job->message, 'scheduled');
        });
    }

    public function test_hearing_update_notifies_parent_and_deans(): void
    {
        Notification::fake();
        Bus::fake();

        $student = Student::factory()->create([
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09179876543',
        ]);
        $dean = User::factory()->dean('CCE')->create();
        $case = StudentCase::factory()->create(['student_id' => $student->id]);
        $hearing = Hearing::create([
            'case_id' => $case->id,
            'venue' => 'OSA Hall',
            'scheduled_at' => now()->addDays(2),
            'participants' => ['Student'],
        ]);

        StakeholderNotifier::notifyHearingScheduled($hearing, isUpdate: true);

        Notification::assertSentOnDemand(ParentHearingNotification::class, function ($notification) {
            return $notification->isUpdate === true;
        });
        Notification::assertSentTo($dean, DeanHearingNotification::class);
        Bus::assertDispatched(SendSmsViaGateway::class, function (SendSmsViaGateway $job) {
            return str_contains($job->message, 'rescheduled');
        });
    }

    public function test_case_closed_notifies_student_parent_and_dean(): void
    {
        Notification::fake();
        Bus::fake();

        $student = Student::factory()->create([
            'phone' => '09171112222',
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09173334444',
        ]);
        $dean = User::factory()->dean('CCE')->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'status' => 'Closed',
            'closed_at' => now(),
            'sanction' => 'Written warning',
        ]);

        StakeholderNotifier::notifyCaseClosed($case);

        Notification::assertSentTo($student, CaseClosedNotification::class);
        Notification::assertSentOnDemand(ParentCaseClosedNotification::class);
        Notification::assertSentTo($dean, DeanCaseClosedNotification::class);

        Bus::assertDispatched(SendSmsViaGateway::class, 2);
    }

    public function test_sms_not_dispatched_when_gateway_disabled(): void
    {
        Notification::fake();
        Bus::fake();
        Config::set('services.sms_gateway.enabled', false);

        $student = Student::factory()->create([
            'phone' => '09171234567',
            'guardian_phone' => '09179876543',
            'guardian_email' => 'parent@example.com',
        ]);
        $case = StudentCase::factory()->create(['student_id' => $student->id]);

        StakeholderNotifier::notifyViolationRecorded($case);

        Bus::assertNotDispatched(SendSmsViaGateway::class);
        Notification::assertSentTo($student, ViolationRecorded::class);
        Notification::assertSentOnDemand(ParentViolationNotification::class);

        $this->assertDatabaseHas('notification_deliveries', [
            'event' => 'student_violation_recorded',
            'channel' => 'mail',
            'status' => 'sent',
        ]);
    }

    public function test_closing_case_via_http_notifies_stakeholders(): void
    {
        Notification::fake();
        Bus::fake();

        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create([
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09179876543',
        ]);
        $violation = Violation::factory()->create(['severity' => 'Minor']);
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->post(route('cases.close', $case))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($student, CaseClosedNotification::class);
        Notification::assertSentOnDemand(ParentCaseClosedNotification::class);
    }

    public function test_scheduling_hearing_via_http_notifies_parent(): void
    {
        Notification::fake();
        Bus::fake();

        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create([
            'guardian_email' => 'parent@example.com',
            'guardian_phone' => '09179876543',
        ]);
        $case = StudentCase::factory()->create(['student_id' => $student->id]);

        $this->actingAs($admin)
            ->post(route('hearings.store'), [
                'case_id' => $case->id,
                'scheduled_at' => now()->addDay()->toDateTimeString(),
                'venue' => 'Guidance Office',
                'participants' => 'Student, Dean',
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, HearingScheduled::class);
        Notification::assertSentOnDemand(ParentHearingNotification::class);
        Bus::assertDispatched(SendSmsViaGateway::class);
    }
}
