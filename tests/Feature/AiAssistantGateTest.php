<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiUsageLog;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiAssistantGateTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_guest_cannot_access_ai_assistant(): void
    {
        $this->get(route('ai-assistant.index'))
            ->assertRedirect(route('login'));
    }

    public function test_dean_can_access_ai_assistant_page(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->get(route('ai-assistant.index'))
            ->assertOk();
    }

    public function test_ai_chat_logs_usage(): void
    {
        $admin = User::factory()->admin()->create();

        $mock = Mockery::mock(AiService::class);
        $mock->shouldReceive('processChat')
            ->once()
            ->andReturn([
                'reply' => 'Test reply',
                'sources' => [['id' => 1, 'title' => 'Handbook A', 'url' => '/handbooks/1', 'type' => 'handbook']],
                'mode' => 'gemini',
            ]);
        $this->app->instance(AiService::class, $mock);

        $this->actingAs($admin)
            ->postJson(route('ai-assistant.chat'), ['message' => 'What is the uniform policy?'])
            ->assertOk()
            ->assertJsonFragment(['mode' => 'gemini'])
            ->assertJsonStructure(['conversationId', 'usageLogId']);

        $this->assertDatabaseHas('ai_usage_logs', [
            'user_id' => $admin->id,
            'channel' => 'chat',
        ]);

        $this->assertDatabaseHas('ai_messages', [
            'role' => 'user',
            'content' => 'What is the uniform policy?',
        ]);
    }

    public function test_ai_feedback_requires_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->create();

        $log = AiUsageLog::create([
            'user_id' => $other->id,
            'message' => 'test',
            'response_length' => 10,
            'channel' => 'chat',
        ]);

        $this->actingAs($admin)
            ->postJson(route('ai-assistant.feedback'), [
                'usage_log_id' => $log->id,
                'rating' => 1,
            ])
            ->assertNotFound();
    }
}
