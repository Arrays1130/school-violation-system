<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanction_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('required_hours', 8, 2);
            $table->string('status', 40)->default('in_progress');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_to_osa_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'case_id']);
        });

        Schema::create('dtr_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sanction_assignment_id')->constrained('sanction_assignments')->cascadeOnDelete();
            $table->foreignId('logged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('time_in');
            $table->dateTime('time_out')->nullable();
            $table->decimal('hours_served', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sanction_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtr_entries');
        Schema::dropIfExists('sanction_assignments');
    }
};
