<?php

namespace Database\Seeders;

use App\Models\Hearing;
use App\Models\StudentCase;
use Database\Seeders\Concerns\SeedsWithoutFaker;
use Illuminate\Database\Seeder;

class HearingSeeder extends Seeder
{
    use SeedsWithoutFaker;

    public function run(): void
    {
        $cases = StudentCase::where('status', 'Hearing Scheduled')->get();
        if ($cases->isEmpty()) {
            $cases = StudentCase::where('status', 'Pending')->take(5)->get();
            foreach ($cases as $case) {
                $case->update(['status' => 'Hearing Scheduled']);
            }
        }

        foreach ($cases as $case) {
            Hearing::create([
                'case_id' => $case->id,
                'scheduled_at' => $this->randomBetween(now(), now()->addWeeks(2)),
                'venue' => 'Guidance Office - Room '.random_int(100, 200),
                'participants' => ['Dean of Discipline', 'Guidance Counselor', 'Student Parent'],
                'notes' => 'Please bring pertinent documents.',
            ]);
        }
    }
}
