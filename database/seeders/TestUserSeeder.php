<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'test-isp'],
            [
                'name' => 'Test ISP',
                'email' => 'admin@test-isp.com',
                'status' => 'active',
            ]
        );

        $launchPlan = SubscriptionPlan::where('code', 'launch')->first()
            ?? SubscriptionPlan::orderBy('sort_order')->first();

        if ($launchPlan && ! TenantSubscription::where('tenant_id', $tenant->id)->whereIn('status', ['active', 'trial'])->exists()) {
            TenantSubscription::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $launchPlan->id,
                'status' => 'active',
                'started_at' => now(),
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
            ]);
        }

        // Platform super admin (no tenant — manages all ISPs)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@netsaz.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'tenant_id' => null,
                'is_active' => true,
            ]
        );

        // super_admin tenant-level (ISP owner)
        $ispOwner = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@isp.com'],
            [
                'name' => 'Admin ISP',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );

        // admin (tenant-level operational)
        $opAdmin = User::firstOrCreate(
            ['email' => 'opadmin@test.com'],
            [
                'name' => 'Operational Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );

        // staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@test.com'],
            [
                'name' => 'Staff Test',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'tenant_id' => $tenant->id,
                'is_active' => true,
            ]
        );

        $this->command->info("Akun test berhasil dibuat:");
        $this->command->info("  Platform Super Admin: superadmin@netsaz.local / password (tanpa tenant — kelola semua ISP)");
        $this->command->info("  ISP Owner: admin@test.com / password (role: super_admin, tenant: Test ISP)");
        $this->command->info("  Admin: opadmin@test.com / password (role: admin)");
        $this->command->info("  Staff: staff@test.com / password (role: staff)");
    }
}
