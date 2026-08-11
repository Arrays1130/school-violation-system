<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctionEscalationTest extends TestCase
{
    use RefreshDatabase;

    public function test_third_minor_offense_creates_major_escalation_case(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $minorViolation = Violation::factory()->create(['severity' => 'Minor']);

        $payload = [
            'student_id' => $student->id,
            'violation_id' => $minorViolation->id,
            'description' => 'Minor offense logged',
            'occurred_at' => now()->toDateTimeString(),
        ];

        $this->actingAs($admin)->post(route('cases.store'), $payload)->assertRedirect();
        $this->actingAs($admin)->post(route('cases.store'), $payload)->assertRedirect();

        $beforeCount = $student->cases()->count();
        $this->actingAs($admin)->post(route('cases.store'), $payload)->assertRedirect();

        $student->refresh();
        $this->assertGreaterThan($beforeCount, $student->cases()->count());
        $major = $student->cases()->whereHas('violation', fn ($q) => $q->where('severity', 'Major'))->first();
        $this->assertNotNull($major);
        $this->assertNotNull($major->endorsed_at);
    }
}
