<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_root_to_login(): void
    {
        $this->get('/')
            ->assertRedirect('/login');
    }

    public function test_authenticated_user_is_redirected_from_root_to_dashboard(): void
    {
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Tenant Home',
            'slug' => 'tenant-home',
            'is_active' => true,
        ]);

        $user = User::factory()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect('/dashboard');
    }
}
