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
        
        $relevantHandbooks = $this->searchHandbooks($message);

        $institutionalContext = [
            'current_date' => now()->format('l, F j, Y'),
            'school_name' => 'I-Link CST',
            'assistant_role' => 'Principal Disciplinary Consultant',
        ];

        $conversationHistory = [];
        $currentMessage = $message;

        for ($i = 0; $i < 3; $i++) {
            $response = $this->callOllamaApi($currentMessage, $relevantHandbooks, $conversationHistory, false, null, $institutionalContext);
            
            if (str_starts_with($response, 'Error:')) {
                Log::warning("Ollama API failed or unavailable. Falling back to local handbook search. Details: " . $response);
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
                    $student = \App\Models\Student::where('student_id', $arg)->orWhere('full_name', 'LIKE', "%$arg%")->first();
                    if (!$student) return "Error: Student not found.";
                    
                    $casesCount = \App\Models\StudentCase::where('student_id', $student->id)->count();
                    $lastCase = \App\Models\StudentCase::where('student_id', $student->id)->with('violation')->latest()->first();
                    
                    return json_encode([
                        'analysis_for' => $student->full_name,
                        'id' => $student->student_id,
                        'department' => $student->department,
                        'year_level' => $student->year_level,
                        'total_violations_recorded' => $casesCount,
                        'recidivism_level' => $this->calculateRisk($casesCount),
                        'last_offense' => $lastCase ? $lastCase->violation->title : 'No record',
                        'recommendation' => 'Check Chapter 4 Section 2 of Handbook for severity Escalation.'
                    ]);

                case 'search_students':
                    $students = \App\Models\Student::where('full_name', 'LIKE', "%$arg%")
                        ->orWhere('student_id', 'LIKE', "%$arg%")
                        ->limit(5)
                        ->get(['id', 'full_name', 'student_id', 'department', 'year_level']);
                    return $students->isEmpty() ? "No students found." : $students->toJson();

                case 'get_student_cases':
                    $student = \App\Models\Student::where('student_id', $arg)
                        ->orWhere('full_name', 'LIKE', "%$arg%")
                        ->first();
                    if (!$student) return "Student $arg not found.";
                    $cases = \App\Models\StudentCase::where('student_id', $student->id)
                        ->with('violation')
                        ->latest('occurred_at')
                        ->get();
                    return $cases->isEmpty() ? "Clean record. No cases found." : $cases->map(fn($c) => [
                        'date' => $c->occurred_at ? $c->occurred_at->format('M d, Y') : $c->created_at->format('M d, Y'),
                        'violation' => "[{$c->violation->code}] {$c->violation->title}",
                        'severity' => $c->violation->severity,
                        'status' => $c->status,
                        'sanction' => $c->sanction ?? 'TBD'
                    ])->toJson();

                case 'get_system_stats':
                    $totalCases = \App\Models\StudentCase::count();
                    $activeViolators = \App\Models\StudentCase::selectRaw('student_id, COUNT(*) as count')
                        ->with('student')
                        ->groupBy('student_id')
                        ->orderByDesc('count')
                        ->limit(3)
                        ->get();
                    return json_encode([
                        'total_system_records' => $totalCases,
                        'top_frequent_violators' => $activeViolators->map(fn($v) => [
                            'name' => $v->student->full_name,
                            'department' => $v->student->department,
                            'dept_code' => $v->student->department_shortcut, // CEE, CCJE, etc
                            'violation_count' => $v->count
                        ])
                    ]);

                case 'get_all_violations':
                    return \App\Models\Violation::all(['code', 'title', 'severity'])->toJson();

                default:
                    return "Tool $name not found.";
            }
        } catch (\Exception $e) {
            return "Error in tool $name: " . $e->getMessage();
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
                       ->orWhere('title', 'LIKE', '%Disciplinary%')
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

    public function streamChat(string $message, \Closure $onChunk): void
    {
        set_time_limit(150);
        $relevantHandbooks = $this->searchHandbooks($message);
        
        $institutionalContext = [
            'current_date' => now()->format('l, F j, Y'),
            'school_name' => 'I-Link CST',
            'assistant_role' => 'Principal Disciplinary Consultant',
        ];

        $history = [];
        $currentMessage = $message;

        // Tool Pre-processing (Synchronous)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->callOllamaApi($currentMessage, $relevantHandbooks, $history, false, null, $institutionalContext);
            
            if (preg_match('/\[TOOL:\s*(\w+)\s*,\s*(.*?)\]/', $response, $matches)) {
                $toolName = trim($matches[1]);
                $toolArg = trim($matches[2]);
                $toolResult = $this->executeTool($toolName, $toolArg);
                
                $history[] = ['role' => 'assistant', 'content' => $response];
                $history[] = ['role' => 'system', 'content' => "CRITICAL SYSTEM DATA ($toolName): " . $toolResult];
                $currentMessage = "SYSTEM DATA RETRIEVED: $toolResult. Now provide the final authoritative answer. Do not hallucinate.";
                continue;
            }
            break; 
        }

        // Final Streaming Response
        $this->callOllamaApi($currentMessage, $relevantHandbooks, $history, true, $onChunk, $institutionalContext);
    }

    private function callOllamaApi(string $message, array $relevantHandbooks, array $history = [], bool $stream = false, ?\Closure $onChunk = null, array $institutionalContext = []): string
    {
        try {
            $model = env('OLLAMA_MODEL', 'llama3.1:latest');
            $baseUrl = env('OLLAMA_API_URL', 'http://127.0.0.1:11434');

            if (empty($relevantHandbooks)) {
                $contextString = "No specific handbook sections found.";
            } else {
                $contextData = array_map(fn($item) => "TITLE: {$item['handbook']->title}\nCONTENT: {$item['handbook']->content}", $relevantHandbooks);
                $contextString = implode("\n\n", $contextData);
            }

            $systemPrompt = "You are the Senior OSA Dean's Data-Driven AI Assistant for I-Link CST.\n" .
                          "Handbook Regulations Context:\n{$contextString}\n\n" .
                          "OPERATIONAL RULES (STRICT):\n" .
                          "1. DATA ACCURACY IS MANDATORY. Include Name, ID, Department, and Violation Count.\n" .
                          "2. If 'dept_code' is present (e.g. CEE, CCJE), USE IT in your response.\n" .
                          "3. Use [TOOL: get_system_stats, arg: current] for top violators.\n";

            // Messages array construction
            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach ($history as $h) $messages[] = $h;
            $messages[] = ['role' => 'user', 'content' => $message];

            if ($stream) {
                $client = new \GuzzleHttp\Client(['connect_timeout' => 2, 'timeout' => 300]);
                $response = $client->post("{$baseUrl}/api/chat", [
                    'json' => [
                        'model' => $model,
                        'messages' => $messages,
                        'stream' => true,
                    ],
                    'stream' => true,
                ]);

                $body = $response->getBody();
                $fullResponse = "";

                while (!$body->eof()) {
                    $line = $this->readLine($body);
                    if (empty($line)) continue;
                    
                    $data = json_decode($line, true);
                    $chunk = $data['message']['content'] ?? "";
                    $fullResponse .= $chunk;
                    
                    if ($onChunk) $onChunk($chunk);
                }
                return $fullResponse;
            }

            $response = Http::connectTimeout(2)->timeout(30)->post("{$baseUrl}/api/chat", [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['message']['content'] ?? "Sorry, I couldn't generate a response.";
            }

            return "Error: Ollama API Request failed.";

        } catch (\Exception $e) {
            Log::error("Ollama Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
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
            return [
                'reply' => (string) Str::markdown("I couldn't find a specific rule regarding that in the student handbook. Please try using different keywords."),
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
             return Str::limit($content, 500);
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
        return $text;
    }
}
