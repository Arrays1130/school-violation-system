<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_import_shows_generic_error(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->post(route('students.import'), [
                'file' => UploadedFile::fake()->create('students.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Unable to import students. Please verify the file format and try again.');
    }
}
