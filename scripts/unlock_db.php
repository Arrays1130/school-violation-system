<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- CHECKING InnoDB TRANSACTIONS ---\n";

try {
    $transactions = DB::select("SELECT trx_id, trx_mysql_thread_id, trx_query, trx_state, trx_started FROM information_schema.innodb_trx");
    if (empty($transactions)) {
        echo "No active InnoDB transactions found.\n";
    } else {
        foreach ($transactions as $trx) {
            echo "Trx ID: {$trx->trx_id} | Thread ID: {$trx->trx_mysql_thread_id} | State: {$trx->trx_state} | Started: {$trx->trx_started} | Query: {$trx->trx_query}\n";
            echo "--> Killing Thread ID: {$trx->trx_mysql_thread_id}\n";
            try {
                DB::statement("KILL {$trx->trx_mysql_thread_id}");
                echo "Successfully killed thread {$trx->trx_mysql_thread_id}\n";
            } catch (\Exception $ex) {
                echo "Failed to kill thread: " . $ex->getMessage() . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n--- CHECK COMPLETE ---\n";
