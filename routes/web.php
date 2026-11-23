<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AnesthesiaTypeController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClinicalStaffController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentApprovalController;
use App\Http\Controllers\DocumentPackController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Granular UI Click & Interaction Telemetry (Guest + Authenticated)
Route::post('/audit-logs/events', [AuditLogController::class, 'storeEvents'])
    ->middleware('throttle:180,1')
    ->name('audit.events.store');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard & Search
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admissions & Clinical Entry (5-Stage Workflow)
    Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::get('/admissions/{admission}/edit', [AdmissionController::class, 'edit'])->name('admissions.edit');
    Route::get('/admissions/{admission}/admission', [AdmissionController::class, 'edit'])->name('admissions.admission');
    Route::put('/admissions/{admission}', [AdmissionController::class, 'update'])->name('admissions.update');
    Route::post('/admissions/{admission}/revision', [AdmissionController::class, 'createRevision'])->name('admissions.revision');

    // Stage 2: Clinical Assessment (Clinical History & Examination)
    Route::get('/admissions/{admission}/clinical', [AdmissionController::class, 'editClinical'])->name('admissions.clinical');
    Route::put('/admissions/{admission}/clinical', [AdmissionController::class, 'updateClinical'])->name('admissions.clinical.update');

    // Stage 3: Test Reports
    Route::get('/admissions/{admission}/test-reports', [AdmissionController::class, 'editTestReports'])->name('admissions.test-reports');
    Route::put('/admissions/{admission}/test-reports', [AdmissionController::class, 'updateTestReports'])->name('admissions.test-reports.update');

    // Stage 4: Labour / LSCS / Surgery
    Route::get('/admissions/{admission}/surgery', [AdmissionController::class, 'editSurgery'])->name('admissions.surgery');
    Route::put('/admissions/{admission}/surgery', [AdmissionController::class, 'updateSurgery'])->name('admissions.surgery.update');

    // Stage 5: Discharge
    Route::get('/admissions/{admission}/discharge', [AdmissionController::class, 'editDischarge'])->name('admissions.discharge');
    Route::put('/admissions/{admission}/discharge', [AdmissionController::class, 'updateDischarge'])->name('admissions.discharge.update');

    // Document Review, Approval & PDF Generation
    Route::get('/admissions/{admission}/documents/preview', [DocumentPackController::class, 'preview'])->name('documents.preview');
    Route::get('/documents/{admission}/preview', [DocumentPackController::class, 'preview']);
    Route::post('/admissions/{admission}/documents/submit-review', [DocumentApprovalController::class, 'submitForReview'])->name('documents.submit-review');
    Route::post('/admissions/{admission}/documents/approve', [DocumentApprovalController::class, 'approve'])->name('documents.approve');
    Route::post('/admissions/{admission}/documents/return', [DocumentApprovalController::class, 'returnForCorrection'])->name('documents.return');
    Route::match(['get', 'post'], '/admissions/{admission}/documents/pdf/{type?}', [DocumentPackController::class, 'pdf'])->name('documents.pdf');
    Route::match(['get', 'post'], '/documents/{admission}/pdf/{type?}', [DocumentPackController::class, 'pdf']);
    Route::get('/admissions/{admission}/history', [DocumentPackController::class, 'history'])->name('documents.history');
    Route::get('/documents/{admission}/history', [DocumentPackController::class, 'history']);

    // Audit Logs (Admins & Auditors)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Clinical Staff & Doctors Management (Admins)
    Route::get('/clinical-staff', [ClinicalStaffController::class, 'index'])->name('clinical-staff.index');
    Route::post('/clinical-staff', [ClinicalStaffController::class, 'store'])->name('clinical-staff.store');
    Route::put('/clinical-staff/{clinicalStaff}', [ClinicalStaffController::class, 'update'])->name('clinical-staff.update');
    Route::delete('/clinical-staff/{clinicalStaff}', [ClinicalStaffController::class, 'destroy'])->name('clinical-staff.destroy');
    Route::patch('/clinical-staff/{clinicalStaff}/toggle', [ClinicalStaffController::class, 'toggleActive'])->name('clinical-staff.toggle');

    // Anesthesia Types Management (Admins)
    Route::get('/anesthesia-types', [AnesthesiaTypeController::class, 'index'])->name('anesthesia-types.index');
    Route::post('/anesthesia-types', [AnesthesiaTypeController::class, 'store'])->name('anesthesia-types.store');
    Route::put('/anesthesia-types/{anesthesiaType}', [AnesthesiaTypeController::class, 'update'])->name('anesthesia-types.update');
    Route::delete('/anesthesia-types/{anesthesiaType}', [AnesthesiaTypeController::class, 'destroy'])->name('anesthesia-types.destroy');
    Route::patch('/anesthesia-types/{anesthesiaType}/toggle', [AnesthesiaTypeController::class, 'toggleActive'])->name('anesthesia-types.toggle');

    // Templates Management (Admins)
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');

    // User Management (Admins)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
});

