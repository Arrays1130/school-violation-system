<?php

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\Hearing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- FAST DATABASE PURGE START ---\n";

// 1. Collect counts before purge
$studentsActiveCount = Student::count();
$studentsTrashedCount = Student::onlyTrashed()->count();
$studentsTotalCount = Student::withTrashed()->count();

$casesCount = DB::table('cases')->count();
$hearingsCount = DB::table('hearings')->count();
$notificationsCount = DB::table('notifications')->where('notifiable_type', Student::class)->count();

echo "Active Students: $studentsActiveCount\n";
echo "Soft-deleted (Trashed) Students: $studentsTrashedCount\n";
echo "Total Students to delete: $studentsTotalCount\n";
echo "Total Cases in database: $casesCount\n";
echo "Total Hearings in database: $hearingsCount\n";
echo "Total Student Database Notifications: $notificationsCount\n";
echo "----------------------------\n";

if ($studentsTotalCount === 0) {
    echo "No students found to delete.\n";
    exit(0);
}

// 2. Perform the deletion using direct DB queries to bypass slow model events/broadcasting
DB::beginTransaction();
try {
    // Delete student notifications
    DB::table('notifications')->where('notifiable_type', Student::class)->delete();
    echo "Deleted student notifications.\n";

    // Delete all students from database directly.
    // This will trigger MySQL database-level cascades on cases, hearings, actions, attachments, etc.
    DB::table('students')->delete();
    echo "Directly deleted all student records from database.\n";

    // Clear dashboard cache
    StudentCase::clearDashboardCache();
    Cache::flush();
    echo "Cleared application cache.\n";

    DB::commit();
    echo "Database transaction committed successfully.\n";

    // Trigger dashboard update event once
    try {
        event(new \App\Events\DashboardUpdated('All students removed'));
        echo "Dispatched dashboard update event.\n";
    } catch (\Exception $e) {
        echo "Note: Event broadcasting timed out/failed as expected (network offline or Reverb unreachable).\n";
    }
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error occurred during deletion: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Verify counts after purge
$newStudentsCount = Student::withTrashed()->count();
$newCasesCount = DB::table('cases')->count();
$newHearingsCount = DB::table('hearings')->count();
$newNotificationsCount = DB::table('notifications')->where('notifiable_type', Student::class)->count();

echo "----------------------------\n";
echo "verification of remaining records:\n";
echo "Students left: $newStudentsCount\n";
echo "Cases left: $newCasesCount\n";
echo "Hearings left: $newHearingsCount\n";
echo "Student Notifications left: $newNotificationsCount\n";
echo "--- DATABASE PURGE COMPLETE ---\n";
