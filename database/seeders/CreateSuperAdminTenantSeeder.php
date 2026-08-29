<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreateSuperAdminTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'netsaz-super-admin'],
            [
                'name' => 'NetSaz Super Admin',
                'email' => 'admin@netsaz.local',
                'whatsapp_number' => '081234567890',
                'status' => 'active',
            ]
        );

        // Create super admin user if not exists
        $user = User::firstOrCreate(
            ['email' => 'superadmin@netsaz.local'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'tenant_id' => null,
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}