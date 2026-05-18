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
        $mapping = [
            'Bachelor Of Science In Information System' => 'CEE',
            'Bachelor Of Science In Criminology' => 'CCJE',
            'Bachelor Of Technical Vocational Teachers Education' => 'CTE',
            'College Of Business And Accounting Education' => 'CBAE',
        ];

        foreach ($mapping as $long => $short) {
            \App\Models\Student::where('department', $long)->update(['department' => $short]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $mapping = [
            'CEE' => 'Bachelor Of Science In Information System',
            'CCJE' => 'Bachelor Of Science In Criminology',
            'CTE' => 'Bachelor Of Technical Vocational Teachers Education',
            'CBAE' => 'College Of Business And Accounting Education',
        ];

        foreach ($mapping as $short => $long) {
            \App\Models\Student::where('department', $short)->update(['department' => $long]);
        }
    }
};
