<?php

namespace Tests\Feature;

use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class AiServiceLogicTest extends TestCase
{
    use RefreshDatabase;

    private function invokeAutoDetect(string $message): array
    {
        $service = app(AiService::class);
        $method = new ReflectionMethod(AiService::class, 'autoDetectAndRunTools');
        $method->setAccessible(true);

        return $method->invoke($service, $message);
    }

    private function toolNames(array $results): array
    {
        return array_column($results, 'tool');
    }

    public function test_policy_questions_do_not_prefetch_system_stats(): void
    {
        $tools = $this->toolNames($this->invokeAutoDetect('What is the uniform policy?'));

        $this->assertNotContains('get_system_stats', $tools);
    }

    public function test_stats_questions_prefetch_system_stats(): void
    {
        $tools = $this->toolNames($this->invokeAutoDetect('How many open cases are there?'));

        $this->assertContains('get_system_stats', $tools);
    }

    public function test_violation_list_questions_prefetch_violations_catalog(): void
    {
        $tools = $this->toolNames($this->invokeAutoDetect('Show me the full violation list'));

        $this->assertContains('get_all_violations', $tools);
        $this->assertNotContains('get_system_stats', $tools);
    }

    public function test_create_smart_snippet_respects_max_length(): void
    {
        $service = app(AiService::class);
        $method = new ReflectionMethod(AiService::class, 'createSmartSnippet');
        $method->setAccessible(true);

        $content = str_repeat('uniform policy rule ', 200);
        $snippet = $method->invoke($service, $content, ['uniform'], 600);

        $this->assertLessThan(strlen($content), strlen($snippet));
        $this->assertStringContainsString('uniform', $snippet);
    }

    public function test_search_violations_matches_offense_codes(): void
    {
        \App\Models\Violation::factory()->create([
            'code' => 'V-099',
            'title' => 'Improper Uniform',
            'severity' => 'Minor',
        ]);

        $service = app(AiService::class);
        $method = new ReflectionMethod(AiService::class, 'buildSearchContext');
        $method->setAccessible(true);

        $context = $method->invoke($service, 'What is the sanction for V-099?');

        $this->assertNotEmpty($context['violations']);
        $this->assertSame('V-099', $context['violations'][0]['violation']->code);
    }

    public function test_extract_student_name_from_record_for_pattern(): void
    {
        $service = app(AiService::class);
        $method = new ReflectionMethod(AiService::class, 'extractStudentName');
        $method->setAccessible(true);

        $this->assertSame('Castillanes Jayson', $method->invoke($service, 'Show the case record for Castillanes Jayson'));
        $this->assertSame('Castillanes Jayson', $method->invoke($service, 'can you check the CASTILLANES JAYSON case record'));
    }

    public function test_department_stats_resolve_shortcuts_to_full_names(): void
    {
        $dept = config('school.departments.CCE');

        $student = \App\Models\Student::factory()->create([
            'department' => $dept,
        ]);

        \App\Models\StudentCase::factory()->create([
            'student_id' => $student->id,
            'status' => 'Open',
        ]);

        $tools = $this->invokeAutoDetect('how many case of CCE deparment');
        $this->assertContains('department_stats', $this->toolNames($tools));

        $deptStats = collect($tools)->firstWhere('tool', 'department_stats');
        $payload = json_decode($deptStats['result'], true);

        $this->assertSame('CCE', $payload['department']);
        $this->assertSame($dept, $payload['department_full_name']);
        $this->assertSame(1, $payload['total_cases']);
    }

    public function test_page_context_tools_prefetch_student_cases(): void
    {
        $dept = config('school.departments.CCE');
        $student = \App\Models\Student::factory()->create(['department' => $dept]);
        \App\Models\StudentCase::factory()->create(['student_id' => $student->id]);

        $service = app(AiService::class);
        $pageContextProp = new \ReflectionProperty(AiService::class, 'pageContext');
        $pageContextProp->setAccessible(true);
        $pageContextProp->setValue($service, ['student_id' => $student->id]);

        $method = new ReflectionMethod(AiService::class, 'buildPageContextTools');
        $method->setAccessible(true);
        $tools = $method->invoke($service);

        $this->assertNotEmpty($tools);
        $this->assertSame('get_student_cases', $tools[0]['tool']);
    }

    public function test_department_alias_bsit_maps_to_cce(): void
    {
        $cceDept = config('school.departments.CCE');
        $student = \App\Models\Student::factory()->create(['department' => $cceDept]);
        \App\Models\StudentCase::factory()->create(['student_id' => $student->id]);

        $tools = $this->invokeAutoDetect('how many cases in BSIT department');
        $this->assertContains('department_stats', $this->toolNames($tools));
    }

    public function test_sanitize_removes_tool_narration(): void
    {
        $service = app(AiService::class);
        $method = new ReflectionMethod(AiService::class, 'sanitizeAssistantReply');
        $method->setAccessible(true);

        $dirty = "Policy answer here.\nI will now use the get_student_cases function.\n```python\nprint(test)\n```\nFinal answer.";
        $clean = $method->invoke($service, $dirty);

        $this->assertStringContainsString('Policy answer here.', $clean);
        $this->assertStringContainsString('Final answer.', $clean);
        $this->assertStringNotContainsString('get_student_cases', $clean);
        $this->assertStringNotContainsString('print(test)', $clean);
    }
}
