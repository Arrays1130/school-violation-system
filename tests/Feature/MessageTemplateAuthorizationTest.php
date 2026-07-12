<?php

namespace Tests\Feature;

use App\Models\Handbook;
use App\Models\MessageTemplate;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTemplateAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_access_message_templates(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('message-templates.index'))
            ->assertForbidden();
    }

    public function test_dean_can_access_message_templates(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->get(route('message-templates.index'))
            ->assertOk();
    }

    public function test_dean_can_create_message_template(): void
    {
        $dean = User::factory()->dean('CCE')->create();

        $this->actingAs($dean)
            ->post(route('message-templates.store'), [
                'title' => 'Parent Notice',
                'content' => 'Dear parent, your child {student_name} has a violation.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('message_templates', ['title' => 'Parent Notice']);
    }

    public function test_dean_can_update_and_delete_message_template(): void
    {
        $dean = User::factory()->dean('CCE')->create();
        $template = MessageTemplate::create([
            'title' => 'Notice',
            'content' => 'Hello {student_name}',
            'created_by' => $dean->id,
        ]);

        $this->actingAs($dean)
            ->put(route('message-templates.update', $template), [
                'title' => 'Updated Notice',
                'content' => 'Updated {student_name}',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('message_templates', ['title' => 'Updated Notice']);

        $this->actingAs($dean)
            ->delete(route('message-templates.destroy', $template))
            ->assertRedirect();

        $this->assertDatabaseMissing('message_templates', ['id' => $template->id]);
    }
}
