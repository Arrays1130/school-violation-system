<?php

namespace App\Console\Commands;

use App\Models\Handbook;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportHandbooks extends Command
{
    protected $signature = 'handbooks:import
                            {--path=database/data/handbooks.json : Path to the handbooks JSON export}
                            {--prune : Delete production handbooks whose titles are not in the export}';

    protected $description = 'Upsert handbook documents from a local JSON export (used to sync Laragon handbooks to Render)';

    public function handle(): int
    {
        $path = base_path($this->option('path'));

        if (! File::exists($path)) {
            $this->error("Handbook export not found: {$path}");

            return self::FAILURE;
        }

        $payload = json_decode(File::get($path), true);
        $rows = $payload['handbooks'] ?? null;

        if (! is_array($rows) || $rows === []) {
            $this->error('Invalid or empty handbooks export.');

            return self::FAILURE;
        }

        $titles = [];
        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $titles[] = $title;

            $handbook = Handbook::query()->firstOrNew(['title' => $title]);
            $isNew = ! $handbook->exists;

            $handbook->content = $row['content'] ?? null;
            if (array_key_exists('attachment', $row)) {
                $handbook->attachment = $row['attachment'];
            }
            $handbook->save();

            if ($isNew) {
                $created++;
                $this->line("Created: {$title}");
            } else {
                $updated++;
                $this->line("Updated: {$title}");
            }
        }

        $pruned = 0;
        if ($this->option('prune') && $titles !== []) {
            $pruned = Handbook::query()->whereNotIn('title', $titles)->delete();
            $this->warn("Pruned {$pruned} handbook(s) not present in the export.");
        }

        $this->info("Handbooks synced. created={$created} updated={$updated} total_in_export=".count($titles));

        return self::SUCCESS;
    }
}
