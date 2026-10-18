<?php

namespace App\Console\Commands;

use Database\Seeders\SubscriptionPlanSeeder;
use Illuminate\Console\Command;

class CreateDefaultPlansCommand extends Command
{
    protected $signature = 'plans:create-default';
    protected $description = 'Create or update the default subscription plans';

    public function handle(): int
    {
        $this->info('Creating default subscription plans...');

        $seeder = new SubscriptionPlanSeeder();
        $seeder->run();

        $this->info('Default subscription plans created successfully.');

        return self::SUCCESS;
    }
}
