<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctionsReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanctions_compliance_rate_reflects_closed_cases(): void
    {
        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create();
        $student = Student::factory()->create();

        StudentCase::factory()->closed()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'sanction' => 'Community service',
        ]);

        StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'sanction' => 'Written warning',
        ]);

        $this->actingAs($admin)
            ->get(route('reports.sanctions'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('totalSanctions', 2)
                ->where('sanctionsServed', 1)
                ->where('sanctionsPending', 1)
                ->where('complianceRate', 50)
            );
    }
}
