<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\Student;
use App\Models\StudentCase;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all students with null academic_year to 'SY 2024-2025'
        Student::whereNull('academic_year')->update(['academic_year' => 'SY 2024-2025']);
        
        // Clear cache so the dashboard immediately updates
        StudentCase::clearDashboardCache();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed for rollback
    }
};
