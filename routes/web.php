<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MoveOutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\MeterController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LandlordController;
use App\Http\Controllers\LandlordLedgerController;
use App\Http\Controllers\LandlordPayableController;
use App\Http\Controllers\AjaxUnitController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\InspectionPersonController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\ReceivingVoucherController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\ProfitLossController;
use App\Http\Controllers\ReceivablePayableReportController;
use App\Http\Controllers\OwnerDuesController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskCategoryController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Guest Routes (unauthenticated only)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    // Password Reset Routes
    Route::get('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'store'])->name('password.update');
});

Route::get('bills/{hash}', [PaymentController::class, 'publicPrint'])->name('payments.public-print');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:units.import')->group(function () {
        Route::get('units/import', [UnitController::class, 'importForm'])->name('units.import.form');
        Route::post('units/import', [UnitController::class, 'importSubmit'])->name('units.import.submit');
        Route::get('units/import/template', [UnitController::class, 'downloadTemplate'])->name('units.import.template');
    });

    // Units — admin + super-admin only
    Route::middleware('permission:units.view')->group(function () {
        Route::get('units/print',               [UnitController::class, 'print'])->name('units.print');
        Route::get('units/print-meters',        [UnitController::class, 'printMeters'])->name('units.print-meters');
        Route::get('units/export-excel',        [UnitController::class, 'exportExcel'])->name('units.export-excel');
        Route::get('units/export-pdf',          [UnitController::class, 'exportPdf'])->name('units.export-pdf');
        Route::get('units/{unit}/print',        [UnitController::class, 'printOne'])->name('units.print-one');
        Route::post('units/{unit}/breaker-inspections', [App\Http\Controllers\UnitBreakerInspectionController::class, 'store'])->name('units.breaker-inspections.store');
        Route::post('units/{unit}/toggle-breaker', [\App\Http\Controllers\UnitController::class, 'toggleBreaker'])->name('units.toggle-breaker');
        Route::resource('units', UnitController::class)->except(['show']);
        Route::get('units/{unit}',              [UnitController::class, 'show'])->name('units.show');
    });

    // Users — admin + super-admin only
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/send-reset-link', [UserController::class, 'sendResetLink'])->name('users.send-reset-link');

        // Task templates for employees (managed under users)
        Route::get('users/{user}/tasks', [UserController::class, 'tasks'])->name('users.tasks');
        Route::post('users/{user}/tasks', [UserController::class, 'storeTasks'])->name('users.tasks.store');
        Route::delete('users/{user}/tasks/{template}', [UserController::class, 'destroyTask'])->name('users.tasks.destroy');
    });

    Route::middleware('permission:roles.view')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware('permission:permissions.view')->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    Route::middleware('permission:activity_logs.view')->group(function () {
        Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::middleware('permission:units.view')->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
    });

    Route::middleware('permission:tenants.wizard')->group(function () {
        Route::get('tenants/create',                [TenantController::class, 'create'])->name('tenants.create');
        Route::post('tenants',                      [TenantController::class, 'store'])->name('tenants.store');
        Route::get('tenants/{tenant}/step/{step}',  [TenantController::class, 'showStep'])->name('tenants.showStep');
        Route::post('tenants/{tenant}/step/{step}', [TenantController::class, 'saveStep'])->name('tenants.saveStep');
        Route::post('tenants/{tenant}/confirm',     [TenantController::class, 'confirm'])->name('tenants.confirm');
    });

    Route::middleware('permission:tenants.view')->group(function () {
        Route::get('tenants/print-guards',      [TenantController::class, 'printGuards'])->name('tenants.printGuards');
        Route::get('tenants/print-staff',       [TenantController::class, 'printStaff'])->name('tenants.printStaff');
        Route::get('tenants/pending-documents', [TenantController::class, 'pendingDocuments'])->name('tenants.pending-documents');
        Route::resource('tenants', TenantController::class)->except(['create', 'store']);
    });

    Route::middleware('permission:tenants.move-out')->group(function () {
        Route::get('tenants/{tenant}/move-out',         [MoveOutController::class, 'create'])->name('tenants.moveOut.create');
        Route::post('tenants/{tenant}/move-out',        [MoveOutController::class, 'store'])->name('tenants.moveOut.store');
    });

    Route::middleware('permission:tenants.print')->group(function () {
        Route::get('tenants/{tenant}/print/{step}', [TenantController::class, 'printStep'])->name('tenants.printStep');
        Route::get('tenants/{tenant}/print-move-out',   [MoveOutController::class, 'printMoveOut'])->name('tenants.printMoveOut');
        Route::get('tenants/{tenant}/clearance-form',   [MoveOutController::class, 'clearanceForm'])->name('tenants.clearanceForm');
    });

    Route::middleware('permission:landlords.view')->group(function () {
        Route::resource('landlords', LandlordController::class);
        
        // Landlord Ledgers
        Route::get('landlord-ledgers', [LandlordLedgerController::class, 'index'])->name('landlord_ledgers.index');
        Route::get('landlord-ledgers/excel', [LandlordLedgerController::class, 'exportExcel'])->name('landlord_ledgers.excel');
        Route::get('landlord-ledgers/pdf', [LandlordLedgerController::class, 'exportPdf'])->name('landlord_ledgers.pdf');
        Route::get('landlord-ledgers/print', [LandlordLedgerController::class, 'print'])->name('landlord_ledgers.print');
        Route::get('landlord-ledgers/{landlord}', [LandlordLedgerController::class, 'show'])->name('landlord_ledgers.show');
    });

    Route::middleware('permission:parties.view')->group(function () {
        Route::resource('parties', \App\Http\Controllers\PartyController::class);
    });

    Route::middleware('permission:general_receiving_vouchers.view')->group(function () {
        Route::get('general-receiving-vouchers-print-list', [\App\Http\Controllers\GeneralReceivingVoucherController::class, 'printList'])
            ->name('general-receiving-vouchers.print-list');
        Route::resource('general-receiving-vouchers', \App\Http\Controllers\GeneralReceivingVoucherController::class);
        Route::get('general-receiving-vouchers/{general_receiving_voucher}/print', [\App\Http\Controllers\GeneralReceivingVoucherController::class, 'print'])->name('general-receiving-vouchers.print');
    });

    // Utility Meter Readings (Month & Unit Filtered Grid Interface)
    Route::get('meter-reading-vouchers', fn() => redirect()->route('utility-readings.index'));
    Route::middleware('permission:utility_readings.view,utility_readings.edit,utilities.record,utility_meters_management,meters.edit,meter_vouchers.view')->group(function () {
        Route::get('utility-readings', [\App\Http\Controllers\UtilityReadingController::class, 'index'])->name('utility-readings.index');
        Route::get('utility-readings/print', [\App\Http\Controllers\UtilityReadingController::class, 'print'])->name('utility-readings.print');
        Route::post('utility-readings/update-row', [\App\Http\Controllers\UtilityReadingController::class, 'updateRow'])->name('utility-readings.update-row');
        Route::post('utility-readings/upload-image', [\App\Http\Controllers\UtilityReadingController::class, 'uploadImage'])->name('utility-readings.upload-image');
    });
 
    Route::middleware('permission:payment_accounts.view')->group(function () {
        Route::resource('payment-accounts', PaymentAccountController::class);
    });

    Route::middleware('permission:inspection_persons.view')->group(function () {
        Route::resource('inspection-persons', InspectionPersonController::class);
    });

    // Report Types Setup
    // ⚠️ Static /create MUST come before wildcard /{reportType} routes
    Route::middleware('permission:report_types.create')->group(function () {
        Route::get('report-types/create', [\App\Http\Controllers\ReportTypeController::class, 'create'])->name('report-types.create');
        Route::post('report-types', [\App\Http\Controllers\ReportTypeController::class, 'store'])->name('report-types.store');
        Route::post('report-types/{reportType}/remarks', [\App\Http\Controllers\ReportTypeController::class, 'addRemark'])->name('report-types.remarks.store');
        Route::post('report-types/{reportType}/members', [\App\Http\Controllers\ReportTypeController::class, 'addMember'])->name('report-types.members.store');
    });
    Route::middleware('permission:report_types.view')->group(function () {
        Route::get('report-types', [\App\Http\Controllers\ReportTypeController::class, 'index'])->name('report-types.index');
        Route::get('report-types/{reportType}', [\App\Http\Controllers\ReportTypeController::class, 'show'])->name('report-types.show');
        Route::get('report-types/{reportType}/remarks', [\App\Http\Controllers\ReportTypeController::class, 'remarks'])->name('report-types.remarks');
        Route::get('report-types/{reportType}/members', [\App\Http\Controllers\ReportTypeController::class, 'members'])->name('report-types.members');
    });
    Route::middleware('permission:report_types.edit')->group(function () {
        Route::get('report-types/{reportType}/edit', [\App\Http\Controllers\ReportTypeController::class, 'edit'])->name('report-types.edit');
        Route::put('report-types/{reportType}', [\App\Http\Controllers\ReportTypeController::class, 'update'])->name('report-types.update');
        Route::post('report-types/{reportType}/toggle-status', [\App\Http\Controllers\ReportTypeController::class, 'toggleStatus'])->name('report-types.toggle-status');
        Route::put('report-types/{reportType}/members/{member}', [\App\Http\Controllers\ReportTypeController::class, 'updateMember'])->name('report-types.members.update');
        Route::post('report-types/{reportType}/members/{member}/toggle-status', [\App\Http\Controllers\ReportTypeController::class, 'toggleMemberStatus'])->name('report-types.members.toggle-status');
    });
    Route::middleware('permission:report_types.delete')->group(function () {
        Route::delete('report-types/{reportType}', [\App\Http\Controllers\ReportTypeController::class, 'destroy'])->name('report-types.destroy');
        Route::delete('report-types/{reportType}/remarks/{remark}', [\App\Http\Controllers\ReportTypeController::class, 'deleteRemark'])->name('report-types.remarks.destroy');
    });

    // Inspection Heads Setup
    Route::middleware('permission:inspection_heads.create')->group(function () {
        Route::get('inspection-heads/create', [\App\Http\Controllers\InspectionHeadController::class, 'create'])->name('inspection-heads.create');
        Route::post('inspection-heads', [\App\Http\Controllers\InspectionHeadController::class, 'store'])->name('inspection-heads.store');
    });
    Route::middleware('permission:inspection_heads.view')->group(function () {
        Route::get('inspection-heads', [\App\Http\Controllers\InspectionHeadController::class, 'index'])->name('inspection-heads.index');
    });
    Route::middleware('permission:inspection_heads.edit')->group(function () {
        Route::get('inspection-heads/{inspectionHead}/edit', [\App\Http\Controllers\InspectionHeadController::class, 'edit'])->name('inspection-heads.edit');
        Route::put('inspection-heads/{inspectionHead}', [\App\Http\Controllers\InspectionHeadController::class, 'update'])->name('inspection-heads.update');
        Route::post('inspection-heads/{inspectionHead}/toggle-status', [\App\Http\Controllers\InspectionHeadController::class, 'toggleStatus'])->name('inspection-heads.toggle-status');
    });
    Route::middleware('permission:inspection_heads.delete')->group(function () {
        Route::delete('inspection-heads/{inspectionHead}', [\App\Http\Controllers\InspectionHeadController::class, 'destroy'])->name('inspection-heads.destroy');
    });

    // Dynamic Inspection Reports & Flat Inspection Reports per Type
    // Static /create and general routes
    Route::get('inspection-reports/{type}/create', [\App\Http\Controllers\InspectionReportController::class, 'create'])->name('inspection-reports.create');
    Route::post('inspection-reports/{type}', [\App\Http\Controllers\InspectionReportController::class, 'store'])->name('inspection-reports.store');
    Route::get('inspection-reports/{type}', [\App\Http\Controllers\InspectionReportController::class, 'index'])->name('inspection-reports.index');
    Route::get('inspection-reports/{type}/{report}', [\App\Http\Controllers\InspectionReportController::class, 'show'])->name('inspection-reports.show');
    Route::get('inspection-reports/{type}/{report}/print', [\App\Http\Controllers\InspectionReportController::class, 'print'])->name('inspection-reports.print');
    Route::get('inspection-reports/{type}/{report}/edit', [\App\Http\Controllers\InspectionReportController::class, 'edit'])->name('inspection-reports.edit');
    Route::put('inspection-reports/{type}/{report}', [\App\Http\Controllers\InspectionReportController::class, 'update'])->name('inspection-reports.update');
    Route::delete('inspection-reports/{type}/{report}', [\App\Http\Controllers\InspectionReportController::class, 'destroy'])->name('inspection-reports.destroy');
    Route::get('cleaning-inspections/create', fn() => redirect()->route('inspection-reports.create', 'cleaning'));
    Route::get('cleaning-inspections', fn() => redirect()->route('inspection-reports.index', 'cleaning'));

    // Flat Inspection Reports (Agreement Move-In / Move-Out)
    // ⚠️ Static /create MUST come before wildcard /{id} routes
    Route::middleware('permission:flat_inspections.create')->group(function () {
        Route::get('flat-inspections/create', [\App\Http\Controllers\FlatInspectionReportController::class, 'create'])->name('flat-inspections.create');
        Route::post('flat-inspections', [\App\Http\Controllers\FlatInspectionReportController::class, 'store'])->name('flat-inspections.store');
    });
    Route::middleware('permission:flat_inspections.view')->group(function () {
        Route::get('flat-inspections/{flatInspectionReport}', [\App\Http\Controllers\FlatInspectionReportController::class, 'show'])->name('flat-inspections.show');
        Route::get('flat-inspections/{flatInspectionReport}/print', [\App\Http\Controllers\FlatInspectionReportController::class, 'print'])->name('flat-inspections.print');
    });
    Route::middleware('permission:flat_inspections.delete')->group(function () {
        Route::delete('flat-inspections/{flatInspectionReport}', [\App\Http\Controllers\FlatInspectionReportController::class, 'destroy'])->name('flat-inspections.destroy');
    });

    // Post Schedule Heads
    Route::middleware('permission:post_schedule_heads.view')->group(function () {
        Route::resource('post-schedule-heads', \App\Http\Controllers\PostScheduleHeadController::class)->except(['show', 'create', 'edit']);
    });

    // Post Schedules (Day-based duty roster & daily print)
    Route::get('post-schedules/print-daily', [\App\Http\Controllers\PostScheduleController::class, 'printDaily'])->name('post-schedules.print-daily');
    Route::post('post-schedules/copy-days', [\App\Http\Controllers\PostScheduleController::class, 'copyDays'])->name('post-schedules.copy-days');
    Route::resource('post-schedules', \App\Http\Controllers\PostScheduleController::class);

    Route::middleware('permission:agreements.view')->group(function () {
        // Agreements are view-only — creation happens via tenant wizard
        Route::resource('agreements', AgreementController::class)->only(['index', 'show', 'edit', 'update', 'destroy']);
    });

    Route::middleware('permission:units.vacate')->group(function () {
        Route::post('units/{unit}/vacate',      [UnitController::class, 'vacate'])->name('units.vacate');
    });

    Route::middleware('permission:units.add-tenant')->group(function () {
        Route::get('units/{unit}/add-tenant',   [UnitController::class, 'addTenant'])->name('units.addTenant');
    });

    // AJAX routes — no permission middleware needed, just auth
    Route::get('ajax/tenant-by-unit', [PaymentController::class, 'getTenantByUnit'])
        ->name('ajax.tenant-by-unit');
    Route::get('ajax/tenant-by-cnic', [TenantController::class, 'getTenantByCnic'])
        ->name('ajax.tenant-by-cnic');
    Route::get('ajax/previous-reading', [PaymentController::class, 'getPreviousReading'])
        ->name('ajax.previous-reading');
    Route::post('ajax/landlord-units/check-unique', [AjaxUnitController::class, 'checkUnique'])
        ->name('ajax.landlord-units.check-unique');
    Route::get('ajax/unit-search', [AjaxUnitController::class, 'search'])
        ->name('ajax.unit-search');

    // Meter AJAX routes (embedded in Unit create/edit)
    Route::get('ajax/meters/{unit}', [MeterController::class, 'byUnit'])->name('ajax.meters.by-unit');
    Route::middleware('permission:meters.edit,units.edit')->group(function () {
        Route::post('ajax/meters', [MeterController::class, 'store'])->name('ajax.meters.store');
        Route::put('ajax/meters/{meter}', [MeterController::class, 'update'])->name('ajax.meters.update');
    });
    Route::middleware('permission:meters.delete,units.edit')->group(function () {
        Route::delete('ajax/meters/{meter}', [MeterController::class, 'destroy'])->name('ajax.meters.destroy');
    });

    // Unit AJAX routes — managed from Landlord form
    Route::get(   'ajax/landlord-units/{landlord}',              [AjaxUnitController::class, 'byLandlord'])->name('ajax.landlord-units.by-landlord');
    Route::middleware('permission:landlords.edit-units')->group(function () {
        Route::post(  'ajax/landlord-units',                         [AjaxUnitController::class, 'store'])     ->name('ajax.landlord-units.store');
        Route::put(   'ajax/landlord-units/{unit}',                  [AjaxUnitController::class, 'update'])    ->name('ajax.landlord-units.update');
        Route::delete('ajax/landlord-units/{unit}',                  [AjaxUnitController::class, 'destroy'])   ->name('ajax.landlord-units.destroy');
        Route::post(  'ajax/landlord-units/{unit}/transfer',         [AjaxUnitController::class, 'transfer'])  ->name('ajax.landlord-units.transfer');
    });

    Route::middleware('permission:payments.bulk-generate')->group(function () {
        Route::get('payments/bulk-management', [PaymentController::class, 'bulkManagementForm'])
            ->name('payments.bulk-management');
        Route::post('payments/bulk-preview', [PaymentController::class, 'bulkPreview'])
            ->name('payments.bulk-preview');
        Route::post('payments/bulk-commit', [PaymentController::class, 'bulkCommit'])
            ->name('payments.bulk-commit');
        Route::post('payments/bulk-generate', [PaymentController::class, 'bulkGenerate'])
            ->name('payments.bulk-generate');
        Route::post('payments/bulk-edit', [PaymentController::class, 'bulkEdit'])
            ->name('payments.bulk-edit');
        Route::delete('payments/bulk-delete', [PaymentController::class, 'bulkDelete'])
            ->name('payments.bulk-delete');
    });

    Route::middleware('permission:payments.view')->group(function () {
        Route::get('payments/history', [PaymentController::class, 'history'])->name('payments.history');
        Route::resource('payments', PaymentController::class)->whereNumber('payment');
    });

    Route::middleware('permission:payments.record')->group(function () {
        Route::post('payments/{payment}/record', [PaymentController::class, 'recordPayment'])
            ->name('payments.record');
        Route::patch('payments/{payment}/toggle-status', [PaymentController::class, 'toggleStatus'])
            ->name('payments.toggle-status');
    });

    Route::middleware('permission:payments.print')->group(function () {
        Route::get('payments/{payment}/print', [PaymentController::class, 'print'])
            ->name('payments.print');
    });

    Route::middleware('permission:utilities.record')->group(function () {
        Route::get('payments/utilities/create', [PaymentController::class, 'createUtilityReading'])
            ->name('payments.utilities.create');
        Route::post('payments/utilities', [PaymentController::class, 'storeUtilityReading'])
            ->name('payments.utilities.store');
    });

    // AJAX
    Route::get('ajax/agreement-by-tenant', [PaymentController::class, 'getAgreementByTenant'])
        ->name('ajax.agreement-by-tenant');
    Route::get('ajax/agreement-by-unit', [PaymentController::class, 'getAgreementByUnit'])
        ->name('ajax.agreement-by-unit');
    Route::get('ajax/tenant-pending-payments', [ReceivingVoucherController::class, 'getTenantPendingPayments'])
        ->name('ajax.tenant-pending-payments');

    // Owners
    Route::middleware('permission:owners.view')->group(function () {
        Route::get('owners/dues', fn() => redirect()->route('ledgers.owner'))->name('owners.dues');
        Route::resource('owners', OwnerController::class);
        Route::resource('withdrawals', WithdrawalController::class);
    });

    // Receiving Vouchers
    Route::middleware('permission:receiving_vouchers.view')->group(function () {
        Route::get('receiving-vouchers-print-list', [ReceivingVoucherController::class, 'printList'])
            ->name('receiving-vouchers.print-list');
        Route::resource('receiving-vouchers', ReceivingVoucherController::class);
        Route::get('receiving-vouchers/{receiving_voucher}/print', [ReceivingVoucherController::class, 'print'])
            ->name('receiving-vouchers.print');
    });

    // Payment Vouchers
    Route::middleware('permission:payment_vouchers.view')->group(function () {
        Route::get('payment-vouchers-print-list', [PaymentVoucherController::class, 'printList'])
            ->name('payment-vouchers.print-list');
        Route::resource('payment-vouchers', PaymentVoucherController::class);
        Route::get('payment-vouchers/{payment_voucher}/print', [PaymentVoucherController::class, 'print'])
            ->name('payment-vouchers.print');
    });

    // Profit & Loss Report
    Route::middleware('permission:reports.profit_loss')->group(function () {
        Route::get('reports/profit-loss', [ProfitLossController::class, 'index'])->name('reports.profit-loss');
        Route::get('reports/profit-loss/pdf', [ProfitLossController::class, 'exportPdf'])->name('reports.profit-loss.pdf');
        Route::get('reports/profit-loss/excel', [ProfitLossController::class, 'exportExcel'])->name('reports.profit-loss.excel');
    });
    Route::middleware('permission:reports.view')->group(function () {
        // Receivables & Payables Reports
        Route::get('reports/receivables', [ReceivablePayableReportController::class, 'receivables'])->name('reports.receivables');
        Route::get('reports/receivables/pdf', [ReceivablePayableReportController::class, 'receivablesPdf'])->name('reports.receivables.pdf');
        Route::get('reports/payables', [ReceivablePayableReportController::class, 'payables'])->name('reports.payables');
        Route::get('reports/payables/pdf', [ReceivablePayableReportController::class, 'payablesPdf'])->name('reports.payables.pdf');
    });

    // AJAX: owner pending balance (used in Payment Voucher form)
    Route::get('ajax/owner-pending-balance', [ReceivablePayableReportController::class, 'getOwnerBalance'])->name('ajax.owner-pending-balance');
    Route::get('ajax/tenant-security-deposits', [App\Http\Controllers\PaymentVoucherController::class, 'getTenantSecurityDeposits'])->name('ajax.tenant-security-deposits');
    Route::get('ajax/landlord-pending-balance', [App\Http\Controllers\PaymentVoucherController::class, 'getLandlordBalance'])->name('ajax.landlord-pending-balance');
    Route::get('ajax/landlord-receivables', [App\Http\Controllers\GeneralReceivingVoucherController::class, 'getLandlordReceivables'])->name('ajax.landlord-receivables');

    // Ledgers
    Route::middleware('permission:ledgers.view')->group(function () {
        Route::get('ledgers/tenant', [\App\Http\Controllers\LedgerController::class, 'tenant'])->name('ledgers.tenant');
        Route::get('ledgers/tenant/pdf', [\App\Http\Controllers\LedgerController::class, 'exportTenantPdf'])->name('ledgers.tenant.pdf');
        Route::get('ledgers/tenant/excel', [\App\Http\Controllers\LedgerController::class, 'exportTenantExcel'])->name('ledgers.tenant.excel');
        Route::get('ledgers/tenant/print', [\App\Http\Controllers\LedgerController::class, 'printTenant'])->name('ledgers.tenant.print');

        Route::get('ledgers/owner', [\App\Http\Controllers\LedgerController::class, 'owner'])->name('ledgers.owner');
        Route::get('ledgers/owner/pdf', [\App\Http\Controllers\LedgerController::class, 'exportOwnerPdf'])->name('ledgers.owner.pdf');
        Route::get('ledgers/owner/excel', [\App\Http\Controllers\LedgerController::class, 'exportOwnerExcel'])->name('ledgers.owner.excel');
        Route::get('ledgers/owner/print', [\App\Http\Controllers\LedgerController::class, 'printOwner'])->name('ledgers.owner.print');

        Route::get('ledgers/payment-account', [\App\Http\Controllers\LedgerController::class, 'paymentAccount'])->name('ledgers.payment-account');
        Route::get('ledgers/payment-account/pdf', [\App\Http\Controllers\LedgerController::class, 'exportAccountPdf'])->name('ledgers.payment-account.pdf');
        Route::get('ledgers/payment-account/excel', [\App\Http\Controllers\LedgerController::class, 'exportAccountExcel'])->name('ledgers.payment-account.excel');
        Route::get('ledgers/payment-account/print', [\App\Http\Controllers\LedgerController::class, 'printAccount'])->name('ledgers.payment-account.print');

        Route::get('ledgers/expense', [\App\Http\Controllers\LedgerController::class, 'expense'])->name('ledgers.expense');
        Route::get('ledgers/expense/pdf', [\App\Http\Controllers\LedgerController::class, 'exportExpensePdf'])->name('ledgers.expense.pdf');
        Route::get('ledgers/expense/excel', [\App\Http\Controllers\LedgerController::class, 'exportExpenseExcel'])->name('ledgers.expense.excel');
        Route::get('ledgers/expense/print', [\App\Http\Controllers\LedgerController::class, 'printExpense'])->name('ledgers.expense.print');

        // Party Ledger Routes
        Route::get('ledgers/party', [\App\Http\Controllers\PartyLedgerController::class, 'index'])->name('ledgers.party');
        Route::post('ledgers/party/dues', [\App\Http\Controllers\PartyLedgerController::class, 'storeDue'])->name('ledgers.party.dues.store');
        Route::delete('ledgers/party/dues/{due}', [\App\Http\Controllers\PartyLedgerController::class, 'destroyDue'])->name('ledgers.party.dues.destroy');
        Route::get('ledgers/party/print', [\App\Http\Controllers\PartyLedgerController::class, 'print'])->name('ledgers.party.print');
    });


    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('reports/print', [ReportController::class, 'print'])->name('reports.print');
        
        Route::get('reports/account-summary', [\App\Http\Controllers\AccountSummaryController::class, 'index'])->name('reports.account_summary');
        Route::get('reports/account-summary/pdf', [\App\Http\Controllers\AccountSummaryController::class, 'exportPdf'])->name('reports.account_summary.pdf');
        Route::get('reports/account-summary/excel', [\App\Http\Controllers\AccountSummaryController::class, 'exportExcel'])->name('reports.account_summary.excel');
        Route::get('reports/meter-readings', fn() => redirect()->route('utility-readings.index'));
        Route::get('reports/meter-readings/print', fn() => redirect()->route('utility-readings.print'));
    });


    Route::middleware('permission:reports.daybook')->group(function () {
        Route::get('reports/day-book', [\App\Http\Controllers\DayBookController::class, 'index'])->name('reports.day-book');
        Route::get('reports/day-book/print', [\App\Http\Controllers\DayBookController::class, 'print'])->name('reports.day-book.print');
    });

    Route::middleware('permission:reports.cashbook')->group(function () {
        Route::get('reports/cash-book', [\App\Http\Controllers\CashBookController::class, 'index'])->name('reports.cash-book');
        Route::get('reports/cash-book/print', [\App\Http\Controllers\CashBookController::class, 'print'])->name('reports.cash-book.print');
    });


    Route::middleware('permission:expense_heads.view')->group(function () {
        Route::resource('expense-heads', \App\Http\Controllers\ExpenseHeadController::class)->except(['show']);
    });

    Route::middleware('permission:expenses.view')->group(function () {
        Route::get('expense-vouchers-print-list', [\App\Http\Controllers\ExpenseController::class, 'printList'])
            ->name('expenses.print-list');
        Route::resource('expense-vouchers', \App\Http\Controllers\ExpenseController::class)->names([
            'index'   => 'expenses.index',
            'create'  => 'expenses.create',
            'store'   => 'expenses.store',
            'show'    => 'expenses.show',
            'edit'    => 'expenses.edit',
            'update'  => 'expenses.update',
            'destroy' => 'expenses.destroy',
        ])->parameters([
            'expense-vouchers' => 'expense',
        ]);
        Route::get('expense-vouchers/{expense}/print', [\App\Http\Controllers\ExpenseController::class, 'print'])
            ->name('expenses.print');
    });

    Route::middleware('permission:jv_vouchers.view')->group(function () {
        Route::get('jv-vouchers-print-list', [\App\Http\Controllers\JvVoucherController::class, 'printList'])
            ->name('jv-vouchers.print-list');
        Route::get('jv-vouchers/{jvVoucher}/settle', [\App\Http\Controllers\JvVoucherController::class, 'payForm'])
            ->name('jv-vouchers.settle');
        Route::post('jv-vouchers/{jvVoucher}/pay', [\App\Http\Controllers\JvVoucherController::class, 'pay'])
            ->name('jv-vouchers.pay');
        Route::resource('jv-vouchers', \App\Http\Controllers\JvVoucherController::class)->names([
            'index'   => 'jv-vouchers.index',
            'create'  => 'jv-vouchers.create',
            'store'   => 'jv-vouchers.store',
            'show'    => 'jv-vouchers.show',
            'edit'    => 'jv-vouchers.edit',
            'update'  => 'jv-vouchers.update',
            'destroy' => 'jv-vouchers.destroy',
        ])->parameters([
            'jv-vouchers' => 'jvVoucher',
        ]);
        Route::get('jv-vouchers/{jvVoucher}/print', [\App\Http\Controllers\JvVoucherController::class, 'print'])
            ->name('jv-vouchers.print');
    });

    // Other Owned Rent Purchase (ORP) Vouchers
    Route::middleware('permission:other_owned_rent_purchase_vouchers.view')->group(function () {
        Route::resource('other-owned-rent-purchase-vouchers', \App\Http\Controllers\OtherOwnedRentPurchaseVoucherController::class)->names([
            'index'   => 'other-owned-rent-purchase-vouchers.index',
            'create'  => 'other-owned-rent-purchase-vouchers.create',
            'store'   => 'other-owned-rent-purchase-vouchers.store',
            'show'    => 'other-owned-rent-purchase-vouchers.show',
            'edit'    => 'other-owned-rent-purchase-vouchers.edit',
            'update'  => 'other-owned-rent-purchase-vouchers.update',
            'destroy' => 'other-owned-rent-purchase-vouchers.destroy',
        ])->parameters([
            'other-owned-rent-purchase-vouchers' => 'otherOwnedRentPurchaseVoucher',
        ]);
        Route::get('other-owned-rent-purchase-vouchers/{otherOwnedRentPurchaseVoucher}/print',
            [\App\Http\Controllers\OtherOwnedRentPurchaseVoucherController::class, 'print'])
            ->name('other-owned-rent-purchase-vouchers.print');
    });

    // AJAX: Get self-owned units by landlord (for ORP Voucher create form)
    Route::get('ajax/landlord-self-units', [\App\Http\Controllers\OtherOwnedRentPurchaseVoucherController::class, 'getLandlordUnits'])
        ->name('ajax.landlord-self-units');

    // Note Pad (Google Keep style tasks & notes)
    Route::middleware('permission:note_pads.view')->group(function () {
        Route::resource('note-pads', \App\Http\Controllers\NotePadController::class)->names([
            'index'   => 'note-pads.index',
            'create'  => 'note-pads.create',
            'store'   => 'note-pads.store',
            'show'    => 'note-pads.show',
            'edit'    => 'note-pads.edit',
            'update'  => 'note-pads.update',
            'destroy' => 'note-pads.destroy',
        ])->parameters([
            'note-pads' => 'notePad',
        ]);
        Route::post('note-pads/{notePad}/toggle-pin', [\App\Http\Controllers\NotePadController::class, 'togglePin'])
            ->name('note-pads.toggle-pin');
        Route::post('note-pads/{notePad}/toggle-task', [\App\Http\Controllers\NotePadController::class, 'toggleTask'])
            ->name('note-pads.toggle-task');
    });

    // Other Tenants
    Route::middleware('permission:other_tenants.view')->group(function () {
        Route::get('other-tenants/print', [\App\Http\Controllers\OtherTenantController::class, 'print'])->name('other-tenants.print');
        Route::get('other-tenants/{other_tenant}/statement-print', [\App\Http\Controllers\OtherTenantController::class, 'printStatement'])->name('other-tenants.statement-print');
        Route::resource('other-tenants', \App\Http\Controllers\OtherTenantController::class);
    });
    Route::middleware('permission:other_tenants.attach')->group(function () {
        Route::post('other-tenants/{other_tenant}/attach', [\App\Http\Controllers\OtherTenantController::class, 'attach'])
            ->name('other-tenants.attach');
        Route::post('other-tenants/{other_tenant}/detach', [\App\Http\Controllers\OtherTenantController::class, 'detach'])
            ->name('other-tenants.detach');
        Route::put('other-tenants/{other_tenant}/unit-history/{history}', [\App\Http\Controllers\OtherTenantController::class, 'updateUnitHistory'])
            ->name('other-tenants.update-unit-history');
    });

    // Inventory & Stock Management
    Route::middleware('permission:inventory.view')->group(function () {
        Route::resource('inventory/items', \App\Http\Controllers\InventoryItemController::class)->except(['show']);
        Route::resource('inventory/stock-entries', \App\Http\Controllers\StockEntryController::class)->except(['edit', 'update']);
    });

    Route::middleware('permission:gatepasses.view')->group(function () {
        Route::resource('inventory/gate-passes', \App\Http\Controllers\GatePassController::class)->except(['edit', 'update']);
        Route::get('inventory/gate-passes/{gate_pass}/print', [\App\Http\Controllers\GatePassController::class, 'print'])
            ->name('gate-passes.print');
        Route::post('inventory/gate-passes/{gate_pass}/cancel', [\App\Http\Controllers\GatePassController::class, 'cancel'])
            ->name('gate-passes.cancel');
    });

    // AJAX

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/units-detail', [DashboardController::class, 'unitsDetail'])->name('dashboard.units-detail');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Calendar
    Route::get('/calendar', function () {
        return view('pages.calender', ['title' => 'Calendar']);
    })->name('calendar');

    // Profile
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Form pages
    Route::get('/form-elements', function () {
        return view('pages.form.form-elements', ['title' => 'Form Elements']);
    })->name('form-elements');

    // Tables pages
    Route::get('/basic-tables', function () {
        return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
    })->name('basic-tables');

    // Blank page
    Route::get('/blank', function () {
        return view('pages.blank', ['title' => 'Blank']);
    })->name('blank');

    // Error pages
    Route::get('/error-404', function () {
        return view('pages.errors.error-404', ['title' => 'Error 404']);
    })->name('error-404');

    // Chart pages
    Route::get('/line-chart', function () {
        return view('pages.chart.line-chart', ['title' => 'Line Chart']);
    })->name('line-chart');

    Route::get('/bar-chart', function () {
        return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
    })->name('bar-chart');

    // UI Elements pages
    Route::get('/alerts', function () {
        return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
    })->name('alerts');

    Route::get('/avatars', function () {
        return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
    })->name('avatars');

    Route::get('/badge', function () {
        return view('pages.ui-elements.badges', ['title' => 'Badges']);
    })->name('badges');

    Route::get('/buttons', function () {
        return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
    })->name('buttons');

    Route::get('/image', function () {
        return view('pages.ui-elements.images', ['title' => 'Images']);
    })->name('images');

    Route::get('/videos', function () {
        return view('pages.ui-elements.videos', ['title' => 'Videos']);
    })->name('videos');

    // Task Management Routes
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/print', [TaskController::class, 'print'])->name('tasks.print');
    Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
    Route::get('/tasks/rows', [TaskController::class, 'tableRows'])->name('tasks.rows');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/data', [TaskController::class, 'getData'])->name('tasks.data');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.update-status');
    Route::get('/tasks/{task}/comments', [TaskController::class, 'getComments'])->name('tasks.comments.index');
    Route::post('/tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Task Category Management Routes
    Route::get('/task-categories', [TaskCategoryController::class, 'index'])->name('task-categories.index');
    Route::post('/task-categories', [TaskCategoryController::class, 'store'])->name('task-categories.store');
    Route::patch('/task-categories/{taskCategory}/toggle', [TaskCategoryController::class, 'toggleStatus'])->name('task-categories.toggle');

    // Notification API Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

    // -----------------------------------------------------------------------
    // Employee Performance Module
    // -----------------------------------------------------------------------

    // Legacy /employees route redirects to /users
    Route::redirect('/employees', '/users')->name('employees.index');
    Route::redirect('/employees/create', '/users/create')->name('employees.create');
    Route::get('/employees/{user}', fn(\App\Models\User $user) => redirect()->route('users.show', $user))->name('employees.show');
    Route::get('/employees/{user}/edit', fn(\App\Models\User $user) => redirect()->route('users.edit', $user))->name('employees.edit');
    Route::get('/employees/{user}/tasks', fn(\App\Models\User $user) => redirect()->route('users.tasks', $user))->name('employees.tasks');


    // Daily entry (employee marks own attendance + tasks)
    Route::get('/performance/daily', [\App\Http\Controllers\PerformanceController::class, 'daily'])->name('performance.daily');
    Route::post('/performance/daily', [\App\Http\Controllers\PerformanceController::class, 'saveDaily'])->name('performance.daily.save');

    // Reports
    Route::middleware('permission:performance.reports.view,employees.view')->group(function () {
        Route::get('/performance', [\App\Http\Controllers\PerformanceController::class, 'index'])->name('performance.index');
        Route::post('/performance/{employee}/report/generate', [\App\Http\Controllers\PerformanceController::class, 'generateReport'])->name('performance.report.generate');
    });

    Route::get('/performance/{employee}/report/{year}/{month}', [\App\Http\Controllers\PerformanceController::class, 'report'])->name('performance.report');
    Route::get('/performance/{employee}/report/{year}/{month}/pdf', [\App\Http\Controllers\PerformanceController::class, 'reportPdf'])->name('performance.report.pdf');
});

