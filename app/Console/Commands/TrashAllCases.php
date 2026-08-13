<?php

namespace App\Console\Commands;

use App\Models\StudentCase;
use App\Support\DashboardCache;
use App\Support\MobileCache;
use Illuminate\Console\Command;

class TrashAllCases extends Command
{
    protected $signature = 'cases:trash-all
                            {--force : Skip confirmation prompt}
                            {--purge : Also permanently delete everything already in trash}';

    protected $description = 'Soft-delete all active student cases (move to Trash Bin)';

    public function handle(): int
    {
        $activeCount = StudentCase::query()->count();
        $trashedCount = StudentCase::onlyTrashed()->count();

        if ($activeCount === 0 && (! $this->option('purge') || $trashedCount === 0)) {
            $this->info('No student cases to remove.');

            return self::SUCCESS;
        }

        $this->warn("Active cases to trash: {$activeCount}");
        if ($this->option('purge')) {
            $this->warn("Trashed cases to permanently delete: {$trashedCount}");
        }

        if (! $this->option('force') && ! $this->confirm('Continue? This affects all student violation cases.')) {
            $this->info('Cancelled.');

            return self::SUCCESS;
        }

        $trashed = 0;
        if ($activeCount > 0) {
            StudentCase::query()->orderBy('id')->chunkById(100, function ($cases) use (&$trashed) {
                foreach ($cases as $case) {
                    $case->delete();
                    $trashed++;
                }
            });
        }

        $purged = 0;
        if ($this->option('purge') && $trashedCount > 0) {
            StudentCase::onlyTrashed()->orderBy('id')->chunkById(100, function ($cases) use (&$purged) {
                foreach ($cases as $case) {
                    $case->forceDelete();
                    $purged++;
                }
            });
        }

        DashboardCache::bust();
        MobileCache::bust();

        $this->info("Moved {$trashed} case(s) to trash.");
        if ($this->option('purge')) {
            $this->info("Permanently deleted {$purged} trashed case(s).");
        }

        return self::SUCCESS;
    }
}
