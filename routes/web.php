<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\{
    HomeController,
    AttendanceController,
    UserController,
    EmployeeController,
    SectionController,
    BonusController,
    SalaryController,
    LeaveController,
    LeaveTypeController,
    DeductionController,
    ShiftController,
    CustomerController,
    ReportController
};


// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');


//  Authentication
// Route::post('register', [AuthController::class, 'register'])->name('register');
// Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {

    // Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('dashboard')->group(function () {

        // Users
        Route::resource('users', UserController::class)->except(['show']);

        // Sections
        Route::resource('sections', SectionController::class);

        // Employees
        Route::resource('employees', EmployeeController::class);

        // Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        });
        
        // API Routes for Attendance
        Route::prefix('api/attendance')->group(function () {
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        });

        // Bonuses
        Route::resource('bonuses', BonusController::class);

        //industries
        Route::resource('industry', \App\Http\Controllers\IndustryController::class)->except(['show']);

        // leads
        Route::resource('leads', \App\Http\Controllers\LeadController::class);

        // Salaries
        Route::resource('salaries', SalaryController::class);

        // Customers
        Route::resource('customers', CustomerController::class);

        //visits
        Route::resource('visits', \App\Http\Controllers\VisitController::class);

        // Leaves
        Route::resource('leaves', LeaveController::class);

        // Leave Types
        Route::resource('leave-types', LeaveTypeController::class);

        // Deductions
        Route::resource('deductions', DeductionController::class);

        // Shifts
        Route::resource('shifts', ShiftController::class);

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });
});

require __DIR__ . '/auth.php';
