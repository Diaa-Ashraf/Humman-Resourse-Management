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
    ReportController,
    RecruitmentController,
    PerformanceReviewController,
    TrainingController,
    EmployeeDocumentController,
    PayrollController,
    EmployeeAdvanceController,
    PayrollComponentController,
    ShiftAssignmentController,
    LeaveBalanceController,
    CompanyHolidayController,
    EmployeePortalController,
    EmployeeRequestController,
    InternalCommunicationController,
    SecurityController,
    AuditLogController,
    RetentionPolicyController,
    NotificationController
};
use App\Http\Middleware\TrackUserSession;


Route::get('/', fn() => redirect()->route('login'));

Route::get('/dashboard', [HomeController::class, 'index'])->middleware(['auth', TrackUserSession::class, 'verified'])->name('dashboard');


//  Authentication
// Route::post('register', [AuthController::class, 'register'])->name('register');
// Route::post('login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth', TrackUserSession::class])->group(function () {

    // Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('dashboard')->group(function () {

        // Users
        Route::resource('users', UserController::class)->except(['show']);

        // Sections
        Route::resource('sections', SectionController::class);

        // Branches
        Route::resource('branches', \App\Http\Controllers\BranchController::class)->except(['show']);

        // Employee records and lifecycle
        Route::get('employees-export', [EmployeeController::class, 'exportCsv'])->name('employees.export');
        Route::post('employees-import', [EmployeeController::class, 'importCsv'])->name('employees.import');
        Route::get('employees-import-template', [EmployeeController::class, 'importTemplate'])->name('employees.import-template');
        Route::post('employees/{employee}/terminate', [EmployeeController::class, 'terminate'])->name('employees.terminate');
        Route::patch('employee-lifecycle-tasks/{task}', [EmployeeController::class, 'completeLifecycleTask'])->name('employee-lifecycle-tasks.update');
        // Employees
        Route::resource('employees', EmployeeController::class);

        // Attendance
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('attendances.index');
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
            Route::post('/import', [AttendanceController::class, 'importCsv'])->name('attendances.import');
            Route::patch('/{attendance}/review', [AttendanceController::class, 'reviewImport'])->name('attendances.review');
        });
        
        // API Routes for Attendance
        Route::prefix('api/attendance')->group(function () {
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        });

        // Bonuses
        Route::resource('bonuses', BonusController::class);
        Route::patch('bonuses/{bonus}/review', [BonusController::class, 'review'])->name('bonuses.review');

        // Salaries
        Route::get('/payroll', [PayrollController::class,'index'])->name('payroll.index');
        Route::post('/payroll', [PayrollController::class,'generate'])->name('payroll.generate');
        Route::post('/payroll/{run}/recalculate', [PayrollController::class,'recalculate'])->name('payroll.recalculate');
        Route::get('/payroll/{run}', [PayrollController::class,'show'])->name('payroll.show');
        Route::patch('/payroll/{run}/submit', [PayrollController::class,'submit'])->name('payroll.submit');
        Route::patch('/payroll/{run}/review', [PayrollController::class,'review'])->name('payroll.review');
        Route::patch('/payroll/{run}/approve', [PayrollController::class,'approve'])->name('payroll.approve');
        Route::patch('/payroll/{run}/close', [PayrollController::class,'close'])->name('payroll.close');
        Route::put('/payroll-policies', [PayrollController::class,'updatePolicies'])->name('payroll.policies.update');
        Route::get('/salary-components', [PayrollComponentController::class,'index'])->name('salary-components.index');
        Route::post('/salary-components', [PayrollComponentController::class,'store'])->name('salary-components.store');
        Route::post('/salary-components/assign', [PayrollComponentController::class,'assign'])->name('salary-components.assign');
        Route::delete('/salary-components/assignments/{assignment}', [PayrollComponentController::class,'remove'])->name('salary-components.remove');
        Route::get('/advances', [EmployeeAdvanceController::class,'index'])->name('advances.index');
        Route::post('/advances', [EmployeeAdvanceController::class,'store'])->name('advances.store');
        Route::patch('/advances/{advance}/review', [EmployeeAdvanceController::class,'review'])->name('advances.review');
        Route::get('/shift-assignments', [ShiftAssignmentController::class,'index'])->name('shift-assignments.index');
        Route::post('/shift-assignments', [ShiftAssignmentController::class,'store'])->name('shift-assignments.store');
        Route::delete('/shift-assignments/{assignment}', [ShiftAssignmentController::class,'destroy'])->name('shift-assignments.destroy');
        Route::get('/leave-balances', [LeaveBalanceController::class,'index'])->name('leave-balances.index');
        Route::post('/leave-balances/accrue', [LeaveBalanceController::class,'accrue'])->name('leave-balances.accrue');
        Route::post('/leave-balances/adjust', [LeaveBalanceController::class,'adjust'])->name('leave-balances.adjust');
        Route::get('/holiday-calendar', [CompanyHolidayController::class,'index'])->name('holiday-calendar.index');
        Route::post('/holiday-calendar', [CompanyHolidayController::class,'store'])->name('holiday-calendar.store');
        Route::delete('/holiday-calendar/{holiday}', [CompanyHolidayController::class,'destroy'])->name('holiday-calendar.destroy');
        Route::resource('salaries', SalaryController::class);
        Route::resource('salaries', SalaryController::class);
        Route::patch('salaries/{salary}/pay', [SalaryController::class, 'markPaid'])->name('salaries.pay');

        // Leaves
        Route::resource('leaves', LeaveController::class);
        Route::patch('leaves/{leave}/review', [LeaveController::class, 'review'])->name('leaves.review');

        // Leave Types
        Route::resource('leave-types', LeaveTypeController::class);

        // Deductions
        Route::resource('deductions', DeductionController::class);
        Route::patch('deductions/{deduction}/review', [DeductionController::class, 'review'])->name('deductions.review');

        // Shifts
        Route::resource('shifts', ShiftController::class);

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Extended HR modules
        Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.index');
        Route::post('/recruitment/openings', [RecruitmentController::class, 'storeOpening'])->name('recruitment.openings.store');
        Route::patch('/recruitment/openings/{opening}/decision', [RecruitmentController::class, 'decideOpening'])->name('recruitment.openings.decision');
        Route::put('/recruitment/openings/{opening}', [RecruitmentController::class, 'updateOpening'])->name('recruitment.openings.update');
        Route::delete('/recruitment/openings/{opening}', [RecruitmentController::class, 'destroyOpening'])->name('recruitment.openings.destroy');
        Route::post('/recruitment/candidates', [RecruitmentController::class, 'storeCandidate'])->name('recruitment.candidates.store');
        Route::patch('/recruitment/candidates/{candidate}', [RecruitmentController::class, 'updateCandidate'])->name('recruitment.candidates.update');
        Route::post('/recruitment/candidates/{candidate}/interviews', [RecruitmentController::class, 'scheduleInterview'])->name('recruitment.interviews.store');
        Route::patch('/recruitment/interviews/{interview}', [RecruitmentController::class, 'evaluateInterview'])->name('recruitment.interviews.update');
        Route::post('/recruitment/candidates/{candidate}/offer', [RecruitmentController::class, 'sendOffer'])->name('recruitment.offer.send');
        Route::patch('/recruitment/candidates/{candidate}/offer', [RecruitmentController::class, 'decideOffer'])->name('recruitment.offer.decision');
        Route::post('/recruitment/candidates/{candidate}/convert', [RecruitmentController::class, 'convertCandidate'])->name('recruitment.candidates.convert');
        Route::get('/performance', [PerformanceReviewController::class, 'index'])->name('performance.index');
        Route::post('/performance', [PerformanceReviewController::class, 'store'])->name('performance.store');
        Route::put('/performance/{review}', [PerformanceReviewController::class, 'update'])->name('performance.update');
        Route::patch('/performance/{review}/decision', [PerformanceReviewController::class, 'decide'])->name('performance.decide');
        Route::post('/performance/{review}/employee-comment', [EmployeePortalController::class, 'commentOnReview'])->name('performance.employee-comment');
        Route::get('/training', [TrainingController::class, 'index'])->name('training.index');
        Route::post('/training/courses', [TrainingController::class, 'storeCourse'])->name('training.courses.store');
        Route::post('/training/enrollments', [TrainingController::class, 'enroll'])->name('training.enrollments.store');
        Route::patch('/training/enrollments/{enrollment}', [TrainingController::class, 'updateEnrollment'])->name('training.enrollments.update');
        Route::post('/training/plans', [TrainingController::class, 'storePlan'])->name('training.plans.store');
        Route::put('/training/plans/{plan}', [TrainingController::class, 'updatePlan'])->name('training.plans.update');
        Route::post('/training/plans/{plan}/needs', [TrainingController::class, 'storeNeed'])->name('training.plans.needs.store');
        Route::post('/training/needs', [TrainingController::class, 'storeNeedFromForm'])->name('training.needs.store');
        Route::patch('/training/plans/{plan}/submit', [TrainingController::class, 'submitPlan'])->name('training.plans.submit');
        Route::patch('/training/plans/{plan}/decision', [TrainingController::class, 'decidePlan'])->name('training.plans.decision');
        Route::get('/internal-comms', [InternalCommunicationController::class,'index'])->name('internal-comms.index');
        Route::post('/internal-comms', [InternalCommunicationController::class,'store'])->name('internal-comms.store');
        Route::patch('/internal-comms/{announcement}/publish', [InternalCommunicationController::class,'publish'])->name('internal-comms.publish');
        Route::patch('/internal-comms/{announcement}/archive', [InternalCommunicationController::class,'archive'])->name('internal-comms.archive');
        Route::get('/internal-comms/{announcement}/download', [InternalCommunicationController::class,'download'])->name('internal-comms.download');
        Route::get('/employee-portal', [EmployeePortalController::class,'index'])->name('employee-portal.index');
        Route::post('/employee-portal/profile-update', [EmployeeRequestController::class,'storeProfileUpdate'])->name('employee-portal.profile-update');
        Route::post('/employee-portal/letters', [EmployeeRequestController::class,'storeLetter'])->name('employee-portal.letters');
        Route::post('/employee-portal/leave', [EmployeePortalController::class,'requestLeave'])->name('employee-portal.leave');
        Route::post('/employee-portal/announcements/{announcement}/acknowledge', [EmployeePortalController::class,'acknowledge'])->name('employee-portal.acknowledge');
        Route::get('/employee-portal/requests/{employeeRequest}/download', [EmployeeRequestController::class,'downloadResolution'])->name('employee-portal.requests.download');
        Route::get('/employee-requests', [EmployeeRequestController::class,'index'])->name('employee-requests.index');
        Route::patch('/employee-requests/{employeeRequest}/review', [EmployeeRequestController::class,'review'])->name('employee-requests.review');
        Route::get('/employee-documents', [EmployeeDocumentController::class, 'index'])->name('employee-documents.index');
        Route::post('/employee-documents', [EmployeeDocumentController::class, 'store'])->name('employee-documents.store');
        Route::get('/employee-documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employee-documents.download');
        Route::delete('/employee-documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employee-documents.destroy');

        // Profile
        Route::get('/profile/security', [SecurityController::class, 'index'])->name('security.index');
        Route::post('/profile/security/mfa', [SecurityController::class, 'beginMfa'])->name('security.mfa.begin');
        Route::post('/profile/security/mfa/enable', [SecurityController::class, 'enableMfa'])->name('security.mfa.enable');
        Route::delete('/profile/security/mfa', [SecurityController::class, 'disableMfa'])->name('security.mfa.disable');
        Route::delete('/profile/security/sessions/{session}', [SecurityController::class, 'revokeSession'])->name('security.sessions.revoke');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/retention-policies', [RetentionPolicyController::class, 'index'])->name('retention.index');
        Route::put('/retention-policies/{policy}', [RetentionPolicyController::class, 'update'])->name('retention.update');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
});

require __DIR__ . '/auth.php';
