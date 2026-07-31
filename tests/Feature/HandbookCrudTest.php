<?php

namespace Tests\Feature;

use App\Models\Handbook;
use App\Models\User;
use App\Services\AiEmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_admin_can_upload_handbook_pdf(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();

        $mock = Mockery::mock(AiEmbeddingService::class);
        $mock->shouldReceive('indexHandbook')->once()->andReturn(1);
        $this->app->instance(AiEmbeddingService::class, $mock);

        $file = UploadedFile::fake()->create('student-handbook.pdf', 120, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('handbooks.store'), [
                'title' => 'Uploaded Handbook',
                'content' => '',
                'document' => $file,
            ])
            ->assertRedirect(route('handbooks.index'));

        $handbook = Handbook::query()->where('title', 'Uploaded Handbook')->first();
        $this->assertNotNull($handbook);
        $this->assertNotNull($handbook->file_path);
        $this->assertSame('student-handbook.pdf', $handbook->file_name);
        Storage::disk('local')->assertExists($handbook->file_path);
    }
}
