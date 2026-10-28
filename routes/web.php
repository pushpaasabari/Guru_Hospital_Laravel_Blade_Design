<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentApprovalController;
use App\Http\Controllers\DocumentPackController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

    // Admissions & Clinical Entry
    Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/create', [AdmissionController::class, 'create'])->name('admissions.create');
    Route::post('/admissions', [AdmissionController::class, 'store'])->name('admissions.store');
    Route::get('/admissions/{admission}', [AdmissionController::class, 'show'])->name('admissions.show');
    Route::get('/admissions/{admission}/edit', [AdmissionController::class, 'edit'])->name('admissions.edit');
    Route::put('/admissions/{admission}', [AdmissionController::class, 'update'])->name('admissions.update');
    Route::post('/admissions/{admission}/revision', [AdmissionController::class, 'createRevision'])->name('admissions.revision');

    // Document Review, Approval & PDF Generation
    Route::get('/admissions/{admission}/documents/preview', [DocumentPackController::class, 'preview'])->name('documents.preview');
    Route::post('/admissions/{admission}/documents/submit-review', [DocumentApprovalController::class, 'submitForReview'])->name('documents.submit-review');
    Route::post('/admissions/{admission}/documents/approve', [DocumentApprovalController::class, 'approve'])->name('documents.approve');
    Route::post('/admissions/{admission}/documents/return', [DocumentApprovalController::class, 'returnForCorrection'])->name('documents.return');
    Route::match(['get', 'post'], '/admissions/{admission}/documents/pdf', [DocumentPackController::class, 'pdf'])->name('documents.pdf');
    Route::get('/admissions/{admission}/history', [DocumentPackController::class, 'history'])->name('documents.history');

    // Audit Logs (Admins & Auditors)
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit.index');

    // Templates Management (Admins)
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::put('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');

    // User Management (Admins)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
});

