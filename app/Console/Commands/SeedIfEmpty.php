<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Violation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        try {
            $this->info('No students found — seeding demo data...');

            if (Violation::count() === 0) {
                $this->call('db:seed', [
                    '--class' => 'Database\\Seeders\\ViolationSeeder',
                    '--force' => true,
                ]);
            }

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
        } catch (\Throwable $e) {
            Log::error('Demo seed failed on deploy', ['message' => $e->getMessage()]);
            $this->error('Demo seed failed: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
