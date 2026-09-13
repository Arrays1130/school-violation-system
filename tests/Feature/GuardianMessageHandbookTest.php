<?php

namespace Tests\Feature;

use App\Models\Handbook;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use App\Services\AiService;
use App\Services\GeminiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GuardianMessageHandbookTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_guardian_message_includes_matching_handbook_sources(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create([
            'guardian_name' => 'Shirley Castillanes',
        ]);
        $violation = Violation::factory()->create([
            'title' => 'Improper Uniform',
            'severity' => 'Minor',
        ]);
        $case = StudentCase::createForStaff([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'description' => 'Student was not in proper uniform.',
            'occurred_at' => now()->subDay(),
            'offense_level' => 1,
            'sanction' => 'Verbal warning',
        ], $admin->id);

        Handbook::create([
            'title' => 'Uniform and Attire Policy',
            'content' => 'Students must wear the official school uniform and prescribed attire while on campus. Improper uniform is a conduct offense with progressive sanctions.',
        ]);

        $capturedPrompt = null;
        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('generate')
            ->once()
            ->andReturnUsing(function (array $contents, string $systemPrompt) use (&$capturedPrompt) {
                $capturedPrompt = ($contents[0]['parts'][0]['text'] ?? '')."\n".$systemPrompt;

                return [
                    'type' => 'text',
                    'text' => 'Good day, Shirley Castillanes. Your child has an improper uniform violation. Please contact OSA.',
                ];
            });

        $this->app->instance(GeminiClient::class, $gemini);

        $result = app(AiService::class)->generateGuardianMessage($student, $case);

        $this->assertSame('gemini', $result['mode']);
        $this->assertNotEmpty($result['message']);
        $this->assertContains('Uniform and Attire Policy', $result['handbook_sources']);
        $this->assertStringContainsString('handbook', strtolower((string) $capturedPrompt));
        $this->assertStringContainsString('Uniform and Attire Policy', (string) $capturedPrompt);
        $this->assertStringContainsString('MUST include a policy paragraph', (string) $capturedPrompt);
        $this->assertStringContainsString('According to school policy', (string) $capturedPrompt);
    }

    public function test_fallback_guardian_message_includes_handbook_policy(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create([
            'guardian_name' => 'Shirley',
            'full_name' => 'JAYSON CASTILLANES',
        ]);
        $violation = Violation::factory()->create([
            'title' => 'Improper Uniform',
            'severity' => 'Minor',
        ]);
        $case = StudentCase::createForStaff([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'description' => 'Student was not in proper uniform.',
            'occurred_at' => now()->subDay(),
            'offense_level' => 1,
            'sanction' => 'COMMUNITY SERVICE',
        ], $admin->id);

        Handbook::create([
            'title' => 'Uniform and Attire Policy',
            'content' => 'Students must wear the official school uniform and prescribed attire while on campus.',
        ]);

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('generate')->once()->andReturn([
            'type' => 'error',
            'error' => 'unavailable',
        ]);
        $this->app->instance(GeminiClient::class, $gemini);

        $result = app(AiService::class)->generateGuardianMessage($student, $case);

        $this->assertSame('fallback', $result['mode']);
        $this->assertStringContainsString('According to school policy', $result['message']);
        $this->assertStringContainsString('Uniform and Attire Policy', $result['message']);
        $this->assertStringContainsString('official school uniform', $result['message']);
        $this->assertContains('Uniform and Attire Policy', $result['handbook_sources']);
    }

    public function test_generate_guardian_message_endpoint_returns_handbook_sources(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $student = Student::factory()->create();
        $violation = Violation::factory()->create([
            'title' => 'Improper Uniform',
            'severity' => 'Minor',
        ]);
        $case = StudentCase::createForStaff([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
            'description' => 'Not in uniform',
            'occurred_at' => now()->subDay(),
            'offense_level' => 1,
            'sanction' => 'Verbal warning',
        ], $admin->id);

        Handbook::create([
            'title' => 'Uniform and Attire Policy',
            'content' => 'Official school uniform is required. Improper uniform violates the attire policy.',
        ]);

        $gemini = Mockery::mock(GeminiClient::class);
        $gemini->shouldReceive('generate')->andReturn([
            'type' => 'text',
            'text' => 'Draft guardian notice about improper uniform.',
        ]);
        $this->app->instance(GeminiClient::class, $gemini);

        $this->actingAs($admin)
            ->postJson(route('students.generateGuardianMessage', $student), [
                'case_id' => $case->id,
            ])
            ->assertOk()
            ->assertJsonPath('mode', 'gemini')
            ->assertJsonFragment(['Uniform and Attire Policy']);
    }
}
