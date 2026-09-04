<?php

namespace App\Support;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\User;
use Illuminate\Http\Request;

class AiAssistantContext
{
  /**
   * @return array<string, mixed>|null
   */
  public static function fromRequest(Request $request, ?User $user = null): ?array
  {
    $user ??= $request->user();
    if (! $user) {
      return null;
    }

    $context = [
      'source' => $request->string('source')->toString() ?: null,
      'prompt' => $request->string('prompt')->toString() ?: null,
    ];

    if ($request->filled('case_id')) {
      $case = StudentCase::query()
        ->forUser($user)
        ->with(['student:id,full_name,department,year_level,section', 'violation:id,code,title,severity'])
        ->find((int) $request->input('case_id'));

      if ($case) {
        $context['case_id'] = $case->id;
        $context['case'] = [
          'id' => $case->id,
          'status' => $case->status,
          'occurred_at' => $case->occurred_at?->toDateString(),
          'violation_code' => $case->violation?->code,
          'violation_title' => $case->violation?->title,
          'student_id' => $case->student_id,
          'student_name' => $case->student?->full_name,
        ];
      }
    }

    if ($request->filled('student_id')) {
      $student = Student::query()
        ->forUser($user)
        ->find((int) $request->input('student_id'));

      if ($student) {
        $context['student_id'] = $student->id;
        $context['student'] = [
          'id' => $student->id,
          'name' => $student->full_name,
          'department' => $student->department,
          'year_level' => $student->year_level,
          'section' => $student->section,
        ];
      }
    }

    if (count(array_filter($context, fn ($value) => $value !== null)) === 0) {
      return null;
    }

    return $context;
  }

  /**
   * @param  array<string, mixed>|null  $context
   */
  public static function defaultPrompt(?array $context): ?string
  {
    if (! $context) {
      return null;
    }

    if (! empty($context['prompt'])) {
      return (string) $context['prompt'];
    }

    if (! empty($context['case']['student_name'])) {
      $caseId = str_pad((string) ($context['case']['id'] ?? ''), 4, '0', STR_PAD_LEFT);

      return "Brief case #{$caseId} for {$context['case']['student_name']}: status, recommended sanction, and the next OSA step.";
    }

    if (! empty($context['student']['name'])) {
      return "Pull the live case record for {$context['student']['name']} and tell me what to do next.";
    }

    return null;
  }
}
