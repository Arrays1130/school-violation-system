<?php

namespace App\Support;

use App\Models\Hearing;
use App\Models\NotificationDelivery;
use App\Models\StudentCase;
use App\Models\User;
use App\Notifications\CaseClosedNotification;
use App\Notifications\DeanCaseClosedNotification;
use App\Notifications\DeanHearingNotification;
use App\Notifications\DeanViolationNotification;
use App\Notifications\HearingScheduled;
use App\Notifications\ParentCaseClosedNotification;
use App\Notifications\ParentHearingNotification;
use App\Notifications\ParentViolationNotification;
use App\Notifications\ViolationRecorded;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class StakeholderNotifier
{
    public static function notifyViolationRecorded(StudentCase $case, bool $includeStudentSms = true): void
    {
        $case->loadMissing(['student', 'violation']);
        $student = $case->student;

        self::notifyStudentViolation($case, $includeStudentSms);
        self::notifyParentViolation($case);
        self::notifyDepartmentDeansOfViolation($case);
    }

    public static function notifyStudentViolation(StudentCase $case, bool $includeSms = true): void
    {
        $student = $case->student;

        try {
            if ($student->email) {
                $student->notify(new ViolationRecorded($case));
                self::markSent('mail', 'student_violation_recorded', $student->email, [
                    'case_id' => $case->id,
                    'student_id' => $student->id,
                ], 'student');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'student_violation_recorded', $student->email, $e->getMessage(), [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'student');
            Log::error('Failed to send student violation notification: '.$e->getMessage());
        }

        if ($includeSms && $student->phone) {
            $firstName = explode(' ', trim($student->full_name))[0];
            $date = $case->occurred_at->format('M j, Y');
            $sent = SmsGateway::dispatch(
                $student->phone,
                "I-Link CST Alert: Hi {$firstName}, a violation ({$case->violation->title}) was recorded on {$date}. Please check your student portal or contact the Discipline Office."
            );
            self::markStatusFromSmsDispatch('student_violation_recorded', $student->phone, $sent, [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'student');
        }
    }

    public static function notifyParentViolation(StudentCase $case): void
    {
        $student = $case->student;

        try {
            if ($student->guardian_email) {
                Notification::route('school_mail', $student->guardian_email)
                    ->notify(new ParentViolationNotification($case));
                self::markSent('mail', 'parent_violation_recorded', $student->guardian_email, [
                    'case_id' => $case->id,
                    'student_id' => $student->id,
                ], 'guardian');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'parent_violation_recorded', $student->guardian_email, $e->getMessage(), [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'guardian');
            Log::error('Failed to send parent violation email: '.$e->getMessage());
        }

        $sent = SmsGateway::dispatch($student->guardian_phone, ParentViolationNotification::smsText($case));
        self::markStatusFromSmsDispatch('parent_violation_recorded', $student->guardian_phone, $sent, [
            'case_id' => $case->id,
            'student_id' => $student->id,
        ], 'guardian');
    }

    public static function notifyDepartmentDeansOfViolation(StudentCase $case): void
    {
        try {
            $deans = User::where('role', 'dean')
                ->where('department', $case->student->department_shortcut)
                ->get();

            foreach ($deans as $dean) {
                $dean->notify(new DeanViolationNotification($case));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify deans about violation: '.$e->getMessage());
        }
    }

    public static function notifyHearingScheduled(Hearing $hearing, bool $isUpdate = false): void
    {
        $hearing->loadMissing(['case.student', 'case.violation']);
        $student = $hearing->case->student;

        try {
            if ($student->email) {
                $student->notify(new HearingScheduled($hearing));
                self::markSent('mail', 'student_hearing_scheduled', $student->email, [
                    'hearing_id' => $hearing->id,
                    'case_id' => $hearing->case_id,
                ], 'student');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'student_hearing_scheduled', $student->email, $e->getMessage(), [
                'hearing_id' => $hearing->id,
                'case_id' => $hearing->case_id,
            ], 'student');
            Log::error('Failed to notify student about hearing: '.$e->getMessage());
        }

        if ($student->phone) {
            $formattedDate = $hearing->scheduled_at->format('F j, Y g:i A');
            $prefix = $isUpdate ? 'rescheduled' : 'scheduled';
            $sent = SmsGateway::dispatch(
                $student->phone,
                "I-Link CST: A hearing is {$prefix} for you on {$formattedDate} at {$hearing->venue}. Please check your portal."
            );
            self::markStatusFromSmsDispatch('student_hearing_scheduled', $student->phone, $sent, [
                'hearing_id' => $hearing->id,
                'case_id' => $hearing->case_id,
                'is_update' => $isUpdate,
            ], 'student');
        }

        self::notifyParentHearing($hearing, $isUpdate);
        self::notifyAllDeansOfHearing($hearing);
    }

    public static function notifyParentHearing(Hearing $hearing, bool $isUpdate = false): void
    {
        $student = $hearing->case->student;

        try {
            if ($student->guardian_email) {
                Notification::route('school_mail', $student->guardian_email)
                    ->notify(new ParentHearingNotification($hearing, $isUpdate));
                self::markSent('mail', 'parent_hearing_scheduled', $student->guardian_email, [
                    'hearing_id' => $hearing->id,
                    'case_id' => $hearing->case_id,
                    'is_update' => $isUpdate,
                ], 'guardian');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'parent_hearing_scheduled', $student->guardian_email, $e->getMessage(), [
                'hearing_id' => $hearing->id,
                'case_id' => $hearing->case_id,
                'is_update' => $isUpdate,
            ], 'guardian');
            Log::error('Failed to send parent hearing email: '.$e->getMessage());
        }

        $sent = SmsGateway::dispatch(
            $student->guardian_phone,
            ParentHearingNotification::smsText($hearing, $isUpdate)
        );
        self::markStatusFromSmsDispatch('parent_hearing_scheduled', $student->guardian_phone, $sent, [
            'hearing_id' => $hearing->id,
            'case_id' => $hearing->case_id,
            'is_update' => $isUpdate,
        ], 'guardian');
    }

    public static function notifyAllDeansOfHearing(Hearing $hearing): void
    {
        try {
            $allDeans = User::where('role', 'dean')->get();
            foreach ($allDeans as $dean) {
                $dean->notify(new DeanHearingNotification($hearing));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify deans about hearing: '.$e->getMessage());
        }
    }

    public static function notifyCaseClosed(StudentCase $case): void
    {
        $case->loadMissing(['student', 'violation']);
        $student = $case->student;

        try {
            if ($student->email) {
                $student->notify(new CaseClosedNotification($case));
                self::markSent('mail', 'student_case_closed', $student->email, [
                    'case_id' => $case->id,
                    'student_id' => $student->id,
                ], 'student');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'student_case_closed', $student->email, $e->getMessage(), [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'student');
            Log::error('Failed to notify student about case closure: '.$e->getMessage());
        }

        if ($student->phone) {
            $sent = SmsGateway::dispatch($student->phone, CaseClosedNotification::smsText($case));
            self::markStatusFromSmsDispatch('student_case_closed', $student->phone, $sent, [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'student');
        }

        try {
            if ($student->guardian_email) {
                Notification::route('school_mail', $student->guardian_email)
                    ->notify(new ParentCaseClosedNotification($case));
                self::markSent('mail', 'parent_case_closed', $student->guardian_email, [
                    'case_id' => $case->id,
                    'student_id' => $student->id,
                ], 'guardian');
            }
        } catch (\Exception $e) {
            self::markFailed('mail', 'parent_case_closed', $student->guardian_email, $e->getMessage(), [
                'case_id' => $case->id,
                'student_id' => $student->id,
            ], 'guardian');
            Log::error('Failed to send parent case-closed email: '.$e->getMessage());
        }

        $sent = SmsGateway::dispatch($student->guardian_phone, ParentCaseClosedNotification::smsText($case));
        self::markStatusFromSmsDispatch('parent_case_closed', $student->guardian_phone, $sent, [
            'case_id' => $case->id,
            'student_id' => $student->id,
        ], 'guardian');

        try {
            $deans = User::where('role', 'dean')
                ->where('department', $student->department_shortcut)
                ->get();

            foreach ($deans as $dean) {
                $dean->notify(new DeanCaseClosedNotification($case));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify deans about case closure: '.$e->getMessage());
        }
    }

    protected static function markStatusFromSmsDispatch(string $event, ?string $phone, bool $sent, array $context, string $recipientType): void
    {
        if (! $phone) {
            return;
        }

        if ($sent) {
            self::markQueued('sms', $event, $phone, $context, $recipientType);
            return;
        }

        self::markFailed('sms', $event, $phone, 'SMS gateway disabled or recipient is empty.', $context, $recipientType);
    }

    protected static function markQueued(string $channel, string $event, ?string $recipient, array $context, string $recipientType): void
    {
        NotificationDelivery::create([
            'channel' => $channel,
            'event' => $event,
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'status' => 'queued',
            'context' => $context,
        ]);
    }

    protected static function markSent(string $channel, string $event, ?string $recipient, array $context, string $recipientType): void
    {
        NotificationDelivery::create([
            'channel' => $channel,
            'event' => $event,
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'status' => 'sent',
            'context' => $context,
            'sent_at' => now(),
        ]);
    }

    protected static function markFailed(string $channel, string $event, ?string $recipient, string $error, array $context, string $recipientType): void
    {
        NotificationDelivery::create([
            'channel' => $channel,
            'event' => $event,
            'recipient' => $recipient,
            'recipient_type' => $recipientType,
            'status' => 'failed',
            'context' => $context,
            'last_error' => $error,
            'failed_at' => now(),
        ]);
    }
}
