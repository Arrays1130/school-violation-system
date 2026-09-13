<?php

namespace App\Services;

use App\Models\CaseAction;
use App\Models\DtrEntry;
use App\Models\SanctionAssignment;
use App\Models\StudentCase;
use App\Models\User;
use App\Notifications\GsoSanctionCompletedNotification;
use App\Notifications\GsoSanctionAssignedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class SanctionAssignmentService
{
    public function assignHours(StudentCase $case, float $requiredHours, User $assignedBy, ?string $notes = null): SanctionAssignment
    {
        if ($requiredHours <= 0) {
            throw ValidationException::withMessages([
                'required_hours' => 'Required hours must be greater than zero.',
            ]);
        }

        if ($case->status === 'Closed') {
            throw ValidationException::withMessages([
                'case' => 'Closed cases cannot receive a GSO sanction assignment.',
            ]);
        }

        $existing = $case->activeSanctionAssignment();
        if ($existing) {
            throw ValidationException::withMessages([
                'required_hours' => 'This case already has an active GSO sanction assignment.',
            ]);
        }

        $assignment = SanctionAssignment::create([
            'case_id' => $case->id,
            'assigned_by' => $assignedBy->id,
            'required_hours' => round($requiredHours, 2),
            'status' => SanctionAssignment::STATUS_IN_PROGRESS,
            'notes' => $notes,
        ]);

        CaseAction::create([
            'case_id' => $case->id,
            'user_id' => $assignedBy->id,
            'action_type' => 'other',
            'description' => sprintf(
                'Assigned %.2f community service hour(s) for GSO monitoring.',
                $requiredHours
            ),
            'endorsed_to_grievance' => false,
        ]);

        $this->notifyGsoUsersAssigned($assignment);

        return $assignment->fresh(['dtrEntries', 'studentCase.student', 'studentCase.violation']);
    }

    public function timeIn(SanctionAssignment $assignment, User $gso, ?string $notes = null): DtrEntry
    {
        $this->assertActive($assignment);

        if ($assignment->openDtrEntry()) {
            throw ValidationException::withMessages([
                'dtr' => 'There is already an open time-in. Time out first.',
            ]);
        }

        return DtrEntry::create([
            'sanction_assignment_id' => $assignment->id,
            'logged_by' => $gso->id,
            'time_in' => now(),
            'notes' => $notes,
        ]);
    }

    public function timeOut(SanctionAssignment $assignment, User $gso, ?string $notes = null): DtrEntry
    {
        $this->assertActive($assignment);

        $open = $assignment->openDtrEntry();
        if (! $open) {
            throw ValidationException::withMessages([
                'dtr' => 'No open time-in found. Time in first.',
            ]);
        }

        $timeOut = now();
        if ($timeOut->lte($open->time_in)) {
            throw ValidationException::withMessages([
                'dtr' => 'Time out must be after time in.',
            ]);
        }

        $hours = round($open->time_in->diffInMinutes($timeOut) / 60, 2);

        $open->update([
            'time_out' => $timeOut,
            'hours_served' => max(0.01, $hours),
            'notes' => $notes ?: $open->notes,
            'logged_by' => $open->logged_by ?: $gso->id,
        ]);

        return $open->fresh('loggedBy');
    }

    public function complete(SanctionAssignment $assignment, User $gso, ?string $notes = null): SanctionAssignment
    {
        $this->assertActive($assignment);

        if ($assignment->openDtrEntry()) {
            throw ValidationException::withMessages([
                'dtr' => 'Close the open time-in before marking complete.',
            ]);
        }

        if (! $assignment->requirementsMet()) {
            throw ValidationException::withMessages([
                'hours' => sprintf(
                    'Required hours not met (%.2f / %.2f).',
                    $assignment->hoursServed(),
                    $assignment->required_hours
                ),
            ]);
        }

        return DB::transaction(function () use ($assignment, $gso, $notes) {
            $assignment->update([
                'status' => SanctionAssignment::STATUS_SUBMITTED_TO_OSA,
                'completed_at' => now(),
                'completed_by' => $gso->id,
                'submitted_to_osa_at' => now(),
                'notes' => $notes ?: $assignment->notes,
            ]);

            CaseAction::create([
                'case_id' => $assignment->case_id,
                'user_id' => $gso->id,
                'action_type' => 'gso_completed',
                'description' => sprintf(
                    'GSO marked community service complete (%.2f / %.2f hours). Forwarded to OSA.',
                    $assignment->hoursServed(),
                    $assignment->required_hours
                ),
                'endorsed_to_grievance' => false,
            ]);

            $assignment = $assignment->fresh(['studentCase.student', 'studentCase.violation', 'dtrEntries']);
            $this->notifyOsaCompleted($assignment);

            return $assignment;
        });
    }

    protected function assertActive(SanctionAssignment $assignment): void
    {
        if (! $assignment->isActive()) {
            throw ValidationException::withMessages([
                'assignment' => 'This sanction assignment is no longer in progress.',
            ]);
        }
    }

    protected function notifyGsoUsersAssigned(SanctionAssignment $assignment): void
    {
        $gsoUsers = User::query()->where('role', 'gso')->get();
        if ($gsoUsers->isEmpty()) {
            return;
        }

        Notification::send($gsoUsers, new GsoSanctionAssignedNotification($assignment));
    }

    protected function notifyOsaCompleted(SanctionAssignment $assignment): void
    {
        $osaUsers = User::query()->whereIn('role', ['admin', 'super_admin'])->get();
        if ($osaUsers->isEmpty()) {
            return;
        }

        Notification::send($osaUsers, new GsoSanctionCompletedNotification($assignment));
    }
}
