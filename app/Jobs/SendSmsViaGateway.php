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
        $smsUrl = env('SMS_GATEWAY_URL');
        $smsUser = env('SMS_GATEWAY_USERNAME');
        $smsPass = env('SMS_GATEWAY_PASSWORD');

        if (! $smsUrl || ! $smsUser || ! $smsPass) {
            Log::warning('SMS gateway skipped: credentials not configured.');

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

            if ($response->successful()) {
                Log::info('SMS sent successfully via gateway to ' . $phone);
            } else {
                Log::error('SMS gateway error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to send SMS via gateway: ' . $e->getMessage());
        }
    }
}
