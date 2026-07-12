<?php

namespace App\Console\Commands;

use App\Services\AiEmbeddingService;
use Illuminate\Console\Command;

class IndexAiKnowledge extends Command
{
    protected $signature = 'ai:index {--fresh : Rebuild all handbook and violation embeddings}';

    protected $description = 'Build vector embeddings for handbook and violation knowledge used by Nexus AI';

    public function handle(AiEmbeddingService $embeddingService): int
    {
        if (! config('ai.api_key')) {
            $this->error('GEMINI_API_KEY is not set. Embeddings require a Gemini API key.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Clearing existing embeddings...');
            $embeddingService->clearAll();
        }

        $this->info('Indexing handbook and violation knowledge for Nexus AI...');

        $bar = $this->output->createProgressBar(2);
        $bar->start();

        $count = $embeddingService->indexAll();
        $bar->advance(2);
        $bar->finish();
        $this->newLine(2);

        $stats = $embeddingService->stats();

        $this->info("Indexed {$count} chunks.");
        $this->line("Handbook chunks: {$stats['handbook_chunks']}");
        $this->line("Violation chunks: {$stats['violation_chunks']}");

        return self::SUCCESS;
    }
}
