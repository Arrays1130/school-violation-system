<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Reformat case codes to 000-1, 000-2, ... (creation order).
     */
    public function up(): void
    {
        if (! Schema::hasColumn('cases', 'case_code')) {
            return;
        }

        $cases = DB::table('cases')->orderBy('id')->get(['id']);
        $seq = 1;

        foreach ($cases as $case) {
            DB::table('cases')
                ->where('id', $case->id)
                ->update([
                    'case_code' => sprintf('000-%d', $seq),
                ]);
            $seq++;
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('cases', 'case_code')) {
            return;
        }

        $cases = DB::table('cases')->orderBy('id')->get(['id', 'created_at']);

        foreach ($cases as $case) {
            $year = $case->created_at
                ? date('Y', strtotime($case->created_at))
                : date('Y');

            DB::table('cases')
                ->where('id', $case->id)
                ->update([
                    'case_code' => sprintf('CASE-%s-%05d', $year, $case->id),
                ]);
        }
    }
};
