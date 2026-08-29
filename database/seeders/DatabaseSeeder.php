<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CreateSuperAdminTenantSeeder::class,
            PackageSeeder::class,
            CustomerSeeder::class,
            TestUserSeeder::class,
        ]);
    }
}
