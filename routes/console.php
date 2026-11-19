<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily invoice generation at midnight
Schedule::command('invoices:generate-monthly')
    ->dailyAt('00:05')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/invoice-generation.log'));

// Daily overdue check at 01:00
Schedule::command('invoices:mark-overdue')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/overdue-check.log'));

// Daily subscription status check at 02:00
Schedule::command('subscriptions:check-status')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/subscription-check.log'));

// Auto-sync code from GitHub repository every 2 minutes
Schedule::command('git:sync')
    ->everyTwoMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/git-sync.log'));

// Auto-isolate at 03:00
Schedule::command('auto-isolate:run')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/auto-isolate.log'));

// Invoice reminders via WhatsApp (H-3 & H-1) at 09:00
Schedule::command('invoices:send-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/invoice-reminders.log'));

// Auto-prune trashed tenants older than 30 days
Artisan::command('tenants:prune-trashed', function () {
    $cutoff = now()->subDays(30);
    $trashedTenants = \App\Models\Tenant::onlyTrashed()
        ->where('deleted_at', '<=', $cutoff)
        ->get();

    $count = $trashedTenants->count();
    foreach ($trashedTenants as $tenant) {
        \App\Http\Controllers\Admin\TenantController::purgeTenantData($tenant);
    }

    $this->info("Berhasil membersihkan {$count} tenant dari kotak sampah (kedaluwarsa > 30 hari).");
})->purpose('Hapus permanen tenant di kotak sampah yang telah melewati masa simpan 30 hari');

Schedule::command('tenants:prune-trashed')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/tenant-prune.log'));
