<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            CreateSuperAdminTenantSeeder::class,
            PackageSeeder::class,
            CustomerSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
