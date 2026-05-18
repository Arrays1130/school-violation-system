<?php
$logPath = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain\\611de44b-50bc-447f-aa75-5277619d93d8\\.system_generated\\logs\\overview.txt';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    for ($i = 405; $i <= 415; $i++) {
        if (isset($lines[$i])) {
            echo "Line $i: " . substr($lines[$i], 0, 300) . "...\n";
            file_put_contents("line_$i.txt", $lines[$i]);
            echo "  Saved to line_$i.txt\n";
        }
    }
} else {
    echo "Log file not found.\n";
}
