<?php
$logPath = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain\\611de44b-50bc-447f-aa75-5277619d93d8\\.system_generated\\logs\\overview.txt';
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    if (isset($lines[407])) {
        $data = json_decode($lines[407], true);
        echo "Line 407 keys:\n";
        print_r(array_keys($data));
        echo "\nFull JSON structure:\n";
        print_r($data);
    }
}
