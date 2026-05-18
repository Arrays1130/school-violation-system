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
        // 1. Merge Critical into Major
        DB::table('violations')->where('severity', 'Critical')->update(['severity' => 'Major']);

        // 2. Remove Critical from ENUM options
        Schema::table('violations', function (Blueprint $table) {
            $table->enum('severity', ['Minor', 'Major'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
             $table->enum('severity', ['Minor', 'Major', 'Critical'])->change();
        });
    }
};
