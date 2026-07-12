<?php

use App\Models\Student;
use App\Support\YearLevel;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Student::withTrashed()
            ->whereNotNull('year_level')
            ->each(function (Student $student) {
                $canonical = YearLevel::canonical($student->year_level);

                if ($canonical && $canonical !== $student->year_level) {
                    $student->forceFill(['year_level' => $canonical])->saveQuietly();
                }
            });
    }

    public function down(): void
    {
        // Data normalization is not reversed.
    }
};
