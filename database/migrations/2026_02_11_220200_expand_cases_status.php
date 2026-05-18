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
        // Convert the enum to a varchar to support more statuses
        // First, change the column type
        Schema::table('cases', function (Blueprint $table) {
            $table->string('status', 50)->default('Pending')->change();
        });

        // Map old statuses to new ones
        DB::table('cases')->where('status', 'Open')->update(['status' => 'Pending']);
        DB::table('cases')->where('status', 'Scheduled')->update(['status' => 'Hearing Scheduled']);

        // Add endorsement and closure tracking columns
        Schema::table('cases', function (Blueprint $table) {
            $table->dateTime('endorsed_at')->nullable()->after('sanction');
            $table->dateTime('closed_at')->nullable()->after('endorsed_at');
            $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['endorsed_at', 'closed_at', 'closed_by']);
        });

        // Revert status names
        DB::table('cases')->where('status', 'Pending')->update(['status' => 'Open']);
        DB::table('cases')->where('status', 'Hearing Scheduled')->update(['status' => 'Scheduled']);

        Schema::table('cases', function (Blueprint $table) {
            $table->enum('status', ['Open', 'Scheduled', 'Resolved', 'Dismissed'])->default('Open')->change();
        });
    }
};
