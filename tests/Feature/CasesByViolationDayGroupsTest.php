<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CasesByViolationDayGroupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_by_violation_lists_day_groups_without_students_then_day_shows_students(): void
    {
        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create(['title' => 'Bullying']);
        $studentA = Student::factory()->inDepartment('CCE')->create(['full_name' => 'Alice Day One']);
        $studentB = Student::factory()->inDepartment('CCE')->create(['full_name' => 'Bob Day One']);
        $studentC = Student::factory()->inDepartment('CCE')->create(['full_name' => 'Cara Day Two']);

        StudentCase::factory()->create([
            'student_id' => $studentA->id,
            'violation_id' => $violation->id,
            'occurred_at' => '2026-08-01 10:00:00',
        ]);
        StudentCase::factory()->create([
            'student_id' => $studentB->id,
            'violation_id' => $violation->id,
            'occurred_at' => '2026-08-01 14:00:00',
        ]);
        StudentCase::factory()->create([
            'student_id' => $studentC->id,
            'violation_id' => $violation->id,
            'occurred_at' => '2026-08-02 09:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('cases.by-violation', $violation))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cases/ByViolation')
                ->has('dayGroups.data', 2)
                ->where('dayGroups.data.0.date', '2026-08-01')
                ->where('dayGroups.data.0.sequence_label', '000-1')
                ->where('dayGroups.data.0.display_label', 'Bullying 000-1')
                ->where('dayGroups.data.0.student_count', 2)
                ->where('dayGroups.data.1.date', '2026-08-02')
                ->where('dayGroups.data.1.sequence_label', '000-2')
                ->where('dayGroups.data.1.student_count', 1)
            )
            ->assertDontSee('ALICE DAY ONE')
            ->assertDontSee('CARA DAY TWO');

        $this->actingAs($admin)
            ->get(route('cases.by-violation.day', [
                'violation' => $violation,
                'date' => '2026-08-01',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Cases/ByViolationDay')
                ->where('dayGroup.sequence_label', '000-1')
                ->has('cases.data', 2)
                ->where('cases.data.0.student.full_name', 'BOB DAY ONE')
                ->where('cases.data.1.student.full_name', 'ALICE DAY ONE')
            )
            ->assertSee('ALICE DAY ONE')
            ->assertSee('BOB DAY ONE')
            ->assertDontSee('CARA DAY TWO');
    }
}
