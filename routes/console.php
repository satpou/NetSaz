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
