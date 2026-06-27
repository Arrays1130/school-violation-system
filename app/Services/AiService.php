<?php

namespace App\Services;

use App\Models\Handbook;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AiService
{
    /**
     * Common school-related synonyms to improve search accuracy.
     */
    protected $respondInTagalog = false;
    protected $synonyms = [
        'fight' => ['assault', 'brawl', 'physical injury', 'violence', 'hitting', 'punching', 'harm', 'sanction', 'penalty'],
        'fighting' => ['assault', 'brawl', 'physical injury', 'violence', 'hitting', 'punching', 'harm', 'sanction', 'penalty'],
        'hit' => ['assault', 'physical injury', 'violence'],
        'punch' => ['assault', 'physical injury', 'violence'],
        'bullied' => ['bullying', 'harassment', 'threat', 'intimidation'],
        'bully' => ['bullying', 'harassment', 'threat', 'intimidation'],
        'harass' => ['bullying', 'harassment', 'threat', 'intimidation'],
        
        'uniform' => ['attire', 'clothing', 'wear', 'shirt', 'pants', 'shoes', 'grooming', 'dress code', 'skirt', 'blouse'],
        'clothes' => ['uniform', 'attire', 'appearance', 'dress code'],
        'shirt' => ['uniform', 'attire'],
        'pants' => ['uniform', 'attire'],
        'shoes' => ['uniform', 'attire', 'footwear'],
        'hair' => ['grooming', 'haircut', 'waistcoat'],
        'dye' => ['grooming', 'hair color'],
        'piercing' => ['grooming', 'earring', 'jewelry'],
        
        'late' => ['tardiness', 'attendance', 'punctuality'],
        'tardy' => ['attendance', 'punctuality'],
        'absent' => ['attendance', 'truancy', 'absence'],
        'skip' => ['truancy', 'cutting classes'],
        'cutting' => ['truancy', 'cutting classes'],
        
        'id' => ['identification', 'card', 'lanyard', 'validation'],
        'card' => ['identification'],
        
        'phone' => ['electronic devices', 'gadgets', 'cellular', 'mobile'],
        'cell' => ['electronic devices', 'cellular', 'mobile'],
        'tablet' => ['electronic devices', 'gadgets'],
        
        'vape' => ['smoking', 'tobacco', 'electronic cigarette'],
        'smoke' => ['smoking', 'tobacco', 'vaping'],
        'cigarette' => ['smoking', 'tobacco'],
        
        'cheat' => ['academic dishonesty', 'plagiarism', 'copying'],
        'copy' => ['academic dishonesty', 'plagiarism', 'cheating'],
        'steal' => ['theft', 'pilferage'],
        
        'bad word' => ['profanity', 'obscenity', 'language'],
        'swear' => ['profanity', 'obscenity', 'language'],
        'curse' => ['profanity', 'obscenity', 'language'],
        
        'teacher' => ['personnel', 'authority', 'faculty', 'staff'],
        'guard' => ['security', 'personnel', 'authority'],
        'sex' => ['harassment', 'sexual'], 
    ];

    public function processChat(string $message): array
    {
        set_time_limit(150); 
        Log::info("AiService: High-Intelligence processing for: '$message'");
        
        // Detect if the user message is in Tagalog
        $this->respondInTagalog = $this->isTagalog($message);
        $relevantHandbooks = $this->searchHandbooks($message);

        $institutionalContext = [
            'current_date' => now()->format('l, F j, Y'),
            'school_name' => 'I-Link CST',
            'assistant_role' => 'Principal Violation Consultant',
        ];

        $conversationHistory = [];
        $currentMessage = $message;

        for ($i = 0; $i < 3; $i++) {
            $response = $this->callGeminiApi($currentMessage, $relevantHandbooks, $conversationHistory, false, null, $institutionalContext);
            
            if (str_starts_with($response, 'Error:')) {
                Log::warning("Gemini API failed or unavailable. Falling back to local handbook search. Details: " . $response);
                break; // Fall back to local database handbook search
            }

            // IMPROVED REGEX: Matches even if AI includes other text
            if (preg_match('/\[TOOL:\s*(\w+)\s*,\s*(.*?)\]/', $response, $matches)) {
                $toolName = trim($matches[1]);
                $toolArg = trim($matches[2]);
                
                $toolResult = $this->executeTool($toolName, $toolArg);
                
                $conversationHistory[] = ['role' => 'assistant', 'content' => $response];
                $conversationHistory[] = ['role' => 'system', 'content' => "CRITICAL SYSTEM DATA ($toolName): " . $toolResult];
                
                $currentMessage = "SYSTEM DATA RECEIVED: $toolResult. Now, answer the user's question directly using this data. If no data was found, say so. Do not imagine data.";
                continue;
            }

            $sources = array_map(fn($item) => $item['handbook']->title, $relevantHandbooks);
            return [
                'reply' => (string) Str::markdown($response),
                'sources' => array_values(array_unique($sources))
            ];
        }

        return $this->formatLocalResponse($relevantHandbooks, $message);
    }

    private function executeTool($name, $arg) {
        try {
            switch ($name) {

                case 'analyze_student_incident':
                    $student = \App\Models\Student::where('full_name', 'LIKE', "%$arg%")
                        ->orWhere('id', $arg)
                        ->first();
                    if (!$student) return "Error: Student '$arg' not found in the database.";

                    $casesCount = \App\Models\StudentCase::where('student_id', $student->id)->count();
                    $lastCase   = \App\Models\StudentCase::where('student_id', $student->id)
                        ->with('violation')->latest()->first();

                    return json_encode([
                        'analysis_for'              => $student->full_name,
                        'system_id'                 => $student->id,
                        'department'                => $student->department,
                        'year_level'                => $student->year_level,
                        'total_violations_recorded' => $casesCount,
                        'recidivism_level'          => $this->calculateRisk($casesCount),
                        'last_offense'              => $lastCase ? ($lastCase->violation->title ?? 'Unknown') : 'No record',
                        'recommendation'            => $casesCount >= 3
                            ? 'URGENT: Recommend endorsement to Dean for disciplinary hearing.'
                            : 'Monitor. Refer to Chapter 4 of Handbook for escalation thresholds.',
                    ]);

                case 'search_students':
                    $students = \App\Models\Student::where('full_name', 'LIKE', "%$arg%")
                        ->orWhere('id', 'LIKE', "%$arg%")
                        ->withCount('cases')
                        ->limit(5)
                        ->get(['id', 'full_name', 'department', 'year_level', 'section']);
                    if ($students->isEmpty()) return "No students found matching '$arg'.";
                    return $students->map(fn($s) => [
                        'id'         => $s->id,
                        'name'       => $s->full_name,
                        'department' => $s->department,
                        'year'       => $s->year_level,
                        'section'    => $s->section,
                        'case_count' => $s->cases_count,
                    ])->toJson();

                case 'get_student_cases':
                    $student = \App\Models\Student::where('full_name', 'LIKE', "%$arg%")
                        ->orWhere('id', $arg)
                        ->first();
                    if (!$student) return "Student '$arg' not found.";

                    $cases = \App\Models\StudentCase::where('student_id', $student->id)
                        ->with('violation')
                        ->latest('occurred_at')
                        ->get();

                    if ($cases->isEmpty()) return "✅ Clean record. No cases found for {$student->full_name}.";

                    return json_encode([
                        'student'     => $student->full_name,
                        'department'  => $student->department,
                        'total_cases' => $cases->count(),
                        'risk_level'  => $this->calculateRisk($cases->count()),
                        'cases'       => $cases->map(fn($c) => [
                            'date'      => $c->occurred_at
                                ? $c->occurred_at->format('M d, Y')
                                : $c->created_at->format('M d, Y'),
                            'violation' => $c->violation
                                ? "[{$c->violation->code}] {$c->violation->title}"
                                : 'Unknown violation',
                            'severity'  => $c->violation->severity ?? 'N/A',
                            'status'    => $c->status,
                            'sanction'  => $c->sanction ?? 'TBD',
                        ])->toArray(),
                    ]);

                case 'get_system_stats':
                    $totalCases     = \App\Models\StudentCase::count();
                    $totalStudents  = \App\Models\Student::count();
                    $openCases      = \App\Models\StudentCase::where('status', 'open')->count();
                    $topViolators   = \App\Models\StudentCase::selectRaw('student_id, COUNT(*) as count')
                        ->with('student')
                        ->groupBy('student_id')
                        ->orderByDesc('count')
                        ->limit(5)
                        ->get();
                    $recentCases    = \App\Models\StudentCase::with(['student', 'violation'])
                        ->latest('occurred_at')
                        ->limit(5)
                        ->get();

                    return json_encode([
                        'total_cases_recorded'  => $totalCases,
                        'total_students'        => $totalStudents,
                        'open_cases'            => $openCases,
                        'recent_incidents'      => $recentCases->map(fn($c) => [
                            'student'   => $c->student->full_name ?? 'Unknown',
                            'violation' => $c->violation ? "[{$c->violation->code}] {$c->violation->title}" : 'Unknown',
                            'date'      => $c->occurred_at ? $c->occurred_at->format('M d, Y') : ($c->created_at ? $c->created_at->format('M d, Y') : 'Unknown'),
                            'status'    => $c->status,
                        ])->toArray(),
                        'top_frequent_violators'=> $topViolators->map(fn($v) => [
                            'name'            => $v->student->full_name ?? 'Unknown',
                            'department'      => $v->student->department ?? 'N/A',
                            'violation_count' => $v->count,
                            'risk_level'      => $this->calculateRisk($v->count),
                        ])->toArray(),
                    ]);

                case 'get_all_violations':
                    $violations = \App\Models\Violation::all(['code', 'title', 'severity']);
                    return $violations->isEmpty()
                        ? "No violations defined in the system."
                        : $violations->toJson();

                default:
                    return "Tool '$name' not found. Available: search_students, get_student_cases, analyze_student_incident, get_system_stats, get_all_violations.";
            }
        } catch (\Exception $e) {
            Log::error("Tool '$name' error: " . $e->getMessage());
            return "Error in tool '$name': " . $e->getMessage();
        }
    }

    private function calculateRisk($count) {
        if ($count >= 5) return 'CRITICAL (Expulsion Warning)';
        if ($count >= 3) return 'HIGH (Suspension Candidate)';
        if ($count >= 1) return 'MODERATE';
        return 'LOW';
    }

    private function findSanctionChapter() {
        return Handbook::where('title', 'LIKE', '%Sanction%')
                       ->orWhere('title', 'LIKE', '%Penalty%')
                       ->orWhere('title', 'LIKE', '%Conduct%')
                       ->orWhere('title', 'LIKE', '%Offense%')
                       ->first();
    }

    private function searchHandbooks(string $message): array
    {
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'in', 'student', 'was', 'were', 'has', 'have', 'had', 'been', 'to', 'for', 'of', 'and', 'or', 'but', 'what', 'penalty', 'policy', 'rule', 'can', 'i', 'get', 'do', 'does', 'are', 'if', 'my', 'how', 'when', 'why'];
        
        $rawWords = explode(' ', strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $message)));
        $keywords = array_filter($rawWords, function($word) use ($stopWords) {
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });

        $expandedKeywords = $keywords;
        foreach ($keywords as $word) {
            if (isset($this->synonyms[$word])) {
                $expandedKeywords = array_merge($expandedKeywords, $this->synonyms[$word]);
            }
            $singular = rtrim($word, 's');
            if (isset($this->synonyms[$singular])) {
                $expandedKeywords = array_merge($expandedKeywords, $this->synonyms[$singular]);
            }
        }
        $expandedKeywords = array_unique($expandedKeywords);

        Log::debug("AiService Search: Expanded keywords: " . implode(', ', $expandedKeywords));

        $handbooks = Handbook::all();
        $scored = [];

        foreach ($handbooks as $handbook) {
            $score = 0;
            $contentLower = strtolower($handbook->content);
            $titleLower = strtolower($handbook->title);
            
            $foundMatches = [];

            foreach ($expandedKeywords as $keyword) {
                if (str_contains($titleLower, $keyword)) {
                    $score += 20; 
                    $foundMatches[] = $keyword;
                }
                if (str_contains($contentLower, $keyword)) {
                    $score += 5;
                    $foundMatches[] = $keyword;
                }
            }

            $uniqueMatches = count(array_unique($foundMatches));
            $score += ($uniqueMatches * 10);

            if ($score > 0) {
                $scored[] = [
                    'handbook' => $handbook,
                    'score' => $score,
                    'matches' => array_unique($foundMatches)
                ];
            }
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        
        if (!empty($scored)) {
            $topScore = $scored[0]['score'];
            $scored = array_filter($scored, function($item) use ($topScore) {
                return $item['score'] >= ($topScore * 0.3);
            });
        }
        
        return array_slice($scored, 0, 3);
    }

    /**
     * Detect if the user is asking about a specific student by name.
     * Returns the student name extracted from the message, or null.
     */
    private function extractStudentName(string $message): ?string
    {
        // Tagalog patterns: "ni <name>", "si <name>", "kay <name>"
        if (preg_match('/\b(?:ni|si|kay)\s+([A-Za-z][A-Za-z\s]{2,30}?)(?:\?|$|\s+ang|\s+ay|\s+na|\s+ba)/ui', $message, $m)) {
            return trim($m[1]);
        }
        // English patterns: "of <name>", "for <name>", "<name>'s violations/cases/record"
        if (preg_match('/\b(?:of|for|about)\s+([A-Za-z][A-Za-z\s]{2,30}?)(?:\?|$|\s+violations|\s+cases|\s+record)/ui', $message, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/\b([A-Za-z][A-Za-z\s]{2,30?})(?:\'s\s+(?:violation|case|record))/ui', $message, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    /**
     * PHP-side automatic tool detection — runs BEFORE calling the LLM.
     * Returns pre-fetched tool data to inject into the LLM context.
     */
    private function autoDetectAndRunTools(string $message): array
    {
        $toolResults = [];
        $lower       = mb_strtolower($message);

        // ── Student lookup ──
        $studentName = $this->extractStudentName($message);
        if ($studentName) {
            $student = \App\Models\Student::where('full_name', 'LIKE', "%{$studentName}%")->first();
            if ($student) {
                $cases = \App\Models\StudentCase::where('student_id', $student->id)
                    ->with('violation')->latest('occurred_at')->get();
                $toolResults[] = [
                    'tool'   => 'get_student_cases',
                    'result' => json_encode([
                        'student'     => $student->full_name,
                        'department'  => $student->department,
                        'year_level'  => $student->year_level,
                        'section'     => $student->section,
                        'total_cases' => $cases->count(),
                        'risk_level'  => $this->calculateRisk($cases->count()),
                        'cases'       => $cases->map(fn($c) => [
                            'date'      => $c->occurred_at
                                ? $c->occurred_at->format('M d, Y')
                                : $c->created_at->format('M d, Y'),
                            'violation' => $c->violation
                                ? "[{$c->violation->code}] {$c->violation->title}"
                                : 'Unknown violation',
                            'severity'  => $c->violation->severity ?? 'N/A',
                            'status'    => $c->status,
                            'sanction'  => $c->sanction ?? 'TBD',
                        ])->toArray(),
                    ]),
                ];
            }
        }

        // ── ALWAYS INCLUDE GLOBAL SYSTEM CONTEXT ──
        $toolResults[] = [
            'tool'   => 'get_system_stats',
            'result' => $this->executeTool('get_system_stats', 'current'),
        ];

        // ── Department queries ──
        if (preg_match('/\b(stats|statistics|system|top violator|most violation|overview|lahat|buod|total|how many|active case|open case|department|dept|CCE|CCJE|CBA|BSIT|CEE)\b/ui', $message)) {
            // If asking about a specific department, also get per-dept breakdown
            if (preg_match('/\b(CCE|CCJE|CBA|BSIT|CEE|[A-Z]{2,5})\b/', $message, $deptMatch)) {
                $dept = $deptMatch[1];
                $deptCases = \App\Models\StudentCase::whereHas('student', fn($q) => $q->where('department', 'LIKE', "%$dept%"))
                    ->where('status', '!=', 'closed')
                    ->count();
                $totalDeptCases = \App\Models\StudentCase::whereHas('student', fn($q) => $q->where('department', 'LIKE', "%$dept%"))->count();
                $toolResults[] = [
                    'tool'   => 'department_stats',
                    'result' => json_encode([
                        'department'    => $dept,
                        'active_cases'  => $deptCases,
                        'total_cases'   => $totalDeptCases,
                    ]),
                ];
            }
        }

        // ── All violations list ──
        if (preg_match('/\b(list.*violation|all.*violation|violation.*list|lahat.*violation|violations.*available)\b/ui', $message)) {
            $toolResults[] = [
                'tool'   => 'get_all_violations',
                'result' => $this->executeTool('get_all_violations', 'all'),
            ];
        }

        return $toolResults;
    }

    public function streamChat(string $message, \Closure $onChunk): void
    {
        set_time_limit(180);
        $this->respondInTagalog = $this->isTagalog($message);
        $relevantHandbooks      = $this->searchHandbooks($message);

        $institutionalContext = [
            'current_date'   => now()->format('l, F j, Y'),
            'school_name'    => 'I-Link CST',
            'assistant_role' => 'Principal Violation Consultant',
        ];

        $history        = [];
        $currentMessage = $message;
        $ollamaWorking  = false;

        // ── Phase 0: PHP auto-detects & fetches tool data BEFORE calling LLM ──
        $autoToolResults = $this->autoDetectAndRunTools($message);
        if (!empty($autoToolResults)) {
            $toolContext = '';
            foreach ($autoToolResults as $tr) {
                $toolContext .= "\n\n[AUTO TOOL: {$tr['tool']}]\n{$tr['result']}";
            }
            $currentMessage = "LIVE DATABASE DATA (retrieved automatically):{$toolContext}\n\n"
                . "USER QUESTION: {$message}\n\n"
                . "INSTRUCTIONS: You are a genius-level AI assistant. Using ONLY the LIVE DATABASE DATA above, provide a brilliant, fast, and natural response to the user's question. NEVER show or mention the raw JSON data, 'LIVE DATABASE DATA', or '[AUTO TOOL]' markers. Present the numbers and facts beautifully and conversationally. Do NOT say you cannot access the database.";
            Log::info("Auto-tool triggered for: $message");
        }

        try {
            // ── Phase 1+2 combined: stream directly from Ollama ──
            // This avoids a slow non-stream call that can timeout (60s+).
            // Auto-tool data is already injected into $currentMessage above.
            $response = $this->callGeminiApi(
                $currentMessage, $relevantHandbooks, $history, true, $onChunk, $institutionalContext
            );

            if (str_starts_with($response, 'Error:')) {
                if (str_contains($response, 'API key is missing')) {
                    $onChunk("⚠️ **AI Disabled:** Walang nakalagay na `GEMINI_API_KEY` sa `.env` file mo kaya naka-dumb mode ang AI ngayon.\n\nPara maging matalino ulit ito, kumuha ng free API key sa [Google AI Studio](https://aistudio.google.com/app/apikey) at ilagay sa `.env` file mo.");
                    return;
                }
                throw new \Exception($response);
            }
            return;

        } catch (\Throwable $e) {
            Log::error('streamChat Gemini error: ' . $e->getMessage());
        }

        // ── Fallback: Local handbook search ──
        $fallback = $this->formatLocalResponse($relevantHandbooks, $message);
        $reply    = strip_tags($fallback['reply'] ?? '');

        if (!empty($reply)) {
            // Stream the local answer word-by-word so it feels natural
            $words = explode(' ', $reply);
            foreach ($words as $word) {
                $onChunk($word . ' ');
                usleep(15000); // 15ms per word
            }
        } else {
            $msg = $this->respondInTagalog
                ? "Paumanhin, hindi ma-contact ang Gemini AI ngayon. Pakisuri ang iyong internet o API key."
                : "The Gemini AI core is temporarily unavailable. Please check your internet connection and API key.";
            $onChunk($msg);
        }
    }

    private function callGeminiApi(string $message, array $relevantHandbooks, array $history = [], bool $stream = false, ?\Closure $onChunk = null, array $institutionalContext = []): string
    {
        try {
            $model   = config('ai.model', 'gemini-2.5-flash');
            $apiKey  = config('ai.api_key');
            if (empty($apiKey)) {
                return "Error: Gemini API key is missing. Please set GEMINI_API_KEY in .env.";
            }

            $today        = $institutionalContext['current_date'] ?? now()->format('l, F j, Y');
            $school       = $institutionalContext['school_name'] ?? 'I-Link CST';
            $role         = $institutionalContext['assistant_role'] ?? 'Principal Violation Consultant';
            $lang         = $this->respondInTagalog
                ? "LANGUAGE: The user's query is in Filipino/Tagalog. Respond entirely in Filipino/Tagalog, but keep technical terms (e.g., violation codes, department names) in their original form."
                : "LANGUAGE: Respond in clear, professional English.";

            if (empty($relevantHandbooks)) {
                $contextString = "No specific handbook sections found for this query.";
            } else {
                $contextData   = array_map(
                    fn($item) => "📌 " . $item['handbook']->title . "\n" . $item['handbook']->content,
                    $relevantHandbooks
                );
                $contextString = implode("\n\n", $contextData);
            }

            $systemPrompt = <<<PROMPT
You are the **Senior OSA Guidance AI** of {$school} — an elite, data-driven institutional intelligence system acting as a {$role}.

Today is {$today}.

━━━ YOUR CAPABILITIES ━━━
You have access to:
1. The official **Student Handbook** (regulatory context below).
2. **Live student records** and **violation case data** via tools.
3. **System-wide statistics** on incidents, trends, and high-risk students.

━━━ HANDBOOK CONTEXT ━━━
{$contextString}

━━━ AVAILABLE TOOLS ━━━
Call tools by emitting EXACTLY this format on its own line:
  [TOOL: tool_name, arg: argument]

Available tools:
• [TOOL: search_students, arg: <name or ID>]  — Find a student
• [TOOL: get_student_cases, arg: <name or ID>] — Get full case history of a student
• [TOOL: analyze_student_incident, arg: <name or ID>] — Risk analysis + recommendations
• [TOOL: get_system_stats, arg: current]        — Top violators + system-wide totals
• [TOOL: get_all_violations, arg: all]          — Full violation reference table

━━━ BEHAVIORAL RULES (STRICT) ━━━
1. **BE ACCURATE**: Never fabricate names, IDs, numbers, or policies. If uncertain, say so.
2. **BE SPECIFIC**: Always include student Name, ID, Department, Violation Count when available.
3. **USE DEPT CODES**: If dept_code (e.g. CEE, CCJE, CBA, BSIT) is in the data, use it.
4. **USE TOOLS PROACTIVELY**: If a query is about a specific student or system data, ALWAYS call the appropriate tool first.
5. **FORMAT BEAUTIFULLY**: Use markdown — **bold**, bullet lists, numbered steps, `code` for IDs, and ### headings for sections.
6. **STEP-BY-STEP REASONING**: For procedural questions (hearings, sanctions, escalations), enumerate every step clearly.
7. **CITE HANDBOOK**: When referencing policies, name the section (e.g., "Chapter 3, Section 2 of the Student Handbook").
8. **SAFETY FIRST**: For urgent or violent incidents, always recommend immediate OSA escalation.
9. **TAGALOG SUPPORT**: {$lang}
10. **BE A GENIUS & CONCISE**: Respond instantly and smartly. Never say "Here is the data". Get straight to the answer.
11. **NEVER EXPOSE JSON/TOOLS**: Never echo raw JSON, `[AUTO TOOL...]`, or "LIVE DATABASE DATA" to the user. Integrate facts naturally.

━━━ RESPONSE STYLE ━━━
- Use **headers** (###) to organize multi-part answers
- Use **bullet points** for lists
- Use **numbered steps** for procedures
- Highlight key info in **bold**
- End complex answers with a short "**📋 Summary**" section
PROMPT;

            $contents = [];
            foreach ($history as $h) {
                $contents[] = [
                    'role' => $h['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $h['content']]]
                ];
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]]
            ];

            $payload = [
                'systemInstruction' => [
                    'parts' => [['text' => $systemPrompt]]
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 1024,
                ]
            ];

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            
            // For now, even if $stream is requested, we'll just do a standard call for simplicity,
            // as true SSE streaming with Gemini in PHP is more complex. We simulate it by calling the callback once.
            
            $response = Http::timeout(30)->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, I couldn't generate a response.";
                
                if ($stream && $onChunk) {
                    $onChunk($content);
                }
                return $content;
            }

            return 'Error: Gemini API Request failed (Status: ' . ($response->status() ?? 'unknown') . '). Response: ' . $response->body();

        } catch (\Exception $e) {
            Log::error('Gemini Error: ' . $e->getMessage());
            return 'Error: ' . $e->getMessage();
        }
    }
    /**
     * Strip <think>...</think> blocks emitted by deepseek-r1 and similar reasoning models.
     * Handles streaming (partial blocks across chunks) via the $inBlock reference.
     */
    private function stripThinkingBlocks(string $text, bool &$inBlock): string
    {
        $result = '';
        $i      = 0;
        $len    = strlen($text);

        while ($i < $len) {
            if (!$inBlock) {
                $open = strpos($text, '<think>', $i);
                if ($open === false) {
                    $result .= substr($text, $i);
                    break;
                }
                $result .= substr($text, $i, $open - $i);
                $inBlock = true;
                $i       = $open + 7; // skip '<think>'
            } else {
                $close = strpos($text, '</think>', $i);
                if ($close === false) {
                    break; // rest of chunk is inside think block — discard
                }
                $inBlock = false;
                $i       = $close + 8; // skip '</think>'
            }
        }

        return $result;
    }

    private function readLine($stream) {
        $buffer = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") break;
            $buffer .= $char;
        }
        return $buffer;
    }

    private function formatLocalResponse(array $relevantHandbooks, string $originalMessage): array
    {
        if (empty($relevantHandbooks)) {
            $reply = "I couldn't find a specific rule regarding that in the student handbook. Please try using different keywords.";
            // If the request likely in Tagalog, provide Tagalog fallback
            if ($this->isTagalog($originalMessage)) {
                $reply = "Hindi ko mahanap ang partikular na alituntunin tungkol diyan sa handbook ng mag-aaral. Pakisubukang gumamit ng ibang mga salita.";
            }
            return [
                'reply' => (string) Str::markdown($reply),
                'sources' => []
            ];
        }

        $response = "";
        $sources = [];
        
        $isPenaltyQuestion = Str::contains(strtolower($originalMessage), ['penalty', 'sanction', 'offense', 'punishment']);
        
        foreach ($relevantHandbooks as $item) {
            $handbook = $item['handbook'];
            $matches = $item['matches'];
            
            if ($isPenaltyQuestion) {
                 $matches = array_merge($matches, ['sanction', 'penalty', 'dismissal', 'suspension', 'warning', 'expulsion']);
            }

            $snippet = $this->createSmartSnippet($handbook->content, $matches);
            
            $response .= "📌 **{$handbook->title}**\n\n";
            $response .= "{$snippet}\n\n";
            $sources[] = $handbook->title;
        }
        
        return [
            'reply' => (string) Str::markdown(trim($response)),
            'sources' => array_values(array_unique($sources))
        ];
    }
    
    private function createSmartSnippet(string $content, array $matches): string
    {
         if (empty($matches)) {
            // Return longer snippet for better context when no matches, and consider Tagalog if needed
            $limit = $this->respondInTagalog ? 800 : 500;
            return Str::limit($content, $limit);
         }

         $contentLower = strtolower($content);
         $bestPos = -1;
         $maxScore = 0;

         // Scan for keyword density
         $windowSize = 400; // Increased window size for context
         $step = 50; 
         
         for ($i = 0; $i < strlen($content); $i += $step) {
             if ($i + $windowSize > strlen($content)) break;
             
             $chunk = substr($contentLower, $i, $windowSize);
             $score = 0;
             foreach ($matches as $word) {
                 if (strlen($word) < 3) continue;
                 $score += substr_count($chunk, strtolower($word));
             }
             
             if ($score > $maxScore) {
                 $maxScore = $score;
                 $bestPos = $i;
             }
         }

         if ($bestPos === -1) {
             // Fallback: first occurrence
             $firstPos = strlen($content);
             foreach ($matches as $word) {
                if (strlen($word) < 3) continue;
                $pos = strpos($contentLower, $word);
                if ($pos !== false && $pos < $firstPos) {
                    $firstPos = $pos;
                }
             }
             $start = ($firstPos === strlen($content)) ? 0 : max(0, $firstPos - 50);
         } else {
             $start = $bestPos;
         }

         // INCREASED SNIPPET LENGTH FOR "COMPLETE TEXT" REQ
         $length = 1000; 
         $snippet = substr($content, $start, $length);
         
         if ($start > 0) $snippet = "..." . $snippet;
         if (($start + $length) < strlen($content)) $snippet .= "...";
         
         return $this->highlightKeywords($snippet, $matches);
    }
    
    private function highlightKeywords(string $text, array $keywords): string
    {
        foreach ($keywords as $word) {
             if (strlen($word) < 3) continue;
             $safeWord = preg_quote($word, '/');
             $text = preg_replace("/($safeWord)/i", "**$1**", $text);
        }
        // If responding in Tagalog, ensure any English terms are also highlighted for clarity
        if ($this->respondInTagalog) {
            $text = preg_replace('/\b(\w{2,})\b/u', '**$1**', $text);
        }
        return $text;
    }

    /**
     * Simple heuristic to detect Tagalog language in the user message.
     */
    private function isTagalog(string $message): bool
    {
        $tagalogWords = ['ano', 'paano', 'sino', 'kung', 'bakit', 'kapag', 'kailan', 'gusto', 'tawag', 'magtanong', 'tulungan', 'paalam', 'salamat', 'paki', 'pakisabi'];
        $lower = strtolower($message);
        foreach ($tagalogWords as $word) {
            if (strpos($lower, $word) !== false) {
                return true;
            }
        }
        // Additional detection: presence of 'ng' or 'sa' as common Tagalog particles
        if (preg_match('/\b(ng|sa|ay)\b/', $lower)) {
            return true;
        }
        return false;
    }
}
