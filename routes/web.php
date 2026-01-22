<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\ShiftController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/employees/{status}', [DashboardController::class, 'getEmployeesByStatus'])->name('dashboard.employees');

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::post('/punch-in', [AttendanceController::class, 'punchIn'])->name('punch-in');
        Route::post('/punch-out', [AttendanceController::class, 'punchOut'])->name('punch-out');
        Route::get('/history', [AttendanceController::class, 'history'])->name('history');
    });

    // Leave Management
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/apply', [LeaveController::class, 'create'])->name('apply');
        Route::post('/store', [LeaveController::class, 'store'])->name('store');
        Route::get('/history', [LeaveController::class, 'history'])->name('history');
        Route::delete('/{leave}/cancel', [LeaveController::class, 'cancel'])->name('cancel');

        // Manager/Admin routes
        Route::get('/approvals', [LeaveController::class, 'approvals'])->name('approvals');
        Route::post('/{leave}/approve', [LeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject', [LeaveController::class, 'reject'])->name('reject');
    });

    // Regularization
    Route::prefix('regularization')->name('regularization.')->group(function () {
        Route::post('/store', [\App\Http\Controllers\RegularizationController::class, 'store'])->name('store');
        Route::post('/{regularization}/approve', [\App\Http\Controllers\RegularizationController::class, 'approve'])->name('approve');
        Route::post('/{regularization}/reject', [\App\Http\Controllers\RegularizationController::class, 'reject'])->name('reject');
    });

    // Reports (Admin/Manager only)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/daily', [ReportController::class, 'daily'])->name('daily');
        Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
        Route::get('/leave', [ReportController::class, 'leave'])->name('leave');
        Route::get('/matrix', [ReportController::class, 'matrix'])->name('matrix');
        Route::get('/logs', [ReportController::class, 'logs'])->name('logs');
    });

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Employee Management
        Route::get('/employees/bulk', [EmployeeController::class, 'bulkIndex'])->name('employees.bulk');
        Route::post('/employees/bulk-update', [EmployeeController::class, 'bulkUpdate'])->name('employees.bulk-update');
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])
            ->name('employees.reset-password');

        // Department Management
        Route::resource('departments', DepartmentController::class)->except(['show']);

        // Location Management
        Route::resource('locations', LocationController::class)->except(['show']);

        // Shift Management
        Route::get('/shifts/bulk-assign', [ShiftController::class, 'bulkAssign'])->name('shifts.bulk-assign');
        Route::post('/shifts/bulk-update', [ShiftController::class, 'bulkUpdate'])->name('shifts.bulk-update');
        Route::resource('shifts', ShiftController::class)->except(['show']);
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
