<?php

namespace Tests\Unit;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\Violation;
use App\Services\OffenseAdviceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OffenseAdviceServiceTest extends TestCase
{
    use RefreshDatabase;

    private OffenseAdviceService $advice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->advice = app(OffenseAdviceService::class);
    }

    public function test_offense_level_and_catalog_sanction_for_second_offense(): void
    {
        $student = Student::factory()->create();
        $violation = Violation::factory()->create([
            'code' => 'V-101',
            'first_offense' => 'Verbal warning',
            'second_offense' => 'Written warning',
            'third_offense' => 'Parent conference',
        ]);

        StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'offense_level' => 1,
        ]);

        $level = $this->advice->offenseLevelFor($student->id, $violation->id);
        $this->assertSame(2, $level);
        $this->assertSame('Written warning', $this->advice->sanctionFor($violation, $level));
    }

    public function test_three_minors_triggers_escalation_forecast(): void
    {
        $student = Student::factory()->create();
        $minor = Violation::factory()->create(['severity' => 'Minor']);

        StudentCase::factory()->count(3)->create([
            'student_id' => $student->id,
            'violation_id' => $minor->id,
        ]);

        $forecast = $this->advice->minorEscalationForecast($student->id);

        $this->assertSame(3, $forecast['total_minors']);
        $this->assertTrue($forecast['triggers_escalation_now']);
        $this->assertSame(1, $forecast['escalation_level']);
    }

    public function test_risk_is_not_expulsion_for_closed_minor_history(): void
    {
        $student = Student::factory()->create();
        $minor = Violation::factory()->create(['severity' => 'Minor']);

        StudentCase::factory()->count(5)->closed()->create([
            'student_id' => $student->id,
            'violation_id' => $minor->id,
            'offense_level' => 1,
        ]);

        $analysis = $this->advice->analyzeStudent($student);

        $this->assertNotSame('CRITICAL', $analysis['risk_level']);
        $this->assertStringNotContainsString('Expulsion', $analysis['risk_level']);
        $this->assertStringNotContainsString('Expulsion', $analysis['recommendation']);
    }

    public function test_suggest_for_new_violation_uses_catalog_third_offense(): void
    {
        $student = Student::factory()->create();
        $violation = Violation::factory()->create([
            'code' => 'V-202',
            'first_offense' => 'Verbal warning',
            'second_offense' => 'Written warning',
            'third_offense' => 'Community service',
        ]);

        StudentCase::factory()->count(2)->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $payload = $this->advice->suggestForNewViolation($student, $violation);

        $this->assertSame(3, $payload['offense_level']);
        $this->assertSame('Community service', $payload['recommended_sanction']);
        $this->assertNotEmpty($payload['next_steps']);
    }

    public function test_advise_case_includes_next_steps_for_pending_major(): void
    {
        $student = Student::factory()->create();
        $violation = Violation::factory()->major()->create([
            'code' => 'V-500',
            'severity' => 'Major',
        ]);
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'offense_level' => 1,
            'sanction' => 'Refer to Student Affairs',
        ]);
        $case->forceFill(['status' => 'Pending'])->save();

        $advice = $this->advice->adviseCase($case->fresh(['student', 'violation']));

        $this->assertSame('Refer to Student Affairs', $advice['recommended_sanction']);
        $this->assertFalse($advice['can_endorse']);
        $this->assertNotEmpty($advice['next_steps']);
        $this->assertTrue(
            collect($advice['next_steps'])->contains(fn ($step) => str_contains(strtolower($step), 'osa'))
        );
    }
}
