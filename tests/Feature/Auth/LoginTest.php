<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Campo Aberto Tecnologia Rural')
            ->assertSee('Acessar plataforma');
    }

    public function test_admin_can_login_and_is_redirected_to_dashboard(): void
    {
        $tenant = Tenant::query()->create([
            'id' => '33000000-0000-0000-0000-000000000001',
            'name' => 'Tenant Login',
            'slug' => 'tenant-login',
            'is_active' => true,
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-login'),
        ]);

        $this->withSession(['_token' => 'test-token'])
            ->post('/login', [
                '_token' => 'test-token',
                'email' => 'admin@example.test',
                'password' => 'secret-login',
            ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }
}
