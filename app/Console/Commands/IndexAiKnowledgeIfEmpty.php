<?php

namespace App\Console\Commands;

use App\Services\AiEmbeddingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IndexAiKnowledgeIfEmpty extends Command
{
    protected $signature = 'ai:index-if-empty';

    protected $description = 'Build Nexus AI embeddings when the ai_embeddings table is empty';

    public function handle(AiEmbeddingService $embeddingService): int
    {
        if (! config('ai.api_key')) {
            $this->warn('GEMINI_API_KEY is not set — skipping AI index.');

            return self::SUCCESS;
        }

        if (! $embeddingService->embeddingsTableReady()) {
            $this->warn('ai_embeddings table is missing — skipping AI index.');

            return self::SUCCESS;
        }

        if ($embeddingService->isAvailable()) {
            $this->info('AI embeddings already exist — skipping index.');

            return self::SUCCESS;
        }

        try {
            $this->info('No AI embeddings found — indexing handbook and violations...');
            $count = $embeddingService->indexAll();
            $stats = $embeddingService->stats();
            $this->info("Indexed {$count} chunks (handbook: {$stats['handbook_chunks']}, violations: {$stats['violation_chunks']}).");
        } catch (\Throwable $e) {
            Log::error('AI index-if-empty failed on boot', ['message' => $e->getMessage()]);
            $this->error('AI index failed: '.$e->getMessage());
        }

        return self::SUCCESS;
    }
}
