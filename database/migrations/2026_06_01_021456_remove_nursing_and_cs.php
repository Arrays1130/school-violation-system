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
        // Delete students belonging to Nursing or Computer Science departments
        DB::table('students')->whereIn('department', [
            'Bachelor Of Science In Nursing',
            'Bachelor Of Science In Computer Science'
        ])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data deletion
    }
};
