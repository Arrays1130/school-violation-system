<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalize all case codes to sequential 000-1, 000-2, ... by creation order.
     * Safe to re-run intent: always rewrite codes regardless of prior CASE-* format.
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
        // Irreversible normalization — leave codes as-is.
    }
};
