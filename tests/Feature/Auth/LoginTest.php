<?php

namespace Tests\Feature\Auth;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);

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

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'secret-login',
        ])->assertRedirect('/users');

        $this->assertAuthenticated();
    }
}
