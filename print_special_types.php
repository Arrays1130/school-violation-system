<?php
$logPath = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain\\611de44b-50bc-447f-aa75-5277619d93d8\\.system_generated\\logs\\overview.txt';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    foreach ($lines as $idx => $line) {
        $data = json_decode($line, true);
        if ($data && isset($data['type'])) {
            if ($data['type'] === 'VIEW_FILE' || $data['type'] === 'RUN_COMMAND') {
                echo "Line $idx: " . substr($line, 0, 500) . "\n";
            }
        }
    }
}
