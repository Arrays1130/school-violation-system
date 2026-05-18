<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->index('created_at');
            $table->index('status');
            $table->index('deleted_at');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('department');
            $table->index('deleted_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
