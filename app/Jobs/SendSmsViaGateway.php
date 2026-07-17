<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsViaGateway implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public array $backoff = [30, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(public string $phone, public string $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $smsUrl = config('services.sms_gateway.url');
        $smsUser = config('services.sms_gateway.username');
        $smsPass = config('services.sms_gateway.password');
        $requestId = (string) str()->uuid();

        if (! $smsUrl || ! $smsUser || ! $smsPass) {
            Log::warning('SMS gateway skipped: credentials not configured.', [
                'request_id' => $requestId,
                'phone' => $this->phone,
            ]);

            return;
        }

        try {
            $phone = $this->phone;
            if (str_starts_with($phone, '0')) {
                $phone = '+63' . substr($phone, 1);
            }

            $response = Http::timeout(5)->withBasicAuth($smsUser, $smsPass)
                ->post($smsUrl, [
                    'textMessage' => [
                        'text' => $this->message
                    ],
                    'phoneNumbers' => [$phone]
                ]);

            if (! $response->successful()) {
                Log::warning('SMS gateway returned non-success response.', [
                    'request_id' => $requestId,
                    'phone' => $phone,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                throw new \RuntimeException('SMS gateway returned status '.$response->status());
            }

            Log::info('SMS sent successfully via gateway.', [
                'request_id' => $requestId,
                'phone' => $phone,
                'status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send SMS via gateway.', [
                'request_id' => $requestId,
                'phone' => $this->phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::critical('SMS job permanently failed.', [
            'phone' => $this->phone,
            'error' => $exception->getMessage(),
        ]);
    }
}
