<?php
$historyDir = 'C:\\Users\\PERSONAL\\AppData\\Roaming\\Code\\User\\History';
$dirs = glob($historyDir . '/*', GLOB_ONLYDIR);
$files = [];

foreach ($dirs as $dir) {
    if (file_exists($dir . '/entries.json')) {
        $content = file_get_contents($dir . '/entries.json');
        $data = json_decode($content, true);
        if ($data && isset($data['resource'])) {
            $resource = urldecode($data['resource']);
            $files[] = [
                'resource' => $resource,
                'latest' => date('Y-m-d H:i:s', max(array_column($data['entries'] ?? [['timestamp' => 0]], 'timestamp'))/1000)
            ];
        }
    }
}

foreach ($files as $f) {
    echo "{$f['latest']} | {$f['resource']}\n";
}
