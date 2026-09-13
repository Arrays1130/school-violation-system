<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE case_actions MODIFY COLUMN action_type ENUM(
            'letter_sent',
            'counseling',
            'parent_conference',
            'verbal_warning',
            'written_warning',
            'endorsement',
            'gso_completed',
            'other'
        ) NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE case_actions MODIFY COLUMN action_type ENUM(
            'letter_sent',
            'counseling',
            'parent_conference',
            'verbal_warning',
            'written_warning',
            'endorsement',
            'other'
        ) NOT NULL");
    }
};
