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

        $cases = DB::table('cases')->orderBy('id')->get(['id']);

        foreach ($cases as $case) {
            DB::table('cases')
                ->where('id', $case->id)
                ->update([
                    'case_code' => sprintf('000-%d', $case->id),
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
