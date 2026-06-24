<?php
return [
    'model'           => env('GEMINI_MODEL', 'gemini-2.5-flash'),
    'api_key'         => env('GEMINI_API_KEY', ''),
    'temperature'     => (float) env('AI_TEMPERATURE', 0.6),
    'max_tokens'      => (int)   env('AI_MAX_TOKENS', 2048),
    'stream'          => (bool)  env('AI_STREAM', true),
    'timeout'         => (int)   env('AI_TIMEOUT', 120),
    'connect_timeout' => (int)   env('AI_CONNECT_TIMEOUT', 5),
];
