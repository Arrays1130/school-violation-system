<?php

namespace App\Services;

use App\Models\AiEmbedding;
use App\Models\Handbook;
use App\Models\Violation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AiEmbeddingService
{
    public function embeddingsTableReady(): bool
    {
        return Schema::hasTable('ai_embeddings');
    }

    public function isAvailable(): bool
    {
        if (! $this->embeddingsTableReady() || ! config('ai.api_key')) {
            return false;
        }

        return AiEmbedding::query()->exists();
    }

    public function indexAll(): int
    {
        if (! $this->embeddingsTableReady()) {
            return 0;
        }

        $count = 0;
        foreach (Handbook::query()->cursor() as $handbook) {
            $count += $this->indexHandbook($handbook);
        }
        foreach (Violation::query()->cursor() as $violation) {
            $count += $this->indexViolation($violation);
        }

        return $count;
    }

    public function clearAll(): void
    {
        if ($this->embeddingsTableReady()) {
            AiEmbedding::query()->delete();
        }
    }

    public function indexHandbook(Handbook $handbook): int
    {
        if (! $this->embeddingsTableReady()) {
            return 0;
        }

        AiEmbedding::query()
            ->where('source_type', 'handbook')
            ->where('source_id', $handbook->id)
            ->delete();

        $chunks = $this->chunkText($handbook->title . "\n\n" . ($handbook->content ?? ''));
        $indexed = 0;

        foreach ($chunks as $index => $chunk) {
            $embedding = $this->embedText($chunk);
            if ($embedding === null) {
                continue;
            }

            AiEmbedding::create([
                'source_type' => 'handbook',
                'source_id' => $handbook->id,
                'chunk_index' => $index,
                'title' => $handbook->title,
                'content' => $chunk,
                'embedding' => $embedding,
            ]);
            $indexed++;
        }

        return $indexed;
    }

    public function indexViolation(Violation $violation): int
    {
        if (! $this->embeddingsTableReady()) {
            return 0;
        }

        AiEmbedding::query()
            ->where('source_type', 'violation')
            ->where('source_id', $violation->id)
            ->delete();

        $text = implode("\n", array_filter([
            "[{$violation->code}] {$violation->title}",
            "Severity: {$violation->severity}",
            "Category: {$violation->category}",
            $violation->default_description,
            $violation->first_offense ? "1st offense: {$violation->first_offense}" : null,
            $violation->second_offense ? "2nd offense: {$violation->second_offense}" : null,
            $violation->third_offense ? "3rd offense: {$violation->third_offense}" : null,
        ]));

        $embedding = $this->embedText($text);
        if ($embedding === null) {
            return 0;
        }

        AiEmbedding::create([
            'source_type' => 'violation',
            'source_id' => $violation->id,
            'chunk_index' => 0,
            'title' => "[{$violation->code}] {$violation->title}",
            'content' => $text,
            'embedding' => $embedding,
        ]);

        return 1;
    }

    /**
     * @return array<int, array{source_type: string, source_id: int, title: string, content: string, score: float}>
     */
    public function search(string $query, int $limit = 6): array
    {
        if (! $this->embeddingsTableReady()) {
            return [];
        }

        $queryEmbedding = $this->embedText($query);
        if ($queryEmbedding === null) {
            return [];
        }

        $scored = [];
        foreach (AiEmbedding::query()->cursor() as $row) {
            $score = $this->cosineSimilarity($queryEmbedding, $row->embedding ?? []);
            if ($score < 0.35) {
                continue;
            }

            $scored[] = [
                'source_type' => $row->source_type,
                'source_id' => $row->source_id,
                'title' => $row->title,
                'content' => $row->content,
                'score' => $score,
            ];
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $limit);
    }

    public function stats(): array
    {
        if (! $this->embeddingsTableReady()) {
            return [
                'total_chunks' => 0,
                'handbook_chunks' => 0,
                'violation_chunks' => 0,
                'last_indexed_at' => null,
            ];
        }

        return [
            'total_chunks' => AiEmbedding::query()->count(),
            'handbook_chunks' => AiEmbedding::query()->where('source_type', 'handbook')->count(),
            'violation_chunks' => AiEmbedding::query()->where('source_type', 'violation')->count(),
            'last_indexed_at' => AiEmbedding::query()->max('updated_at'),
        ];
    }

    protected function embedText(string $text): ?array
    {
        $apiKey = config('ai.api_key');
        if (empty($apiKey)) {
            return null;
        }

        $model = config('ai.embedding_model', 'text-embedding-004');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:embedContent?key={$apiKey}";

        try {
            $response = Http::timeout(30)->post($url, [
                'model' => "models/{$model}",
                'content' => [
                    'parts' => [['text' => Str::limit($text, 8000)]],
                ],
            ]);

            if (! $response->successful()) {
                Log::warning('Gemini embedding failed: ' . $response->body());

                return null;
            }

            return $response->json('embedding.values');
        } catch (\Throwable $e) {
            Log::error('Gemini embedding error: ' . $e->getMessage());

            return null;
        }
    }

    protected function chunkText(string $text, int $size = 700, int $overlap = 120): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        if ($text === '') {
            return [];
        }

        if (strlen($text) <= $size) {
            return [$text];
        }

        $chunks = [];
        $start = 0;
        $length = strlen($text);

        while ($start < $length) {
            $chunk = substr($text, $start, $size);
            $chunks[] = trim($chunk);
            if ($start + $size >= $length) {
                break;
            }
            $start += max(1, $size - $overlap);
        }

        return array_values(array_filter($chunks));
    }

    protected function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($a as $i => $value) {
            $other = $b[$i] ?? 0;
            $dot += $value * $other;
            $normA += $value * $value;
            $normB += $other * $other;
        }

        if ($normA <= 0 || $normB <= 0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
