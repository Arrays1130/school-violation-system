<?php

namespace App\Console\Commands;

use App\Models\StudentCase;
use App\Models\User;
use App\Support\SchoolMailer;
use App\Support\SchoolSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendWeeklyDeanReport extends Command
{
    protected $signature = 'reports:weekly-dean-email';

    protected $description = 'Email weekly violation summary to all deans';

    public function handle(): int
    {
        if (! SchoolSettings::bool('email_enabled', true)) {
            $this->info('Email notifications are disabled. Skipping weekly dean report.');

            return self::SUCCESS;
        }

        $weekStart = now()->subWeek()->startOfDay();
        $weekEnd = now()->endOfDay();

        $deans = User::where('role', 'dean')->get();

        if ($deans->isEmpty()) {
            $this->warn('No dean accounts found.');

            return self::SUCCESS;
        }

        foreach ($deans as $dean) {
            $cases = StudentCase::query()
                ->forUser($dean)
                ->whereHas('student')
                ->whereBetween('occurred_at', [$weekStart, $weekEnd])
                ->with(['student', 'violation'])
                ->orderByDesc('occurred_at')
                ->limit(50)
                ->get();

            $total = $cases->count();
            $open = $cases->whereNotIn('status', ['Closed', 'Dismissed'])->count();
            $closed = $cases->where('status', 'Closed')->count();

            $lines = $cases->take(15)->map(function ($case) {
                $date = $case->occurred_at?->format('M j, Y') ?? '—';
                $student = $case->student?->full_name ?? 'Unknown';
                $violation = $case->violation?->title ?? 'N/A';

                return "{$date} — {$student}: {$violation} ({$case->status})";
            })->implode("\n");

            $body = "Weekly Violation Report for {$dean->department}\n"
                ."Period: {$weekStart->format('M j, Y')} – {$weekEnd->format('M j, Y')}\n\n"
                ."Summary:\n"
                ."- Total cases this week: {$total}\n"
                ."- Open: {$open}\n"
                ."- Closed: {$closed}\n\n"
                .($lines ? "Recent cases:\n{$lines}\n\n" : "No new cases recorded this week.\n\n")
                .'Log in to VioTrack for full details.';

            try {
                $html = '<pre style="font-family:sans-serif;white-space:pre-wrap;">'.e($body).'</pre>';
                SchoolMailer::send($dean->email, 'Weekly Violation Report — VioTrack', $html);
                $this->info("Sent weekly report to {$dean->email}");
            } catch (\Throwable $e) {
                Log::error('Weekly dean report failed', ['dean' => $dean->id, 'error' => $e->getMessage()]);
                $this->error("Failed for {$dean->email}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
