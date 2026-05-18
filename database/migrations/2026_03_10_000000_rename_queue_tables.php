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
        if (Schema::hasTable('jobs')) {
            Schema::rename('jobs', 'system_processes');
        }
        
        if (Schema::hasTable('failed_jobs')) {
            Schema::rename('failed_jobs', 'system_failed_processes');
        }

        if (Schema::hasTable('job_batches')) {
            Schema::rename('job_batches', 'system_process_batches');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('system_processes')) {
            Schema::rename('system_processes', 'jobs');
        }

        if (Schema::hasTable('system_failed_processes')) {
            Schema::rename('system_failed_processes', 'failed_jobs');
        }

        if (Schema::hasTable('system_process_batches')) {
            Schema::rename('system_process_batches', 'job_batches');
        }
    }
};
