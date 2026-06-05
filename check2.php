<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$students = \App\Models\Student::count();
$violations = \App\Models\Violation::count();
$cases = \App\Models\StudentCase::count();
$hearings = \App\Models\Hearing::count();

echo "Students: $students, Violations: $violations, Cases: $cases, Hearings: $hearings\n";
