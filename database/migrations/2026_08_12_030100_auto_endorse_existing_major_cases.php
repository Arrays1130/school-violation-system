<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $cases = DB::table('cases')
            ->join('violations', 'cases.violation_id', '=', 'violations.id')
            ->where('violations.severity', 'Major')
            ->whereNull('cases.endorsed_at')
            ->whereNull('cases.deleted_at')
            ->select('cases.id', 'cases.created_by')
            ->get();

        foreach ($cases as $case) {
            DB::table('cases')->where('id', $case->id)->update([
                'endorsed_at' => $now,
                'updated_at' => $now,
            ]);

            $hasEndorsement = DB::table('case_actions')
                ->where('case_id', $case->id)
                ->where('endorsed_to_grievance', true)
                ->exists();

            if ($hasEndorsement || ! $case->created_by) {
                continue;
            }

            DB::table('case_actions')->insert([
                'case_id' => $case->id,
                'user_id' => $case->created_by,
                'action_type' => 'endorsement',
                'description' => 'Automatically endorsed to the Grievance Committee — major offense.',
                'endorsed_to_grievance' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Existing endorsements stay in place.
    }
};
