<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/customers/export-pdf', [CustomerController::class, 'exportPdf'])->name('customers.export-pdf');
    Route::resource('customers', CustomerController::class);

    Route::resource('loans', LoanController::class);
    Route::get('/loans/{loan}/export-pdf', [LoanController::class, 'exportPdf'])->name('loans.export-pdf');

    Route::resource('loan-products', LoanProductController::class)->except(['show', 'create', 'edit']);

    Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

    Route::get('/centro-reportes', [ReportController::class, 'center'])->name('reports.center');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    Route::get('/reports/overdue', [ReportController::class, 'overdue'])->name('reports.overdue');
    Route::get('/reports/upcoming', [ReportController::class, 'upcoming'])->name('reports.upcoming');
    Route::get('/reports/upcoming/export-pdf', [ReportController::class, 'exportUpcomingPdf'])->name('reports.upcoming.export-pdf');
});
