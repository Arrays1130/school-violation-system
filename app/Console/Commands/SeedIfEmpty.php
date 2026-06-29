<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;

class SeedIfEmpty extends Command
{
    protected $signature = 'app:seed-if-empty';

    protected $description = 'Seed demo students and cases only when the students table is empty';

    public function handle(): int
    {
        if (Student::withTrashed()->exists()) {
            $this->info('Students already exist — skipping demo seed.');

            return self::SUCCESS;
        }

        $this->info('No students found — seeding demo data...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\StudentSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\CaseSeeder',
            '--force' => true,
        ]);

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\HearingSeeder',
            '--force' => true,
        ]);

        $this->info('Demo data restored.');

        return self::SUCCESS;
    }
}
