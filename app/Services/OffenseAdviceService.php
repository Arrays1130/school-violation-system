<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentCase;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OffenseAdviceService
{
    public const DEFAULT_SANCTION = 'Sanction pending determination.';

    public const RECURRING_SANCTION = 'Recurring Offense (Refer to Student Affairs)';

    /**
     * Next offense level if a new case for this violation is recorded now.
     */
    public function offenseLevelFor(int $studentId, int $violationId, ?Builder $caseQuery = null): int
    {
        return $this->priorSameViolationCount($studentId, $violationId, $caseQuery) + 1;
    }

    public function priorSameViolationCount(int $studentId, int $violationId, ?Builder $caseQuery = null): int
    {
        return $this->baseCaseQuery($caseQuery)
            ->where('student_id', $studentId)
            ->where('violation_id', $violationId)
            ->count();
    }

    public function sanctionFor(Violation $violation, int $offenseLevel): string
    {
        $sanction = match ($offenseLevel) {
            1 => $violation->first_offense,
            2 => $violation->second_offense,
            3 => $violation->third_offense,
            default => self::RECURRING_SANCTION,
        };

        return $sanction ?: self::DEFAULT_SANCTION;
    }

    /**
     * @return array{
     *   total_minors: int,
     *   triggers_escalation_now: bool,
     *   next_escalation_at: int,
     *   minors_until_escalation: int,
     *   escalation_level: int|null,
     *   note: string
     * }
     */
    public function minorEscalationForecast(int $studentId, ?Builder $caseQuery = null, int $additionalMinors = 0): array
    {
        $totalMinors = $this->baseCaseQuery($caseQuery)
            ->where('student_id', $studentId)
            ->whereHas('violation', fn (Builder $q) => $q->where('severity', 'Minor'))
            ->count() + $additionalMinors;

        $triggersNow = $totalMinors > 0 && $totalMinors % 3 === 0;
        $remainder = $totalMinors % 3;
        $minorsUntil = $remainder === 0 ? ($totalMinors === 0 ? 3 : 0) : 3 - $remainder;
        $nextAt = $totalMinors + ($minorsUntil === 0 && $totalMinors > 0 ? 3 : $minorsUntil);
        $escalationLevel = $triggersNow ? (int) ($totalMinors / 3) : null;

        $note = match (true) {
            $totalMinors === 0 => 'No minor offenses on record.',
            $triggersNow => "Student has {$totalMinors} minor offenses, which triggers a Major (SYS-001) escalation (level {$escalationLevel}).",
            default => "Student has {$totalMinors} minor offense(s). {$minorsUntil} more minor(s) until Major escalation.",
        };

        return [
            'total_minors' => $totalMinors,
            'triggers_escalation_now' => $triggersNow,
            'next_escalation_at' => $nextAt,
            'minors_until_escalation' => $minorsUntil === 0 && $totalMinors > 0 ? 3 : $minorsUntil,
            'escalation_level' => $escalationLevel,
            'note' => $note,
        ];
    }

    /**
     * @return array{
     *   total_cases: int,
     *   open_cases: int,
     *   closed_cases: int,
     *   severity_breakdown: array{Minor: int, Major: int},
     *   risk_level: string,
     *   risk_reasons: array<int, string>,
     *   escalation: array,
     *   recommendation: string,
     *   next_steps: array<int, string>
     * }
     */
    public function analyzeStudent(Student $student, ?Builder $caseQuery = null): array
    {
        $cases = $this->baseCaseQuery($caseQuery)
            ->where('student_id', $student->id)
            ->with('violation')
            ->latest('occurred_at')
            ->get();

        $open = $cases->whereNotIn('status', ['Closed', 'Dismissed']);
        $severity = [
            'Minor' => $cases->filter(fn ($c) => ($c->violation?->severity ?? '') === 'Minor')->count(),
            'Major' => $cases->filter(fn ($c) => ($c->violation?->severity ?? '') === 'Major')->count(),
        ];

        $escalation = $this->minorEscalationForecast($student->id, $caseQuery);
        $risk = $this->riskFromCases($cases, $escalation);

        return [
            'total_cases' => $cases->count(),
            'open_cases' => $open->count(),
            'closed_cases' => $cases->count() - $open->count(),
            'severity_breakdown' => $severity,
            'risk_level' => $risk['level'],
            'risk_reasons' => $risk['reasons'],
            'escalation' => $escalation,
            'recommendation' => $risk['recommendation'],
            'next_steps' => $risk['next_steps'],
        ];
    }

    /**
     * Advice if recording this violation now (before save).
     *
     * @return array<string, mixed>
     */
    public function suggestForNewViolation(Student $student, Violation $violation, ?Builder $caseQuery = null): array
    {
        $level = $this->offenseLevelFor($student->id, $violation->id, $caseQuery);
        $sanction = $this->sanctionFor($violation, $level);
        $escalation = $violation->severity === 'Minor'
            ? $this->minorEscalationForecast($student->id, $caseQuery, 1)
            : $this->minorEscalationForecast($student->id, $caseQuery, 0);

        $nextSteps = [
            "Record violation [{$violation->code}] {$violation->title} as offense #{$level}.",
            "Apply catalog sanction: {$sanction}.",
        ];

        if ($violation->severity === 'Minor' && ($escalation['triggers_escalation_now'] ?? false)) {
            $nextSteps[] = 'System will also generate Major offense SYS-001 due to reaching 3 minor offenses.';
            $nextSteps[] = 'Review the escalated major case for OSA action / hearing.';
        } elseif ($violation->severity === 'Major') {
            $nextSteps[] = 'Document at least one OSA action before endorsing or closing without a hearing.';
            $nextSteps[] = 'Consider scheduling a hearing if required by severity.';
        } else {
            $nextSteps[] = $escalation['note'];
        }

        return [
            'student' => $student->full_name,
            'student_id' => $student->id,
            'violation_code' => $violation->code,
            'violation_title' => $violation->title,
            'severity' => $violation->severity,
            'offense_level' => $level,
            'recommended_sanction' => $sanction,
            'escalation' => $escalation,
            'next_steps' => $nextSteps,
            'instruction' => 'Quote recommended_sanction and next_steps exactly. Do not invent alternate penalties.',
        ];
    }

    /**
     * Advice for an existing case in the workflow.
     *
     * @return array<string, mixed>
     */
    public function adviseCase(StudentCase $case, ?Builder $caseQuery = null): array
    {
        $case->loadMissing(['student', 'violation', 'hearing']);
        $student = $case->student;
        $violation = $case->violation;

        $analysis = $student
            ? $this->analyzeStudent($student, $caseQuery)
            : null;

        $nextSteps = [];
        $status = $case->status;

        if ($status === 'Closed') {
            $nextSteps[] = 'Case is already closed. Review history if preparing reports or appeals.';
        } elseif ($status === 'Pending') {
            if ($case->isMajorOffense()) {
                if (! $case->canEndorseToGrievance()) {
                    $nextSteps[] = 'Record at least one OSA action (counseling, warning, parent conference, etc.).';
                }
                if ($case->canEndorse()) {
                    $nextSteps[] = 'Endorse to Grievance Committee when ready, or schedule a hearing.';
                } elseif ($reason = $case->endorseBlockReason()) {
                    $nextSteps[] = "Endorse blocked: {$reason}";
                }
                $nextSteps[] = 'Schedule a hearing for this major/critical case if policy requires it.';
            } else {
                $nextSteps[] = 'Apply/confirm sanction, notify student/guardian, and close when sanctions are resolved — or schedule a hearing if contested.';
            }
        } elseif ($status === 'Hearing Scheduled') {
            $nextSteps[] = 'Start the hearing on the scheduled date and record minutes.';
        } elseif ($status === 'Hearing') {
            $nextSteps[] = 'Complete the hearing with a final sanction decision to close the case.';
        }

        if ($reason = $case->closureBlockReason()) {
            $nextSteps[] = "Close blocked: {$reason}";
        } elseif ($status !== 'Closed') {
            $nextSteps[] = 'Case can be closed when disposition and sanctions are finalized.';
        }

        $catalogSanction = $violation
            ? $this->sanctionFor($violation, (int) ($case->offense_level ?: 1))
            : self::DEFAULT_SANCTION;

        return [
            'case_id' => $case->id,
            'status' => $status,
            'offense_level' => $case->offense_level,
            'current_sanction' => $case->sanction,
            'recommended_sanction' => $case->sanction ?: $catalogSanction,
            'catalog_sanction_for_level' => $catalogSanction,
            'endorsed_at' => $case->endorsed_at?->toDateTimeString(),
            'can_close' => $case->canClose(),
            'can_endorse' => $case->canEndorse(),
            'close_block_reason' => $case->closureBlockReason(),
            'endorse_block_reason' => $case->endorseBlockReason(),
            'has_hearing' => (bool) $case->hearing,
            'violation' => $violation ? [
                'code' => $violation->code,
                'title' => $violation->title,
                'severity' => $violation->severity,
            ] : null,
            'student_analysis' => $analysis,
            'next_steps' => array_values(array_unique($nextSteps)),
            'instruction' => 'Use recommended_sanction and next_steps exactly. Do not invent expulsion/suspension unless those words appear in the sanction text.',
        ];
    }

    /**
     * @param  Collection<int, StudentCase>  $cases
     * @param  array{triggers_escalation_now: bool, total_minors: int, minors_until_escalation: int, note: string}  $escalation
     * @return array{level: string, reasons: array<int, string>, recommendation: string, next_steps: array<int, string>}
     */
    public function riskFromCases(Collection $cases, array $escalation): array
    {
        $reasons = [];
        $nextSteps = [];
        $open = $cases->whereNotIn('status', ['Closed', 'Dismissed']);
        $openMajor = $open->filter(fn ($c) => ($c->violation?->severity ?? '') === 'Major');
        $thirdOrHigher = $cases->filter(fn ($c) => (int) $c->offense_level >= 3);

        if ($cases->isEmpty()) {
            return [
                'level' => 'LOW',
                'reasons' => ['Clean record — no cases found.'],
                'recommendation' => 'No disciplinary action needed. Continue routine monitoring.',
                'next_steps' => ['No active case work required.'],
            ];
        }

        $level = 'MODERATE';

        if ($openMajor->isNotEmpty()) {
            $level = 'HIGH';
            $reasons[] = 'Open Major case(s) need OSA action or hearing tracking.';
            $nextSteps[] = 'Review open major cases: record OSA actions, endorse or schedule hearing.';
        }

        if ($escalation['triggers_escalation_now'] ?? false) {
            $level = $this->maxRisk($level, 'HIGH');
            $reasons[] = $escalation['note'];
            $nextSteps[] = 'Treat SYS-001 Major escalation: review generated major case and continue major-offense workflow.';
        } elseif (($escalation['minors_until_escalation'] ?? 3) === 1) {
            $level = $this->maxRisk($level, 'MODERATE');
            $reasons[] = 'One more minor offense will trigger Major escalation.';
            $nextSteps[] = 'Counsel student and guardians before another minor is recorded.';
        }

        if ($thirdOrHigher->isNotEmpty()) {
            $level = $this->maxRisk($level, 'HIGH');
            $reasons[] = 'Student has reached 3rd (or higher) offense level on at least one violation.';
            $nextSteps[] = 'Apply catalog 3rd/recurring sanction and refer to Student Affairs if needed.';
        }

        if ($open->isEmpty() && $level === 'MODERATE') {
            $level = 'LOW';
            $reasons[] = 'All cases are closed; residual history only.';
            $nextSteps[] = 'Monitor only unless a new incident is reported.';
        }

        if (empty($reasons)) {
            $reasons[] = 'Active or historical cases present — routine review advised.';
        }

        if (empty($nextSteps)) {
            $nextSteps[] = 'Review latest case status and confirm sanctions are documented.';
        }

        $recommendation = match ($level) {
            'HIGH' => 'Elevated risk: follow major-offense / escalation procedures and keep dean informed.',
            'MODERATE' => 'Monitor and apply catalog sanctions for new or open minor cases.',
            default => 'Low risk: routine monitoring and handbook guidance.',
        };

        return [
            'level' => $level,
            'reasons' => $reasons,
            'recommendation' => $recommendation,
            'next_steps' => array_values(array_unique($nextSteps)),
        ];
    }

    public function formatAdviceMarkdown(array $advice, bool $tagalog = false): string
    {
        $lines = [];

        if (isset($advice['recommended_sanction'])) {
            $label = $tagalog ? 'Inirerekomendang sanction' : 'Recommended sanction';
            $lines[] = "**{$label}:** {$advice['recommended_sanction']}";
        }

        if (isset($advice['offense_level'])) {
            $label = $tagalog ? 'Antas ng offense' : 'Offense level';
            $lines[] = "**{$label}:** #{$advice['offense_level']}";
        }

        if (! empty($advice['risk_level'])) {
            $label = $tagalog ? 'Antas ng panganib' : 'Risk level';
            $lines[] = "**{$label}:** {$advice['risk_level']}";
        }

        if (! empty($advice['recommendation'])) {
            $label = $tagalog ? 'Rekomendasyon' : 'Recommendation';
            $lines[] = "**{$label}:** {$advice['recommendation']}";
        }

        if (! empty($advice['escalation']['note'])) {
            $lines[] = '**Escalation:** '.$advice['escalation']['note'];
        }

        $steps = $advice['next_steps'] ?? [];
        if ($steps !== []) {
            $heading = $tagalog ? 'Susunod na hakbang' : 'Next steps';
            $lines[] = '';
            $lines[] = "### {$heading}";
            foreach ($steps as $i => $step) {
                $lines[] = ($i + 1).". {$step}";
            }
        }

        return trim(implode("\n", $lines));
    }

    private function baseCaseQuery(?Builder $caseQuery): Builder
    {
        return $caseQuery ? (clone $caseQuery) : StudentCase::query();
    }

    private function maxRisk(string $current, string $candidate): string
    {
        $order = ['LOW' => 0, 'MODERATE' => 1, 'HIGH' => 2];

        return ($order[$candidate] ?? 0) > ($order[$current] ?? 0) ? $candidate : $current;
    }
}
