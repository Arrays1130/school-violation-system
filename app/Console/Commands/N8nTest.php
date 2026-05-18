<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class N8nTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'n8n:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the N8n Webhook connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing N8n Webhook connection...');
        
        $n8n = new \App\Services\N8nService();
        $success = $n8n->triggerWebhook('test_connection', [
            'message' => 'This is a test event from the Laravel Backend.',
            'timestamp' => now()->toIso8601String(),
        ]);

        if ($success) {
            $this->info('✅ N8n Webhook triggered successfully!');
        } else {
            $this->error('❌ Failed to trigger N8n Webhook. Check the logs or .env configuration.');
        }
    }
}
