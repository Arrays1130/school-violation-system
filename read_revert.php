<?php
$j = json_decode(file_get_contents('revert_plan.json'), true);
foreach($j as $file => $changes) {
    if (strpos($file, 'email-logs.blade.php') !== false) {
        echo substr($changes[0]['target'], 0, 1000);
    }
}
