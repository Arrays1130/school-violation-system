<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TriggerN8nWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $eventName;
    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(string $eventName, array $payload)
    {
        $this->eventName = $eventName;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
        public function handle(): void
    {
        try {
            // Native Laravel implementation replacing N8N
            $smsUrl = env('SMS_GATEWAY_URL');
            $smsUser = env('SMS_GATEWAY_USERNAME');
            $smsPass = env('SMS_GATEWAY_PASSWORD');

            // Construct message based on event
            $message = "Notification: Event '{$this->eventName}' triggered.";
            $phone = null;
            
            if (isset($this->payload['student_name'])) {
                 $message = "I-Link CST Notice: An event ({$this->eventName}) was recorded for {$this->payload['student_name']}.";
            }
            if (isset($this->payload['guardian_phone'])) {
                 $phone = $this->payload['guardian_phone'];
            }
            
            if ($smsUrl && $phone) {
                 \Illuminate\Support\Facades\Http::timeout(5)->post($smsUrl, [
                     'username' => $smsUser,
                     'password' => $smsPass,
                     'number'   => $phone,
                     'message'  => $message
                 ]);
            }
            
            \Illuminate\Support\Facades\Log::info("Native Notification handled for event {$this->eventName}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to handle native notification: " . $e->getMessage());
        }
    }
}

