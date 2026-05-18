<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'section')) {
                $table->string('section')->nullable()->after('department');
            }
            if (!Schema::hasColumn('students', 'year_level')) {
                $table->string('year_level')->nullable()->after('section');
            }
        });

        // Populate existing students with random data
        $sections = ['A', 'B', 'C', 'D'];
        $yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];

        Student::all()->each(function ($student) use ($sections, $yearLevels) {
            $student->update([
                'section' => $sections[array_rand($sections)],
                'year_level' => $yearLevels[array_rand($yearLevels)],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['section', 'year_level']);
        });
    }
};
