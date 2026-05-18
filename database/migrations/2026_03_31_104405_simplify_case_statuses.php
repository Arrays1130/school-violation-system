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
        // 1. Map old statuses to the new 4-state flow: Pending, Hearing Scheduled, Hearing, Closed
        
        // Pending states
        DB::table('cases')->whereIn('status', ['Open', 'Under OSA Review', 'Endorsed to Grievance'])->update(['status' => 'Pending']);
        
        // Ensure cases with hearings are marked as Hearing Scheduled if they aren't closed
        DB::table('cases')
            ->whereIn('status', ['Pending'])
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('hearings')
                      ->whereColumn('hearings.case_id', 'cases.id');
            })
            ->update(['status' => 'Hearing Scheduled']);

        // Closed states
        DB::table('cases')->whereIn('status', ['Approved', 'Resolved', 'Dismissed'])->update(['status' => 'Closed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reversing accurately is difficult since we're collapsing multiple states.
        // We'll just leave them as is or map back to Pending/Open.
        DB::table('cases')->where('status', 'Closed')->update(['status' => 'Approved']);
    }
};
