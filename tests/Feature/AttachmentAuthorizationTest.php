<?php

namespace Tests\Feature;

use App\Models\CaseAttachment;
use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AttachmentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dean_cannot_download_attachment_from_other_department(): void
    {
        Storage::fake('local');

        $dean = User::factory()->dean('CCE')->create();
        $ccjeStudent = Student::factory()->inDepartment('CCJE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $ccjeStudent->id,
            'violation_id' => $violation->id,
        ]);

        $path = 'attachments/test.pdf';
        Storage::disk('local')->put($path, 'secret');

        $attachment = CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => User::factory()->admin()->create()->id,
            'file_name' => 'test.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 6,
        ]);

        $this->actingAs($dean)
            ->get(route('attachments.download', $attachment))
            ->assertForbidden();
    }

    public function test_dean_can_download_attachment_from_own_department(): void
    {
        Storage::fake('local');

        $dean = User::factory()->dean('CCE')->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $path = 'attachments/allowed.pdf';
        Storage::disk('local')->put($path, 'allowed');

        $attachment = CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => User::factory()->admin()->create()->id,
            'file_name' => 'allowed.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 7,
        ]);

        $this->actingAs($dean)
            ->get(route('attachments.download', $attachment))
            ->assertOk();
    }

    public function test_dean_cannot_delete_attachments(): void
    {
        Storage::fake('local');

        $dean = User::factory()->dean('CCE')->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $path = 'attachments/delete-me.pdf';
        Storage::disk('local')->put($path, 'data');

        $attachment = CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => User::factory()->admin()->create()->id,
            'file_name' => 'delete-me.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 4,
        ]);

        $this->actingAs($dean)
            ->delete(route('attachments.destroy', $attachment))
            ->assertForbidden();
    }

    public function test_signed_attachment_download_requires_valid_signature(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $student = Student::factory()->inDepartment('CCE')->create();
        $violation = Violation::factory()->create();
        $case = StudentCase::factory()->create([
            'student_id' => $student->id,
            'violation_id' => $violation->id,
        ]);

        $path = 'attachments/signed.pdf';
        Storage::disk('local')->put($path, 'signed-content');

        $attachment = CaseAttachment::create([
            'case_id' => $case->id,
            'uploaded_by' => $admin->id,
            'file_name' => 'signed.pdf',
            'file_path' => $path,
            'file_type' => 'application/pdf',
            'file_size' => 12,
        ]);

        $validUrl = URL::temporarySignedRoute(
            'attachments.signed-download',
            now()->addMinutes(10),
            ['attachment' => $attachment->id]
        );

        $this->actingAs($admin)->get($validUrl)->assertOk();
        $this->actingAs($admin)->get(route('attachments.signed-download', $attachment))->assertForbidden();
    }
}
