<?php
$history = unserialize(file_get_contents('extracted_history.ser'));

echo "=== REPORTS INDEX HISTORICAL KEYS ===\n";
foreach (array_keys($history) as $k) {
    if (stripos($k, 'reports') !== false && stripos($k, 'index') !== false) {
        echo "Found key: $k\n";
        foreach ($history[$k] as $idx => $v) {
            echo "  Version [$idx] from Conversation {$v['conv']} ({$v['timestamp']}) - Length: " . strlen($v['content']) . " bytes\n";
            file_put_contents("reports_index_v{$idx}.blade.php", $v['content']);
            echo "    -> Saved to reports_index_v{$idx}.blade.php\n";
        }
    }
    if (stripos($k, 'layouts') !== false && stripos($k, 'app') !== false) {
        echo "Found key: $k\n";
        foreach ($history[$k] as $idx => $v) {
            echo "  Version [$idx] from Conversation {$v['conv']} ({$v['timestamp']}) - Length: " . strlen($v['content']) . " bytes\n";
            file_put_contents("layouts_app_v{$idx}.blade.php", $v['content']);
            echo "    -> Saved to layouts_app_v{$idx}.blade.php\n";
        }
    }
}
