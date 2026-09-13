<?php

namespace App\Jobs;

use App\Services\N8nService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TriggerN8nWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $eventName,
        protected array $payload
    ) {
    }

    public function handle(N8nService $n8n): void
    {
        try {
            // Primary path: forward the lifecycle event to n8n for automation demo / ops.
            $n8n->triggerWebhook($this->eventName, $this->payload);

            // Optional native SMS fallback (same gateway used elsewhere), if configured.
            $smsUrl = env('SMS_GATEWAY_URL');
            $smsUser = env('SMS_GATEWAY_USERNAME');
            $smsPass = env('SMS_GATEWAY_PASSWORD');
            $phone = $this->payload['guardian_contact']
                ?? $this->payload['guardian_phone']
                ?? null;

            if ($smsUrl && $phone) {
                $message = $this->buildSmsMessage();
                if (str_starts_with((string) $phone, '0')) {
                    $phone = '+63'.substr((string) $phone, 1);
                }

                Http::timeout(5)->withBasicAuth((string) $smsUser, (string) $smsPass)->post($smsUrl, [
                    'textMessage' => ['text' => $message],
                    'phoneNumbers' => [$phone],
                ]);
            }

            Log::info("Lifecycle notification handled for event {$this->eventName}");
        } catch (\Throwable $e) {
            Log::error("Failed to handle lifecycle notification ({$this->eventName}): ".$e->getMessage());
        }
    }

    protected function buildSmsMessage(): string
    {
        $student = $this->payload['student_name'] ?? 'a student';
        $case = $this->payload['case_code'] ?? (isset($this->payload['case_id']) ? '#'.$this->payload['case_id'] : '');

        return match ($this->eventName) {
            'violation_recorded' => "I-Link CST: Violation recorded for {$student}. Case {$case}.",
            'hearing_scheduled' => "I-Link CST: Hearing scheduled for {$student}. Case {$case}.",
            'gso_sanction_assigned' => "I-Link CST: Community service assigned for {$student}. Case {$case}.",
            'gso_sanction_completed' => "I-Link CST: GSO completed hours for {$student}. Case {$case}. Ready for OSA.",
            'case_closed' => "I-Link CST: Case {$case} for {$student} is closed.",
            default => "I-Link CST: Update ({$this->eventName}) for {$student}.",
        };
    }
}
