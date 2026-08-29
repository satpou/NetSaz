<?php

namespace Tests\Feature;

use App\Livewire\SystemHealth;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SystemHealthRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_health_component_renders(): void
    {
        $this->seed(RoleSeeder::class);
        $tenant = Tenant::factory()->create(['status' => 'active']);
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'super_admin',
            'is_active' => true,
        ]);
        $user->assignRole('super_admin');

        $this->withSession(['active_tenant_id' => $tenant->id]);

        Livewire::actingAs($user)
            ->test(SystemHealth::class)
            ->assertStatus(200);
    }
}
