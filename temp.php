<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
$sections = ['A', 'B', 'C'];

$count = 0;
foreach (\App\Models\Student::all() as $student) {
    $student->update([
        'year_level' => $years[array_rand($years)],
        'section' => $sections[array_rand($sections)]
    ]);
    $count++;
}
echo "Updated $count students.\n";
