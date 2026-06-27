<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\StudentCase;

use Illuminate\Contracts\Queue\ShouldQueue;

class DeanViolationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public StudentCase $case)
    {
        //
    }

    public function via($notifiable)
    {
        $channels = ['database'];
        if (env('ENABLE_EMAILS', false)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->case->student?->full_name ?? 'Unknown Student';
        $violationTitle = $this->case->violation?->title ?? 'Unknown Violation';
        $department = $this->case->student?->department ?? 'N/A';
        $yearLevel = $this->case->student?->year_level ?? 'N/A';
        $section = $this->case->student?->section ?? 'N/A';
        $severity = $this->case->violation?->severity ?? 'N/A';
        $date = $this->case->occurred_at?->format('F j, Y g:i A') ?? 'N/A';
        $status = $this->case->status ?? 'Pending';

        return (new MailMessage)
            ->subject("[DEAN ALERT] New Violation: $studentName ($department)")
            ->greeting("Dear Dean,")
            ->line("A new violation has been recorded for a student in your department.")
            ->line("**Student Information:**")
            ->line("- **Name:** $studentName")
            ->line("- **Department:** $department")
            ->line("- **Year/Section:** $yearLevel - $section")
            ->line("**Violation Details:**")
            ->line("- **Offense:** $violationTitle")
            ->line("- **Severity:** $severity")
            ->line("- **Date/Time:** $date")
            ->line("- **Status:** $status")
            ->action('View Case Details', route('cases.show', $this->case->id))
            ->line('Please review the case files for further departmental action if necessary.');
    }

    public function toArray(object $notifiable): array
    {
        $studentName = $this->case->student?->full_name ?? 'Unknown Student';
        $violationTitle = $this->case->violation?->title ?? 'Unknown Violation';

        return [
            'title' => 'New Department Violation',
            'message' => "$studentName from your department has a new violation recorded.",
            'case_id' => $this->case->id,
            'student_name' => $studentName,
            'violation_title' => $violationTitle,
        ];
    }
}
