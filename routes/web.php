<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Root redirect is handled below inside auth group

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeanDashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\HearingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CaseActionController;
use App\Http\Controllers\CaseAttachmentController;
use App\Http\Controllers\MeetingMinuteController;
use App\Http\Controllers\EmailLogController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dean/dashboard', [DeanDashboardController::class, 'index'])->name('dean.dashboard');
    Route::post('/notifications/{id}/mark-as-read', [DeanDashboardController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-as-read', [DeanDashboardController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // Profile
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Students
    Route::get('/students/trash', [StudentController::class, 'trash'])->name('students.trash');
    Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
    Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');
    Route::get('/students/import', [StudentController::class, 'importForm'])->name('students.import_form');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/{student}/print', [StudentController::class, 'printReport'])->name('students.print');
    Route::resource('students', StudentController::class);

    // Cases (Violations Recordings)
    // Create route: supports both /cases/create?student_id=X and /students/{student}/cases/create
    Route::get('/cases/create', [CaseController::class, 'create'])->name('cases.create');
    Route::get('/students/{student}/cases/create', [CaseController::class, 'create'])->name('students.cases.create');
    Route::get('/cases/trash', [CaseController::class, 'trash'])->name('cases.trash');
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
    Route::delete('/meeting-minutes/{meetingMinute}', [MeetingMinuteController::class, 'destroy'])->name('meeting-minutes.destroy');

    // Attachments (Files)
    Route::post('/cases/{case}/attachments', [CaseAttachmentController::class, 'store'])->name('cases.attachments.store');
    Route::get('/attachments/{attachment}/view', [CaseAttachmentController::class, 'view'])->name('attachments.view');
    Route::get('/attachments/{attachment}/download', [CaseAttachmentController::class, 'download'])->name('attachments.download');
    Route::delete('/attachments/{attachment}', [CaseAttachmentController::class, 'destroy'])->name('attachments.destroy');



    Route::get('/ai-assistant', [App\Http\Controllers\AiAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/ai-assistant/chat', [App\Http\Controllers\AiAssistantController::class, 'chat'])->name('ai-assistant.chat');
    Route::post('/api/chat', [App\Http\Controllers\AiAssistantController::class, 'chat'])->name('api.chat');
    Route::get('/ai-assistant/stream', [App\Http\Controllers\AiAssistantController::class, 'stream'])->name('ai-assistant.stream');


    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/retrieval', [ReportController::class, 'retrieval'])->name('reports.retrieval');
    Route::get('/reports/email-logs', [EmailLogController::class, 'index'])->name('reports.email-logs');
    Route::delete('/reports/email-logs/{emailLog}', [EmailLogController::class, 'destroy'])->name('reports.email-logs.destroy');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/reports/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/reports/csv', [ReportController::class, 'csv'])->name('reports.csv');

    // Case Actions (OSA interventions)
    Route::post('/cases/{case}/actions', [CaseActionController::class, 'store'])->name('cases.actions.store');
    Route::post('/cases/{case}/endorse', [CaseActionController::class, 'endorse'])->name('cases.endorse');


    // Case Status
    Route::post('/cases/{case}/close', [CaseController::class, 'close'])->name('cases.close');

    // Student Search API (popup with violation history)
    Route::get('/api/students/search', [StudentController::class, 'searchWithHistory'])->name('api.students.search');
});



require __DIR__.'/auth.php';

