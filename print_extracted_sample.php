<?php
$history = unserialize(file_get_contents('extracted_history.ser'));
$keys = array_keys($history);
foreach (array_slice($keys, 0, 5) as $key) {
    echo "FILE: $key\n";
    $versions = $history[$key];
    foreach ($versions as $idx => $v) {
        echo "  Version $idx from {$v['timestamp']} (Type: {$v['type']}):\n";
        echo "    Content prefix: " . substr($v['content'], 0, 200) . "...\n";
    }
}
