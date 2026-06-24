<?php
return [
    // llama3.1 is best for English + Tagalog; deepseek-r1 as fallback for complex reasoning
    'model'           => env('OLLAMA_MODEL', 'llama3.1:latest'),
    'fallback_model'  => env('OLLAMA_FALLBACK_MODEL', 'deepseek-r1:latest'),
    'api_url'         => env('OLLAMA_API_URL', 'http://127.0.0.1:11434'),
    'temperature'     => (float) env('AI_TEMPERATURE', 0.6),
    'max_tokens'      => (int)   env('AI_MAX_TOKENS', 2048),
    'stream'          => (bool)  env('AI_STREAM', true),
    'timeout'         => (int)   env('AI_TIMEOUT', 120),
    'connect_timeout' => (int)   env('AI_CONNECT_TIMEOUT', 5),
];
