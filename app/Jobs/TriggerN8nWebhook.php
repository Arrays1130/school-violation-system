<?php

namespace App\Jobs;

use App\Services\N8nService;
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
    public function handle(N8nService $n8n): void
    {
        $n8n->triggerWebhook($this->eventName, $this->payload);
    }
}
