<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->index(['student_id', 'occurred_at']);
            $table->index(['status', 'occurred_at']);
            $table->index(['is_archived', 'status']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index(['academic_year', 'department']);
        });

        Schema::table('hearings', function (Blueprint $table) {
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'occurred_at']);
            $table->dropIndex(['status', 'occurred_at']);
            $table->dropIndex(['is_archived', 'status']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['academic_year', 'department']);
        });

        Schema::table('hearings', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
        });
    }
};
