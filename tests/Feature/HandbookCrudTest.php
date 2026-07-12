<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AiEmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HandbookCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_handbook_and_triggers_embedding_index(): void
    {
        $admin = User::factory()->admin()->create();

        $mock = Mockery::mock(AiEmbeddingService::class);
        $mock->shouldReceive('indexHandbook')->once()->andReturn(3);
        $this->app->instance(AiEmbeddingService::class, $mock);

        $this->actingAs($admin)
            ->post(route('handbooks.store'), [
                'title' => 'Uniform Policy',
                'content' => 'Students must wear proper uniform at all times.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('handbooks', ['title' => 'Uniform Policy']);
    }
}
