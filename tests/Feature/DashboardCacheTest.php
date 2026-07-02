<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Support\DashboardCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_changes_bump_dashboard_cache_version(): void
    {
        $student = Student::factory()->create();
        $violation = Violation::factory()->create();
        $versionBefore = DashboardCache::version();

        StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $this->assertGreaterThan($versionBefore, DashboardCache::version());
    }
}
