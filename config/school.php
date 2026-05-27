<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public registration
    |--------------------------------------------------------------------------
    |
    | Staff accounts must be created by a super admin. Keep this false in
    | production unless you explicitly need open sign-up.
    |
    */
    'registration_enabled' => (bool) env('REGISTRATION_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Temporary Student Password
    |--------------------------------------------------------------------------
    |
    | Students are stored in the `students` table. If you ever enable student
    | authentication, this value will be used as their initial password.
    |
    | For maximum safety, set STUDENT_DEFAULT_PASSWORD in your .env.
    | If not set, the app will generate a random password per student.
    |
    */
    'student_default_password' => env('STUDENT_DEFAULT_PASSWORD'),

    'name' => env('SCHOOL_NAME', 'I-Link CST'),

    /*
    |--------------------------------------------------------------------------
    | Departments (shortcut => full name)
    |--------------------------------------------------------------------------
    */
    'departments' => [
        'CCE' => 'Bachelor Of Science In Information System',
        'CCJE' => 'Bachelor Of Science In Criminology',
        'CTE' => 'Bachelor Of Technical Vocational Teachers Education',
        'CBAE' => 'College Of Business And Accounting Education',
    ],

    'department_aliases' => [
        'CEE' => 'CCE',
        'BSIT' => 'CCE',
    ],

];
