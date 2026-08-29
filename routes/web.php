<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicTenantController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\API\FinanceDashboardController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ContactController;

$mainDomain = config('app.main_domain');
$tenantDomain = config('app.tenant_domain');

Route::domain($mainDomain)->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/landing', fn() => view('landing'))->name('landing');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.app');

    Route::get('/features', fn() => view('features'))->name('features');
    Route::get('/price', fn() => view('price'))->name('price');
    Route::get('/faq', fn() => view('faq'))->name('faq');
    Route::get('/legal', fn() => view('legal'))->name('legal');
    Route::get('/contact', fn() => view('contact'))->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    Route::get('/register', fn() => view('register'))->name('register');
    Route::get('/daftar', fn() => view('register'))->name('daftar');

    Route::get('/register/tenant', [PublicTenantController::class, 'landing'])->name('tenant.register');
    Route::post('/register', [PublicTenantController::class, 'processRegistration'])->name('tenant.register.store')->middleware('throttle:3,1');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });

    Route::post('/webhook/midtrans/{tenant_code}', [WebhookController::class, 'midtrans'])->name('webhook.midtrans');
    Route::get('/midtrans-callback', [WebhookController::class, 'midtransCallback'])->name('midtrans.callback');

    Route::get('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/reset-password/{token}', [\App\Http\Controllers\ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/reset-password', [\App\Http\Controllers\ResetPasswordController::class, 'update'])->name('password.update');
});

Route::domain('{tenant_slug}.' . $tenantDomain)->middleware('tenant.subdomain')->group(function () {
    Route::get('/', [PublicTenantController::class, 'landing'])->name('tenant.landing');
    Route::get('/paket', [PublicTenantController::class, 'packages'])->name('tenant.packages');
    Route::get('/daftar', [PublicTenantController::class, 'register'])->name('tenant.register');
    Route::post('/daftar', [PublicTenantController::class, 'processRegistration'])->name('tenant.register.process')->middleware('throttle:5,1');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('tenant.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout'])->name('tenant.logout');

    Route::get('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'showForm']);
    Route::post('/forgot-password', [\App\Http\Controllers\ForgotPasswordController::class, 'sendResetLink']);
    Route::get('/reset-password/{token}', [\App\Http\Controllers\ResetPasswordController::class, 'showForm']);
    Route::post('/reset-password', [\App\Http\Controllers\ResetPasswordController::class, 'update']);

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');

    Route::middleware(['auth', 'tenant'])->group(function () {
        Route::resource('customers', CustomerController::class)
            ->names(['index' => 'customers.index', 'create' => 'customers.create', 'store' => 'customers.store', 'show' => 'customers.show', 'edit' => 'customers.edit', 'update' => 'customers.update', 'destroy' => 'customers.destroy']);

        Route::resource('packages', PackageController::class);
        Route::resource('areas', AreaController::class);

        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
        Route::post('invoices/{invoice}/add-payment', [InvoiceController::class, 'addManualPayment'])->name('invoices.add-payment');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

        Route::resource('payments', PaymentController::class);
        Route::post('payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');

        Route::get('/api/finance-dashboard', [FinanceDashboardController::class, 'dashboard'])->name('api.finance-dashboard');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export/{type}', [ReportController::class, 'handleExport'])->name('export');
        });

        Route::get('/profile', [ProfileController::class, 'show'])->name('tenant.profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('tenant.profile.password');
    });
});

// Customer Portal — tenant subdomain
Route::domain('{tenant_slug}.' . $tenantDomain)->middleware('tenant.subdomain')->group(function () {
    Route::prefix('portal')->name('customer.auth.')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [CustomerAuthController::class, 'login'])->name('authenticate')->middleware('throttle:10,1');
        Route::get('/auth/{token}', [CustomerAuthController::class, 'magicLogin'])->name('auth')->middleware('throttle:10,1');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });

    Route::prefix('portal')->name('customer.portal.')->middleware('auth:customer')->group(function () {
        Route::get('/profile', [CustomerPortalController::class, 'profile'])->name('profile');
        Route::put('/profile', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{id}', [CustomerPortalController::class, 'showInvoice'])->name('invoices.show');
        Route::get('/invoices/{id}/download', [CustomerPortalController::class, 'downloadInvoice'])->name('invoices.download');
        Route::post('/invoices/{id}/pay', [CustomerPortalController::class, 'payInvoice'])->name('invoices.pay');
        Route::post('/invoices/{id}/pay/manual', [CustomerPortalController::class, 'submitManualPayment'])->name('invoices.pay.manual');
        Route::post('/invoices/{id}/pay/qris', [CustomerPortalController::class, 'payQris'])->name('invoices.pay.qris');
        Route::get('/invoices/{id}/pay/qris', [CustomerPortalController::class, 'showQris'])->name('invoices.pay.qris.show');
        Route::post('/invoices/{id}/pay/qris/check', [CustomerPortalController::class, 'checkQris'])->name('invoices.pay.qris.check');
        Route::get('/payments', [CustomerPortalController::class, 'payments'])->name('payments');
    });
});
