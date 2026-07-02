<?php

namespace Tests\Feature;

use App\Models\CaseAttachment;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_repository_index_paginates_without_loading_all_records(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $violation = Violation::factory()->create();
        $student = Student::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        for ($i = 0; $i < 12; $i++) {
            $path = "attachments/file-{$i}.pdf";
            Storage::disk('local')->put($path, 'content');

            CaseAttachment::create([
                'case_id' => $case->id,
                'uploaded_by' => $admin->id,
                'file_name' => "file-{$i}.pdf",
                'file_path' => $path,
                'file_type' => 'application/pdf',
                'file_size' => 7,
            ]);
        }

        $this->actingAs($admin)
            ->get(route('meeting-minutes.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('records.data', 10)
                ->where('records.total', 12)
            );
    }
}
