<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiPageContextTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_assistant_page_includes_student_context_from_query(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $student = Student::factory()->create(['full_name' => 'CASTILLANES JAYSON']);

        $this->actingAs($admin)
            ->get(route('ai-assistant.index', [
                'student_id' => $student->id,
                'prompt' => 'Show violation record',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('AiAssistant/Index')
                ->where('pageContext.student_id', $student->id)
                ->where('pageContext.student.name', 'CASTILLANES JAYSON')
                ->where('initialPrompt', 'Show violation record')
            );
    }
}
