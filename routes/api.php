<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ContractTemplateController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Student registration (public)
Route::post('/students', [StudentController::class, 'store']);

// Contract signing (public - student signs their own contract)
Route::post('/contracts', [ContractController::class, 'store']);
Route::post('/contracts/preview', [ContractController::class, 'preview']);

// Public packages (for registration form)
Route::get('/packages', [PackageController::class, 'index']);
Route::get('/packages/slug/{slug}', [PackageController::class, 'showBySlug']);

// Public landing pages
Route::get('/landing-pages/slug/{slug}', [LandingPageController::class, 'showBySlug']);

// Protected routes (admin only)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Students
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/stats', [StudentController::class, 'stats']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::put('/students/{student}', [StudentController::class, 'update']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    // Contracts
    Route::get('/contracts', [ContractController::class, 'index']);
    Route::get('/contracts/{contract}', [ContractController::class, 'show']);
    Route::get('/contracts/{contract}/pdf', [ContractController::class, 'downloadPdf']);

    // Contract Templates
    Route::get('/contract-templates', [ContractTemplateController::class, 'index']);
    Route::get('/contract-templates/{contractTemplate}', [ContractTemplateController::class, 'show']);
    Route::put('/contract-templates/{contractTemplate}', [ContractTemplateController::class, 'update']);

    // Packages (admin management)
    Route::post('/packages', [PackageController::class, 'store']);
    Route::get('/packages/{package}', [PackageController::class, 'show']);
    Route::put('/packages/{package}', [PackageController::class, 'update']);
    Route::delete('/packages/{package}', [PackageController::class, 'destroy']);

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/payment-status', [InvoiceController::class, 'getStudentPaymentStatus']);
    Route::get('/invoices/students-unpaid', [InvoiceController::class, 'getStudentsWithUnpaidInstallments']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
    Route::post('/invoices/{invoice}/send-reminder', [SettingsController::class, 'sendPaymentReminder']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);

    // Landing Pages
    Route::get('/landing-pages', [LandingPageController::class, 'index']);
    Route::post('/landing-pages', [LandingPageController::class, 'store']);
    Route::get('/landing-pages/{landingPage}', [LandingPageController::class, 'show']);
    Route::put('/landing-pages/{landingPage}', [LandingPageController::class, 'update']);
    Route::delete('/landing-pages/{landingPage}', [LandingPageController::class, 'destroy']);
});
