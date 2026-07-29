<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CaseActionController;
// Root redirect is handled below inside auth group

use App\Http\Controllers\CaseAttachmentController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeanDashboardController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\HearingController;
use App\Http\Controllers\MeetingMinuteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViolationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    $checks = [
        'database' => false,
        'cache' => false,
        'queue' => false,
    ];

    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['database'] = true;

        $key = 'health:'.str()->random(8);
        \Illuminate\Support\Facades\Cache::put($key, 'ok', 10);
        $checks['cache'] = \Illuminate\Support\Facades\Cache::get($key) === 'ok';
        \Illuminate\Support\Facades\Cache::forget($key);
        $checks['queue'] = config('queue.default') !== 'sync';
        // Only DB reachability is required for "health".
        // Cache/queue status is informational for readiness.
        $healthy = $checks['database'];

        return response()->json([
            'status' => $healthy ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'checks' => $checks,
            'time' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    } catch (\Throwable) {
        return response()->json(['status' => 'error', 'checks' => $checks], 503);
    }
})->name('health');

Route::get('/health/mail', function () {
    $url = trim((string) config('school.google_apps_script_url'));

    $result = [
        'google_apps_script_url_set' => $url !== '',
        'google_apps_script_url_suffix' => $url !== '' ? substr($url, -12) : null,
        'php_curl' => function_exists('curl_init'),
        'mail_default' => config('mail.default'),
        'uses_smtp' => \App\Support\SchoolMailer::usesSmtp(),
        'uses_apps_script' => \App\Support\SchoolMailer::usesGoogleAppsScript(),
        'get' => null,
        'post' => null,
    ];

    if ($url === '') {
        return response()->json(['status' => 'error', 'error' => 'GOOGLE_APPS_SCRIPT_URL missing', 'result' => $result], 503);
    }

    try {
        $get = \Illuminate\Support\Facades\Http::timeout(20)
            ->withHeaders(['User-Agent' => 'VioTrack-MailRelay/1.0'])
            ->get($url);
        $result['get'] = [
            'status' => $get->status(),
            'body' => mb_substr($get->body(), 0, 200),
        ];
    } catch (\Throwable $e) {
        $result['get'] = ['error' => $e->getMessage()];
    }

    try {
        $ok = \App\Support\GoogleAppsScriptMailer::send(
            'castillanesjaytzy@gmail.com',
            'VioTrack /health/mail test',
            '<p>Health mail probe from Render.</p>'
        );
        $result['post'] = ['ok' => $ok] + \App\Support\GoogleAppsScriptMailer::lastAttempt();
    } catch (\Throwable $e) {
        $result['post'] = ['error' => $e->getMessage()];
    }

    $healthy = ($result['get']['status'] ?? null) === 200 && ($result['post']['ok'] ?? false) === true;

    return response()->json([
        'status' => $healthy ? 'ok' : 'error',
        'result' => $result,
        'time' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
})->name('health.mail');

Route::get('/', function () {
    return redirect()->route('login');
});

// Public student self-registration (OTP via institutional email; disabled by default)
Route::prefix('student')->name('student.')->middleware(\App\Http\Middleware\EnsureStudentRegistrationEnabled::class)->group(function () {
    Route::get('/register', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'showRegistrationForm'])->name('register.form');
    Route::post('/register', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'sendOtp'])
        ->middleware('throttle:5,1')
        ->name('register.send');
    Route::get('/register/verify', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'showVerifyForm'])->name('register.verify_form');
    Route::post('/register/verify', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'verifyOtp'])
        ->middleware('throttle:10,1')
        ->name('register.verify');
    Route::post('/register/resend-otp', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'resendOtp'])
        ->middleware('throttle:3,1')
        ->name('register.resend');
    Route::get('/register/success', [\App\Http\Controllers\PublicStudentRegistrationController::class, 'showSuccess'])->name('register.success');
});

// Dean mobile web app (Flutter Web — iPhone PWA, same API as Android APK)
Route::get('/dean-app', function () {
    $index = public_path('dean-app/index.html');
    abort_unless(file_exists($index), 404, 'Dean app not built. Run: scripts/build-dean-web.ps1');

    return response()->file($index, ['Content-Type' => 'text/html; charset=UTF-8']);
})->name('dean-app');

Route::get('/dean-app/{path}', function (string $path) {
    $safePath = str_replace(['..', '\\'], '', $path);
    $file = public_path('dean-app/'.$safePath);

    if ($safePath !== '' && file_exists($file) && is_file($file)) {
        return response()->file($file);
    }

    $index = public_path('dean-app/index.html');
    abort_unless(file_exists($index), 404);

    return response()->file($index, ['Content-Type' => 'text/html; charset=UTF-8']);
})->where('path', '.*');


Route::middleware(['auth', 'recaptcha.verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dean/dashboard', [DeanDashboardController::class, 'index'])->name('dean.dashboard');
    Route::post('/notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/students/{student}/send-custom-message', [StudentController::class, 'sendCustomMessage'])->name('students.sendCustomMessage');
    Route::post('/students/{student}/generate-guardian-message', [StudentController::class, 'generateGuardianMessage'])
        ->middleware('throttle:20,1')
        ->name('students.generateGuardianMessage');
    Route::post('/students/promote', [StudentController::class, 'promoteStudents'])->name('students.promote');
    Route::post('/students/graduate-fourth-years', [StudentController::class, 'graduateFourthYears'])->name('students.graduate_fourth_years');
    Route::get('/students/trash', [StudentController::class, 'trash'])->name('students.trash');
    Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');
    Route::get('/students/import', [StudentController::class, 'importForm'])->name('students.import_form');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/{student}/print', [StudentController::class, 'printReport'])->name('students.print');
    Route::resource('students', StudentController::class);

    // Message Templates
    Route::resource('message-templates', \App\Http\Controllers\MessageTemplateController::class)->except(['create', 'edit', 'show']);

    // Cases (Violations Recordings)
    // Create route: supports both /cases/create?student_id=X and /students/{student}/cases/create
    Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
    Route::get('/students/{student}/cases/create', [CaseController::class, 'create'])->name('students.cases.create');
    Route::get('/cases/trash', [CaseController::class, 'trash'])->name('cases.trash');
    Route::get('/cases/by-violation/{violation}', [CaseController::class, 'byViolation'])->name('cases.by-violation');
    Route::post('/cases/{id}/restore', [CaseController::class, 'restore'])->name('cases.restore');
    Route::delete('/cases/{id}/force-delete', [CaseController::class, 'forceDelete'])->name('cases.force-delete');
    Route::get('/cases/{case}/print', [CaseController::class, 'print'])->name('cases.print');
    Route::resource('cases', CaseController::class)->except(['create']); // Create handled by explicit routes above

    // Violations (Reference)
    Route::resource('violations', ViolationController::class);
    Route::get('/violations-search', [ViolationController::class, 'search'])->name('violations.search');
    Route::get('/api/get-sanction-info', [ViolationController::class, 'getSanctionInfo'])->name('api.get-sanction-info');

    // Hearings
    Route::get('/cases/{case}/hearings/create', [HearingController::class, 'create'])->name('hearings.create');
    Route::get('/hearings/{hearing}/print-mom', [HearingController::class, 'printMom'])->name('hearings.print-mom');
    Route::get('/hearings/calendar', [HearingController::class, 'calendar'])->name('hearings.calendar');
    Route::resource('hearings', HearingController::class)->except(['create', 'index']);
    Route::post('/hearings/{hearing}/start', [HearingController::class, 'start'])->name('hearings.start');
    Route::post('/hearings/{hearing}/complete', [HearingController::class, 'markCompleted'])->name('hearings.complete');

    // Hand Book
    Route::resource('handbooks', \App\Http\Controllers\HandbookController::class);

    // Minutes of Meeting (Unified Document Repository)
    Route::get('/meeting-minutes', [CaseAttachmentController::class, 'index'])->name('meeting-minutes.index');
    Route::post('/meeting-minutes/upload', [CaseAttachmentController::class, 'store'])->name('meeting-minutes.upload');
    Route::get('/meeting-minutes/create', [MeetingMinuteController::class, 'create'])->name('meeting-minutes.create');
    Route::post('/meeting-minutes', [MeetingMinuteController::class, 'store'])->name('meeting-minutes.store');
    Route::get('/meeting-minutes/{meetingMinute}', [MeetingMinuteController::class, 'show'])->name('meeting-minutes.show');
    Route::get('/meeting-minutes/{meetingMinute}/edit', [MeetingMinuteController::class, 'edit'])->name('meeting-minutes.edit');
    Route::patch('/meeting-minutes/{meetingMinute}', [MeetingMinuteController::class, 'update'])->name('meeting-minutes.update');
    Route::delete('/meeting-minutes/{meetingMinute}', [MeetingMinuteController::class, 'destroy'])->name('meeting-minutes.destroy');

    // Attachments (Files)
    Route::post('/cases/{case}/attachments', [CaseAttachmentController::class, 'storeForCase'])->name('cases.attachments.store');
    Route::get('/attachments/{attachment}/view', [CaseAttachmentController::class, 'view'])->name('attachments.view');
    Route::get('/attachments/{attachment}/download', [CaseAttachmentController::class, 'download'])->name('attachments.download');
    Route::get('/attachments/{attachment}/signed-download', [CaseAttachmentController::class, 'signedDownload'])
        ->middleware('signed')
        ->name('attachments.signed-download');
    Route::delete('/attachments/{attachment}', [CaseAttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/ai-assistant', [App\Http\Controllers\AiAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/ai-assistant/chat', [App\Http\Controllers\AiAssistantController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('ai-assistant.chat');
    Route::post('/api/chat', [App\Http\Controllers\AiAssistantController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('api.chat');
    Route::post('/ai-assistant/stream', [App\Http\Controllers\AiAssistantController::class, 'stream'])
        ->middleware('throttle:30,1')
        ->name('ai-assistant.stream');
    Route::post('/ai-assistant/feedback', [App\Http\Controllers\AiAssistantController::class, 'feedback'])
        ->middleware('throttle:60,1')
        ->name('ai-assistant.feedback');
    Route::post('/ai-assistant/clear', [App\Http\Controllers\AiAssistantController::class, 'clearConversation'])
        ->name('ai-assistant.clear');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/system', [ReportController::class, 'system'])->name('reports.system');
    Route::get('/reports/system/export', [ReportController::class, 'systemExport'])->name('reports.system.export');
    Route::get('/reports/sanctions', [ReportController::class, 'sanctions'])->name('reports.sanctions');
    Route::get('/reports/retrieval', [ReportController::class, 'retrieval'])->name('reports.retrieval');
    Route::get('/reports/email-logs', [EmailLogController::class, 'index'])->name('reports.email-logs');
    Route::delete('/reports/email-logs/{emailLog}', [EmailLogController::class, 'destroy'])->name('reports.email-logs.destroy');
    Route::get('/reports/audit-logs', [AuditLogController::class, 'index'])->name('reports.audit-logs');
    Route::get('/reports/audit-logs/export', [AuditLogController::class, 'export'])->name('reports.audit-logs.export');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/csv', [ReportController::class, 'csv'])->name('reports.csv');

    // Case Actions (OSA interventions)
    Route::post('/cases/{case}/actions', [CaseActionController::class, 'store'])->name('cases.actions.store');
    Route::post('/cases/{case}/endorse', [CaseActionController::class, 'endorse'])->name('cases.endorse');

    // User Management
    Route::resource('users', UserController::class);

    // Case Status
    Route::post('/cases/{case}/close', [CaseController::class, 'close'])->name('cases.close');

    // Student Search API (popup with violation history)
    Route::get('/api/students/search', [StudentController::class, 'searchWithHistory'])->name('api.students.search');

    // Fetch graduated students by academic year
    Route::get('/api/graduated-students', [StudentController::class, 'getGraduatedStudents'])->name('api.graduated-students');
});

// Settings (Admin & Dean only handled in controller)
Route::middleware(['auth', 'recaptcha.verified'])->group(function () {
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/archive-cases', [App\Http\Controllers\SettingsController::class, 'archiveClosedCases'])->name('settings.archive-cases');
});

require __DIR__.'/auth.php';
