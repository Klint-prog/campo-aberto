<?php

namespace Tests\Feature;

use App\Models\AiRecommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase08ReportingBiAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_filters_by_tenant_and_farm(): void
    {
        $this->postJson('/api/internal/v1/reports/atividades/export', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'user_id' => 1,
            'permissions' => 'reports.export',
            'allowed_farm_ids' => '100',
            'format' => 'csv',
        ])->assertCreated();

        $this->postJson('/api/internal/v1/reports/atividades/export', [
            'tenant_id' => 20,
            'farm_id' => 200,
            'user_id' => 2,
            'permissions' => 'reports.export',
            'allowed_farm_ids' => '200',
            'format' => 'csv',
        ])->assertCreated();

        $response = $this->getJson('/api/internal/v1/reports/atividades?tenant_id=10&farm_id=100&permissions=reports.view&allowed_farm_ids=100');

        $response->assertOk()
            ->assertJsonPath('filters.tenant_id', 10)
            ->assertJsonPath('filters.farm_id', 100);

        $this->assertDatabaseHas('report_exports', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'report_key' => 'atividades',
        ]);
    }

    public function test_user_without_permission_does_not_export_report(): void
    {
        $this->postJson('/api/internal/v1/reports/financeiro/export', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'user_id' => 1,
            'permissions' => 'reports.view',
            'allowed_farm_ids' => '100',
            'format' => 'pdf',
        ])->assertForbidden();

        $this->assertDatabaseMissing('report_exports', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'report_key' => 'financeiro',
        ]);
    }

    public function test_ai_does_not_access_unauthorized_farm(): void
    {
        $this->postJson('/api/internal/v1/ai/consultations', [
            'tenant_id' => 10,
            'farm_id' => 999,
            'user_id' => 1,
            'permissions' => 'ai.consult',
            'allowed_farm_ids' => '100',
            'question' => 'Resuma a produtividade desta fazenda.',
        ])->assertForbidden();

        $this->assertDatabaseCount('ai_consultations', 0);
    }

    public function test_ai_consultation_generates_audit_log(): void
    {
        $this->postJson('/api/internal/v1/ai/consultations', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'user_id' => 1,
            'permissions' => 'ai.consult',
            'allowed_farm_ids' => '100',
            'use_case' => 'analise_produtividade',
            'question' => 'Analise a produtividade da safra atual.',
        ])->assertCreated()
            ->assertJsonPath('recommendation.critical_action', false)
            ->assertJsonPath('recommendation.requires_confirmation', false);

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'event' => 'ai.consultation.created',
        ]);
    }

    public function test_critical_ai_action_requires_confirmation(): void
    {
        $response = $this->postJson('/api/internal/v1/ai/consultations', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'user_id' => 1,
            'permissions' => 'ai.consult',
            'allowed_farm_ids' => '100',
            'use_case' => 'analise_pecuaria',
            'question' => 'Aplicar medicamento no lote doente agora.',
        ])->assertCreated()
            ->assertJsonPath('recommendation.critical_action', true)
            ->assertJsonPath('recommendation.requires_confirmation', true);

        $recommendationId = $response->json('recommendation.id');
        $recommendation = AiRecommendation::findOrFail($recommendationId);

        $this->assertTrue($recommendation->critical_action);
        $this->assertTrue($recommendation->requires_confirmation);

        $this->postJson('/api/internal/v1/ai/recommendations/'.$recommendationId.'/confirm', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'user_id' => 1,
            'permissions' => 'ai.confirm_critical_action',
            'allowed_farm_ids' => '100',
            'action_key' => 'animal_treatment_manual_review',
            'confirmation_note' => 'Responsável técnico revisou e aprovou seguir para o módulo sanitário.',
        ])->assertCreated();

        $this->assertDatabaseHas('audit_logs', [
            'tenant_id' => 10,
            'farm_id' => 100,
            'event' => 'ai.critical_action.confirmed',
        ]);
    }
}
