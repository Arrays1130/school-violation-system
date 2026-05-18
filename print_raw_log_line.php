<?php
$logPath = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain\\611de44b-50bc-447f-aa75-5277619d93d8\\.system_generated\\logs\\overview.txt';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    foreach ($lines as $idx => $line) {
        if (stripos($line, 'reports/index.blade.php') !== false) {
            echo "Line $idx: " . substr($line, 0, 500) . "...\n";
            // Write full line to a file so we can view it
            file_put_contents("raw_log_line_$idx.txt", $line);
            echo "  Saved full line to raw_log_line_$idx.txt\n";
        }
    }
} else {
    echo "Log file not found.\n";
}
