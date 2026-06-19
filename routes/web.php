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
use App\Http\Controllers\AjaxUnitController;
use App\Http\Controllers\PaymentAccountController;
use App\Http\Controllers\InspectionPersonController;
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
        Route::resource('units', UnitController::class)->except(['show']);
        Route::get('units/{unit}', [UnitController::class, 'show'])->name('units.show');
    });

    // Users — admin + super-admin only
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/send-reset-link', [UserController::class, 'sendResetLink'])->name('users.send-reset-link');
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
    });
 
    Route::middleware('permission:payment_accounts.view')->group(function () {
        Route::resource('payment-accounts', PaymentAccountController::class);
    });

    Route::middleware('permission:inspection_persons.view')->group(function () {
        Route::resource('inspection-persons', InspectionPersonController::class);
    });

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

    // Meter AJAX routes (embedded in Unit create/edit)
    Route::get('ajax/meters/{unit}', [MeterController::class, 'byUnit'])->name('ajax.meters.by-unit');
    Route::middleware('permission:meters.edit')->group(function () {
        Route::post('ajax/meters', [MeterController::class, 'store'])->name('ajax.meters.store');
        Route::put('ajax/meters/{meter}', [MeterController::class, 'update'])->name('ajax.meters.update');
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

    Route::middleware('permission:payments.view')->group(function () {
        Route::resource('payments', PaymentController::class);
    });

    Route::middleware('permission:payments.record')->group(function () {
        Route::post('payments/{payment}/record', [PaymentController::class, 'recordPayment'])
            ->name('payments.record');
        Route::patch('payments/{payment}/toggle-status', [PaymentController::class, 'toggleStatus'])
            ->name('payments.toggle-status');
    });

    Route::middleware('permission:payments.bulk-generate')->group(function () {
        Route::post('payments/bulk-generate', [PaymentController::class, 'bulkGenerate'])
            ->name('payments.bulk-generate');
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



    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.excel');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    });

    Route::middleware('permission:reports.daybook')->group(function () {
        Route::get('reports/day-book', [\App\Http\Controllers\DayBookController::class, 'index'])->name('reports.day-book');
    });

    Route::middleware('permission:expense_heads.view')->group(function () {
        Route::resource('expense-heads', \App\Http\Controllers\ExpenseHeadController::class)->except(['show']);
    });

    Route::middleware('permission:expenses.view')->group(function () {
        Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    });

    // Other Tenants
    Route::middleware('permission:other_tenants.view')->group(function () {
        Route::resource('other-tenants', \App\Http\Controllers\OtherTenantController::class);
    });
    Route::middleware('permission:other_tenants.attach')->group(function () {
        Route::post('other-tenants/{other_tenant}/attach', [\App\Http\Controllers\OtherTenantController::class, 'attach'])
            ->name('other-tenants.attach');
        Route::post('other-tenants/{other_tenant}/detach', [\App\Http\Controllers\OtherTenantController::class, 'detach'])
            ->name('other-tenants.detach');
    });

    // AJAX

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
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
});
