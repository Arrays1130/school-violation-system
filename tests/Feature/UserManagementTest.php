<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Support\DepartmentResolver;
use App\Support\StakeholderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_form_offers_official_departments(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Create')
                ->where('departments', DepartmentResolver::options())
            );
    }

    public function test_creating_a_dean_requires_a_valid_department(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $payload = $this->deanPayload(['department' => '']);

        $this->actingAs($admin)
            ->post(route('users.store'), $payload)
            ->assertSessionHasErrors('department');

        $payload['department'] = 'CITE';

        $this->actingAs($admin)
            ->post(route('users.store'), $payload)
            ->assertSessionHasErrors('department');
    }

    public function test_creating_a_dean_stores_the_official_shortcut(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), $this->deanPayload([
                'department' => config('school.departments.CCE'),
            ]))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'dean.cce@example.com',
            'role' => 'dean',
            'department' => 'CCE',
        ]);
    }

    public function test_creating_an_admin_clears_department(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('users.store'), $this->deanPayload([
                'role' => 'admin',
                'department' => 'CCE',
            ]))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'dean.cce@example.com',
            'role' => 'admin',
            'department' => null,
        ]);
    }

    public function test_edit_form_canonicalizes_existing_department(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $dean = User::factory()->dean(config('school.departments.CCE'))->create();

        $this->actingAs($admin)
            ->get(route('users.edit', $dean))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Edit')
                ->where('userRecord.department', 'CCE')
                ->where('departments', DepartmentResolver::options())
            );
    }

    public function test_updating_a_dean_to_admin_clears_department(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($admin)
            ->put(route('users.update', $dean), [
                'name' => $dean->name,
                'email' => $dean->email,
                'role' => 'admin',
                'department' => 'CCE',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $dean->id,
            'role' => 'admin',
            'department' => null,
        ]);
    }

    public function test_dean_with_long_department_name_still_receives_violation_notice(): void
    {
        Notification::fake();

        $student = Student::factory()->inDepartment('CCE')->create();
        $dean = User::factory()->dean(config('school.departments.CCE'))->create();
        $otherDean = User::factory()->dean('CCJE')->create();
        $violation = Violation::factory()->create(['severity' => 'Minor']);
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        StakeholderNotifier::notifyViolationRecorded($case);

        Notification::assertSentTo($dean, \App\Notifications\DeanViolationNotification::class);
        Notification::assertNotSentTo($otherDean, \App\Notifications\DeanViolationNotification::class);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function deanPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Dean of CCE',
            'email' => 'dean.cce@example.com',
            'phone' => '09171234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'dean',
            'department' => 'CCE',
        ], $overrides);
    }
}
