<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Support\SchoolSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('settings.update'), [
                'school_name' => 'Updated School',
                'current_academic_year' => 'SY 2026-2027',
            ])
            ->assertRedirect();
    }

    public function test_changing_academic_year_promotes_students_and_stamps_year(): void
    {
        $admin = User::factory()->superAdmin()->create();
        SchoolSettings::set('current_academic_year', 'SY 2025-2026');
        SchoolSettings::set('school_name', 'Test School');

        $first = Student::factory()->create([
            'year_level' => '1st Year',
            'academic_year' => 'SY 2025-2026',
        ]);
        $second = Student::factory()->create([
            'year_level' => '2nd Year',
            'academic_year' => 'SY 2025-2026',
        ]);
        $third = Student::factory()->create([
            'year_level' => '3rd Year',
            'academic_year' => 'SY 2025-2026',
        ]);
        $fourth = Student::factory()->create([
            'year_level' => '4th Year',
            'academic_year' => 'SY 2025-2026',
        ]);

        $this->actingAs($admin)
            ->post(route('settings.update'), [
                'school_name' => 'Test School',
                'current_academic_year' => 'SY 2026-2027',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('2nd Year', $first->fresh()->year_level);
        $this->assertSame('3rd Year', $second->fresh()->year_level);
        $this->assertSame('4th Year', $third->fresh()->year_level);
        $this->assertSame('4th Year', $fourth->fresh()->year_level);
        $this->assertNull($fourth->fresh()->deleted_at);

        foreach ([$first, $second, $third, $fourth] as $student) {
            $this->assertSame('SY 2026-2027', $student->fresh()->academic_year);
        }

        $this->assertSame('SY 2026-2027', SchoolSettings::get('current_academic_year'));
    }

    public function test_saving_same_academic_year_does_not_promote(): void
    {
        $admin = User::factory()->superAdmin()->create();
        SchoolSettings::set('current_academic_year', 'SY 2025-2026');
        SchoolSettings::set('school_name', 'Test School');

        $student = Student::factory()->create([
            'year_level' => '1st Year',
            'academic_year' => 'SY 2025-2026',
        ]);

        $this->actingAs($admin)
            ->post(route('settings.update'), [
                'school_name' => 'Renamed School',
                'current_academic_year' => 'SY 2025-2026',
            ])
            ->assertRedirect();

        $this->assertSame('1st Year', $student->fresh()->year_level);
        $this->assertSame('SY 2025-2026', $student->fresh()->academic_year);
        $this->assertSame('Renamed School', SchoolSettings::get('school_name'));
    }

    public function test_dean_cannot_update_settings(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->post(route('settings.update'), [
                'school_name' => 'Hacked School',
                'current_academic_year' => 'SY 2099-2100',
            ])
            ->assertForbidden();
    }
}
