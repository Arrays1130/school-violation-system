<?php

namespace Tests\Feature;

use App\Models\MeetingMinute;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingMinuteAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_cannot_view_minutes_from_other_department(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
        ]);

        $minute = MeetingMinute::create([
            'case_id' => $case->id,
            'title' => 'Cross-dept minutes',
            'content' => 'Secret discussion',
            'meeting_date' => now(),
            'venue' => 'Room 1',
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->actingAs($dean)
            ->get(route('meeting-minutes.show', $minute))
            ->assertForbidden();
    }

    public function test_dean_can_view_minutes_from_own_department(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $minute = MeetingMinute::create([
            'case_id' => $case->id,
            'title' => 'Dept minutes',
            'content' => 'Allowed discussion',
            'meeting_date' => now(),
            'venue' => 'Room 2',
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->actingAs($dean)
            ->get(route('meeting-minutes.show', $minute))
            ->assertOk();
    }

    public function test_dean_cannot_create_meeting_minutes(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->get(route('meeting-minutes.create'))
            ->assertForbidden();
    }

    public function test_dean_cannot_delete_meeting_minutes(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $minute = MeetingMinute::create([
            'case_id' => $case->id,
            'title' => 'Protected minutes',
            'content' => 'Cannot delete',
            'meeting_date' => now(),
            'venue' => 'Room 3',
            'created_by' => User::factory()->admin()->create()->id,
        ]);

        $this->actingAs($dean)
            ->delete(route('meeting-minutes.destroy', $minute))
            ->assertForbidden();
    }
}
