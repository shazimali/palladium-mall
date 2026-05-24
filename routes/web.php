<?php

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UtilityReadingController;
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

});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Units — admin + super-admin only
    Route::middleware('permission:units.view')->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
        Route::get('units/{unit}', [UnitController::class, 'show'])->name('units.show');
    });

    // Users — admin + super-admin only
    Route::middleware('permission:users.view')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::middleware('permission:roles.view')->group(function () {
        Route::resource('roles', RoleController::class);
    });

    Route::middleware('permission:permissions.view')->group(function () {
        Route::resource('permissions', PermissionController::class);
    });

    Route::middleware('permission:units.view')->group(function () {
        Route::resource('units', UnitController::class)->except(['show']);
    });

    Route::middleware('permission:tenants.view')->group(function () {
        Route::resource('tenants', TenantController::class);
    });

    Route::middleware('permission:agreements.view')->group(function () {
        Route::resource('agreements', AgreementController::class);
    });

    Route::middleware('permission:utilities.view')->group(function () {
        Route::resource('utilities', UtilityReadingController::class);
        Route::post('utilities/{utility}/mark-paid', [UtilityReadingController::class, 'markPaid'])
            ->name('utilities.mark-paid');
    });

    // AJAX routes — no permission middleware needed, just auth
    Route::get('ajax/tenant-by-unit', [UtilityReadingController::class, 'getTenantByUnit'])
        ->name('ajax.tenant-by-unit');
    Route::get('ajax/previous-reading', [UtilityReadingController::class, 'getPreviousReading'])
        ->name('ajax.previous-reading');

    Route::middleware('permission:payments.view')->group(function () {
        Route::resource('payments', PaymentController::class);
        Route::post('payments/{payment}/record', [PaymentController::class, 'recordPayment'])
            ->name('payments.record');
        Route::post('payments/bulk-generate', [PaymentController::class, 'bulkGenerate'])
            ->name('payments.bulk-generate');
    });

    // AJAX
    Route::get('ajax/agreement-by-tenant', [PaymentController::class, 'getAgreementByTenant'])
        ->name('ajax.agreement-by-tenant');

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
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profile']);
    })->name('profile');

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
