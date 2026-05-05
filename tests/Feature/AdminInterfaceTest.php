<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Usuários')
            ->assertSee('Campo Aberto');
    }

    public function test_authenticated_user_can_access_users_index(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/users')
            ->assertOk()
            ->assertSee('Usuários do tenant')
            ->assertSee($user->email);
    }

    public function test_under_construction_page_is_available(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->get('/under-construction/pwa')
            ->assertOk()
            ->assertSee('PWA')
            ->assertSee('Em desenvolvimento');
    }

    private function makeUser(): User
    {
        $tenant = Tenant::query()->create([
            'id' => '44000000-0000-0000-0000-000000000001',
            'name' => 'Tenant Interface',
            'slug' => 'tenant-interface',
            'is_active' => true,
        ]);

        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin@campoaberto.local',
            'is_active' => true,
        ]);
    }
}
