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
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\Admin\TenantSubscriptionController;

$mainDomain = config('app.main_domain');
$tenantDomain = config('app.tenant_domain');
$host = request()->getHost();
$isSubdomainCapable = !str_contains($host, 'trycloudflare.com')
    && !str_contains($host, 'ngrok')
    && !str_contains($host, 'pinggy')
    && !str_contains($host, 'loca.lt')
    && !filter_var($host, FILTER_VALIDATE_IP);

Route::group([], function () {
    Route::get('/', fn() => view('landing'))->name('dashboard');
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

    Route::get('/register/tenant', fn() => view('register'))->name('tenant.register.form');
    Route::post('/register', [AuthController::class, 'registerTenant'])->name('tenant.register.store')->middleware('throttle:3,1');
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

$tenantManagementRoutes = function () {
    Route::resource('customers', CustomerController::class)
        ->names(['index' => 'customers.index', 'create' => 'customers.create', 'store' => 'customers.store', 'show' => 'customers.show', 'edit' => 'customers.edit', 'update' => 'customers.update', 'destroy' => 'customers.destroy']);
    Route::post('customers/{customer}/send-portal-link', [CustomerController::class, 'sendPortalLink'])->name('customers.send-portal-link');
    Route::post('customers/{customer}/send-welcome', [CustomerController::class, 'sendWelcome'])->name('customers.send-welcome');
    Route::post('customers/{customer}/pppoe/enable', [CustomerController::class, 'enablePppoe'])->name('customers.pppoe.enable');
    Route::post('customers/{customer}/pppoe/disable', [CustomerController::class, 'disablePppoe'])->name('customers.pppoe.disable');
    Route::post('customers/{customer}/pppoe/reset-password', [CustomerController::class, 'resetPppoePassword'])->name('customers.pppoe.reset-password');
    Route::post('customers/{customer}/pppoe/delete', [CustomerController::class, 'deletePppoe'])->name('customers.pppoe.delete');

    Route::resource('packages', PackageController::class);
    Route::resource('areas', AreaController::class);

    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
    Route::post('invoices/{invoice}/add-payment', [InvoiceController::class, 'addManualPayment'])->name('invoices.add-payment');
    Route::post('invoices/{invoice}/send-whatsapp', [InvoiceController::class, 'sendWhatsapp'])->name('invoices.send-whatsapp');
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

    Route::resource('partners', PartnerController::class);
    Route::resource('tickets', TicketController::class);
    Route::put('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');
    Route::post('/import/sync-beatcom', [ImportController::class, 'syncBeatcom'])->name('import.sync-beatcom');
    Route::get('/import/template', [ImportController::class, 'downloadTemplate'])->name('import.template');
    Route::get('/export/data', [ImportController::class, 'exportData'])->name('export.data');

    // Router MikroTik
    Route::resource('routers', \App\Http\Controllers\RouterController::class);
    Route::post('routers/{router}/test', [\App\Http\Controllers\RouterController::class, 'testConnection'])->name('routers.test');
    Route::post('routers/{router}/sync-secrets', [\App\Http\Controllers\RouterController::class, 'syncSecrets'])->name('routers.sync-secrets');

    // Tenant Settings
    Route::get('/settings', [\App\Http\Controllers\TenantSettingController::class, 'edit'])->name('tenant.settings.edit');
    Route::put('/settings', [\App\Http\Controllers\TenantSettingController::class, 'update'])->name('tenant.settings.update');
    Route::post('/settings/test-whatsapp', [\App\Http\Controllers\TenantSettingController::class, 'testWhatsapp'])->name('tenant.settings.test-whatsapp');
    Route::get('/settings/whatsapp-status', [\App\Http\Controllers\TenantSettingController::class, 'checkWhatsappStatus'])->name('tenant.settings.whatsapp-status');

    Route::get('/profile', [ProfileController::class, 'show'])->name('tenant.profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('tenant.profile.password');
};

if ($isSubdomainCapable) {
    Route::domain('{tenant_slug}.' . $tenantDomain)->middleware('tenant.subdomain')->group(function () use ($tenantManagementRoutes) {
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

        Route::middleware(['auth', 'tenant'])->group($tenantManagementRoutes);
    });
} else {
    // Single-domain / tunnel mode: direct tenant management on current host
    Route::middleware(['auth', 'tenant'])->group(function () use ($tenantManagementRoutes) {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        $tenantManagementRoutes();
    });
}

// Admin routes (Platform Super Admin only)
Route::middleware(['auth', 'platform.superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Tenants
    Route::resource('tenants', \App\Http\Controllers\Admin\TenantController::class);
    Route::post('tenants/{tenant}/toggle-status', [\App\Http\Controllers\Admin\TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

    // Subscription Plans
    Route::resource('plans', \App\Http\Controllers\Admin\PlanController::class);

    // Tenant Subscriptions
    Route::resource('subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class);
    Route::post('subscriptions/{subscription}/extend', [\App\Http\Controllers\Admin\SubscriptionController::class, 'extend'])->name('subscriptions.extend');
    Route::put('/tenant-subscriptions/{tenantSubscription}', [TenantSubscriptionController::class, 'update'])->name('tenant-subscriptions.update');

    // Platform Reports
    Route::get('reports', [\App\Http\Controllers\Admin\PlatformReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [\App\Http\Controllers\Admin\PlatformReportController::class, 'export'])->name('reports.export');
});

// Customer Portal
$customerPortalRoutes = function () {
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
};

if ($isSubdomainCapable) {
    Route::domain('{tenant_slug}.' . $tenantDomain)->middleware('tenant.subdomain')->group($customerPortalRoutes);
} else {
    Route::group([], $customerPortalRoutes);
}
