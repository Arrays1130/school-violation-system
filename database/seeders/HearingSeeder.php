<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StudentCase;
use App\Models\Hearing;

class HearingSeeder extends Seeder
{
    public function run(): void
    {
        // Get scheduled cases
        $cases = StudentCase::where('status', 'Hearing Scheduled')->get();
        if ($cases->isEmpty()) {
            // Force create some if none
            $cases = StudentCase::where('status', 'Pending')->take(5)->get();
            foreach($cases as $case) $case->update(['status' => 'Hearing Scheduled']);
        }

        foreach ($cases as $case) {
            Hearing::create([
                'case_id' => $case->id,
                'scheduled_at' => fake()->dateTimeBetween('now', '+2 weeks'),
                'venue' => 'Guidance Office - Room ' . fake()->numberBetween(100, 200),
                'participants' => ['Dean of Discipline', 'Guidance Counselor', 'Student Parent'],
                'notes' => 'Please bring pertinent documents.',
            ]);
        }
    }
}
