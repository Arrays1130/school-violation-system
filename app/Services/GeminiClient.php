<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    public function functionDeclarations(): array
    {
        return [
            [
                'name' => 'search_students',
                'description' => 'Find students by partial name or ID within the user scope.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_name_or_id' => [
                            'type' => 'string',
                            'description' => 'Student full name or numeric ID.',
                        ],
                    ],
                    'required' => ['student_name_or_id'],
                ],
            ],
            [
                'name' => 'get_student_cases',
                'description' => 'Get full violation case history for a student.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_name_or_id' => [
                            'type' => 'string',
                            'description' => 'Student full name or numeric ID.',
                        ],
                    ],
                    'required' => ['student_name_or_id'],
                ],
            ],
            [
                'name' => 'analyze_student_incident',
                'description' => 'Risk analysis and recommendation for a student using offense levels, severity, and minor-escalation rules.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_name_or_id' => [
                            'type' => 'string',
                            'description' => 'Student full name or numeric ID.',
                        ],
                    ],
                    'required' => ['student_name_or_id'],
                ],
            ],
            [
                'name' => 'suggest_sanction_and_next_step',
                'description' => 'Suggest the catalog sanction and concrete OSA next steps for a student, optional violation code, or current case context.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'student_name_or_id' => [
                            'type' => 'string',
                            'description' => 'Student full name or numeric ID. Optional when page case context is available.',
                        ],
                        'violation_code' => [
                            'type' => 'string',
                            'description' => 'Optional violation code like V-099 to preview sanction for a new incident.',
                        ],
                        'case_id' => [
                            'type' => 'integer',
                            'description' => 'Optional case ID to advise on an existing case workflow.',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'get_system_stats',
                'description' => 'System-wide case totals, open cases, and top violators.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'scope' => [
                            'type' => 'string',
                            'description' => 'Use "current" for scoped totals.',
                        ],
                    ],
                    'required' => ['scope'],
                ],
            ],
            [
                'name' => 'get_all_violations',
                'description' => 'List all configured violation rules with codes, severity, and 1st/2nd/3rd offense sanctions.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'scope' => [
                            'type' => 'string',
                            'description' => 'Use "all".',
                        ],
                    ],
                    'required' => ['scope'],
                ],
            ],
        ];
    }

    /**
     * @return array{type: string, text?: string, function_name?: string, function_args?: array, raw?: array, error?: string}
     */
    public function generate(array $contents, string $systemPrompt, bool $enableTools = true): array
    {
        $apiKey = config('ai.api_key');
        if (empty($apiKey)) {
            return ['type' => 'error', 'error' => 'Error: Gemini API key is missing. Please set GEMINI_API_KEY in .env.'];
        }

        $model = config('ai.model', 'gemini-2.5-flash');
        $payload = [
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) config('ai.temperature', 0.5),
                'maxOutputTokens' => (int) config('ai.max_tokens', 1024),
            ],
        ];

        if ($enableTools) {
            $payload['tools'] = [[
                'functionDeclarations' => $this->functionDeclarations(),
            ]];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = Http::timeout((int) config('ai.timeout', 120))
                ->connectTimeout((int) config('ai.connect_timeout', 5))
                ->post($url, $payload);

            if (! $response->successful()) {
                return [
                    'type' => 'error',
                    'error' => 'Error: Gemini API Request failed (Status: ' . $response->status() . '). Response: ' . $response->body(),
                ];
            }

            return $this->parseCandidate($response->json());
        } catch (\Throwable $e) {
            Log::error('Gemini generate error: ' . $e->getMessage());

            return ['type' => 'error', 'error' => 'Error: ' . $e->getMessage()];
        }
    }

    public function stream(array $contents, string $systemPrompt, \Closure $onChunk): array
    {
        $apiKey = config('ai.api_key');
        if (empty($apiKey)) {
            return ['type' => 'error', 'error' => 'Error: Gemini API key is missing. Please set GEMINI_API_KEY in .env.'];
        }

        $model = config('ai.model', 'gemini-2.5-flash');
        $payload = [
            'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) config('ai.temperature', 0.5),
                'maxOutputTokens' => (int) config('ai.max_tokens', 1024),
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:streamGenerateContent?alt=sse&key={$apiKey}";
        $fullText = '';

        try {
            $response = Http::timeout((int) config('ai.timeout', 120))
                ->connectTimeout((int) config('ai.connect_timeout', 5))
                ->withOptions(['stream' => true])
                ->post($url, $payload);

            if (! $response->successful()) {
                return [
                    'type' => 'error',
                    'error' => 'Error: Gemini stream failed (Status: ' . $response->status() . ').',
                ];
            }

            $body = $response->toPsrResponse()->getBody();
            $buffer = '';

            while (! $body->eof()) {
                $buffer .= $body->read(1024);
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);

                    if ($line === '' || ! str_starts_with($line, 'data:')) {
                        continue;
                    }

                    $json = trim(substr($line, 5));
                    if ($json === '' || $json === '[DONE]') {
                        continue;
                    }

                    $data = json_decode($json, true);
                    if (! is_array($data)) {
                        continue;
                    }

                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if ($text !== '') {
                        $fullText .= $text;
                        $onChunk($text);
                    }
                }
            }

            if ($fullText === '') {
                return ['type' => 'error', 'error' => 'Error: Gemini stream returned no text.'];
            }

            return ['type' => 'text', 'text' => $fullText];
        } catch (\Throwable $e) {
            Log::error('Gemini stream error: ' . $e->getMessage());

            return ['type' => 'error', 'error' => 'Error: ' . $e->getMessage()];
        }
    }

    protected function parseCandidate(array $data): array
    {
        $parts = $data['candidates'][0]['content']['parts'] ?? [];

        foreach ($parts as $part) {
            if (! empty($part['functionCall'])) {
                return [
                    'type' => 'function_call',
                    'function_name' => $part['functionCall']['name'] ?? '',
                    'function_args' => $part['functionCall']['args'] ?? [],
                    'raw' => $data,
                ];
            }
        }

        $text = $parts[0]['text'] ?? '';

        return [
            'type' => 'text',
            'text' => $text !== '' ? $text : "Sorry, I couldn't generate a response.",
            'raw' => $data,
        ];
    }

    public function buildFunctionResponseContent(string $name, mixed $result): array
    {
        $payload = is_array($result) ? $result : ['result' => (string) $result];

        return [
            'role' => 'user',
            'parts' => [[
                'functionResponse' => [
                    'name' => $name,
                    'response' => $payload,
                ],
            ]],
        ];
    }

    public function buildModelFunctionCallContent(string $name, array $args): array
    {
        return [
            'role' => 'model',
            'parts' => [[
                'functionCall' => [
                    'name' => $name,
                    'args' => $args,
                ],
            ]],
        ];
    }
}
