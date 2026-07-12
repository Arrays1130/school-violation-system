<?php

namespace App\Support;

class AiPromptGuard
{
    protected const INJECTION_PATTERNS = [
        '/ignore\s+(all\s+)?(previous|prior|above)\s+instructions/i',
        '/disregard\s+(all\s+)?(previous|prior|system)\s+/i',
        '/you\s+are\s+now\s+/i',
        '/new\s+instructions?\s*:/i',
        '/system\s*prompt/i',
        '/jailbreak/i',
        '/\bDAN\b/',
        '/reveal\s+(the\s+)?(system|hidden)\s+prompt/i',
        '/show\s+(me\s+)?(all|every)\s+students/i',
        '/dump\s+(the\s+)?database/i',
    ];

    public function sanitize(string $message): string
    {
        $message = strip_tags($message);
        $message = preg_replace('/\x{00}-\x{1F}\x{7F}/u', ' ', $message) ?? $message;
        $message = preg_replace('/\s{2,}/', ' ', $message) ?? $message;

        return trim($message);
    }

    public function isBlocked(string $message): bool
    {
        foreach (self::INJECTION_PATTERNS as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    public function refusalMessage(bool $tagalog = false): string
    {
        return $tagalog
            ? 'Hindi ko masagot ang kahilingang iyon. Magtanong lamang tungkol sa handbook, violations, kaso, o patakaran ng paaralan.'
            : 'I cannot process that request. Please ask only about school policies, violations, cases, or institutional guidance.';
    }
}
