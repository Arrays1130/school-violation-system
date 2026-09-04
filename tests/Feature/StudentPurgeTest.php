<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPurgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_move_all_students_to_trash(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        StudentCase::factory()->create(['student_id' => $student->id]);

        $this->actingAs($admin)
            ->delete(route('students.empty-all'))
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted($student);
        $this->assertDatabaseHas('cases', ['student_id' => $student->id]);
    }

    public function test_dean_cannot_move_all_students_to_trash(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        Student::factory()->inDepartment('CCE')->create();

        $this->actingAs($dean)
            ->delete(route('students.empty-all'))
            ->assertForbidden();

        $this->assertSame(1, Student::count());
    }

    public function test_admin_cannot_empty_student_trash(): void
    {
        $admin = User::factory()->admin()->create();
        Student::factory()->create()->delete();

        $this->actingAs($admin)
            ->delete(route('students.empty-trash'))
            ->assertForbidden();

        $this->assertSame(1, Student::onlyTrashed()->count());
    }

    public function test_super_admin_can_permanently_delete_trashed_students_and_cases(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create();
        $case = StudentCase::factory()->create(['student_id' => $student->id]);
        $student->delete();

        $this->actingAs($admin)
            ->delete(route('students.empty-trash'))
            ->assertRedirect(route('students.trash'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }
}
