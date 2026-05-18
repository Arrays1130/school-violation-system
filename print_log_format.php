<?php
$logPath = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain\\f4b343f1-2723-412e-8e04-4fe056c86123\\.system_generated\\logs\\overview.txt';
if (file_exists($logPath)) {
    $handle = fopen($logPath, 'r');
    for ($i = 0; $i < 5; $i++) {
        $line = fgets($handle);
        if ($line) {
            echo "Line $i: " . substr($line, 0, 300) . "...\n";
        }
    }
    fclose($handle);
} else {
    echo "Log file not found: $logPath\n";
}
