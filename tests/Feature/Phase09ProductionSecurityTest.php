<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class Phase09ProductionSecurityTest extends TestCase
{
    public function test_app_debug_is_false_in_production_example(): void
    {
        $example = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $example);
        $this->assertStringContainsString('APP_DEBUG=false', $example);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $example);
        $this->assertStringContainsString('PGADMIN_ENABLED=false', $example);
    }

    public function test_security_headers_are_defined(): void
    {
        $headers = config('phase09.security_headers');

        $this->assertSame('DENY', $headers['X-Frame-Options']);
        $this->assertSame('nosniff', $headers['X-Content-Type-Options']);
        $this->assertArrayHasKey('Strict-Transport-Security', $headers);
        $this->assertArrayHasKey('Permissions-Policy', $headers);
    }

    public function test_security_headers_middleware_sets_expected_headers(): void
    {
        Config::set('phase09.security_headers', [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $middleware = new SecurityHeaders();
        $request = Request::create('/probe', 'GET');

        $response = $middleware->handle($request, fn () => new Response('ok'));

        $this->assertSame('DENY', $response->headers->get('X-Frame-Options'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_lgpd_minimum_flow_is_documented(): void
    {
        $guide = file_get_contents(base_path('docs/production-checklist.md'));

        $this->assertStringContainsString('Fluxo LGPD mínimo', $guide);
        $this->assertStringContainsString('Exportação de dados', $guide);
        $this->assertStringContainsString('Exclusão ou anonimização', $guide);
        $this->assertStringContainsString('Minimização', $guide);
        $this->assertStringContainsString('user.data_exported', $guide);
        $this->assertStringContainsString('user.deleted_or_anonymized', $guide);
    }

    public function test_backup_and_restore_scripts_have_safety_controls(): void
    {
        $backup = file_get_contents(base_path('scripts/backup-postgres.sh'));
        $restore = file_get_contents(base_path('scripts/restore-postgres.sh'));
        $uploads = file_get_contents(base_path('scripts/backup-uploads.sh'));

        $this->assertStringContainsString('pg_dump', $backup);
        $this->assertStringContainsString('sha256sum', $backup);
        $this->assertStringContainsString('BACKUP_RETENTION_DAYS', $backup);
        $this->assertStringContainsString('pg_restore', $restore);
        $this->assertStringContainsString('RESTORE_CONFIRM=I_UNDERSTAND_THIS_REPLACES_DATA', $restore);
        $this->assertStringContainsString('sha256sum -c', $restore);
        $this->assertStringContainsString('tar -czf', $uploads);
    }

    public function test_observability_requirements_are_documented(): void
    {
        $guide = file_get_contents(base_path('docs/production-checklist.md'));

        foreach (['Laravel', 'Nginx', 'PHP-FPM', 'PostgreSQL', 'Worker', 'Scheduler', 'CPU', 'RAM', 'Disco', 'Tempo de resposta', 'Healthcheck'] as $term) {
            $this->assertStringContainsString($term, $guide);
        }
    }
}
