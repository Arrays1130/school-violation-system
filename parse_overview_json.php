<?php
$brainDir = 'C:\\Users\\PERSONAL\\.gemini\\antigravity\\brain';

$logFile = $brainDir . '/611de44b-50bc-447f-aa75-5277619d93d8/.system_generated/logs/overview.txt';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $line = $lines[532];
    $data = json_decode($line, true);
    if ($data && isset($data['tool_calls'])) {
        foreach ($data['tool_calls'] as $tc) {
            echo "Tool: " . $tc['name'] . "\n";
            echo "Keys: " . implode(', ', array_keys($tc['args'])) . "\n";
            if (isset($tc['args']['ReplacementChunks'])) {
                var_dump($tc['args']['ReplacementChunks']);
            }
        }
    }
}
