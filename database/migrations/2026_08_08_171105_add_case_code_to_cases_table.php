<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('case_code', 32)->nullable()->unique()->after('id');
        });

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

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropUnique(['case_code']);
            $table->dropColumn('case_code');
        });
    }
};
