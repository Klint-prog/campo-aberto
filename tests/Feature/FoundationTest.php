<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_health_route_starts(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
    }

    public function test_postgis_extensions_are_available(): void
    {
        $extensions = collect(DB::select("SELECT extname FROM pg_extension WHERE extname IN ('postgis', 'pg_trgm', 'pgcrypto')"))
            ->pluck('extname')
            ->all();

        $this->assertContains('postgis', $extensions);
        $this->assertContains('pg_trgm', $extensions);
        $this->assertContains('pgcrypto', $extensions);
    }

    public function test_foundation_seeder_creates_initial_tenant_and_admin_user(): void
    {
        $this->seed();

        $this->assertDatabaseHas('tenants', [
            'slug' => 'campo-aberto-demo',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@campoaberto.local',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('farms', [
            'code' => 'DEMO-001',
            'state' => 'PE',
        ]);
    }
}
