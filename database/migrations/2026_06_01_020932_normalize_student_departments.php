<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Normalize the department names from short acronyms to their full names
        $mappings = [
            'BSIT' => 'Bachelor Of Science In Information System',
            'BSCS' => 'Bachelor Of Science In Computer Science',
            'BSCpE' => 'Bachelor Of Science In Computer Engineering',
            'BSBA' => 'College Of Business And Accounting Education',
            'BSEd' => 'Bachelor Of Technical Vocational Teachers Education',
            'BSN' => 'Bachelor Of Science In Nursing'
        ];

        foreach ($mappings as $short => $long) {
            DB::table('students')->where('department', $short)->update(['department' => $long]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data normalization
    }
};
