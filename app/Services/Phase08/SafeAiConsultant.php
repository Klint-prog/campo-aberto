<?php

namespace App\Services\Phase08;

use App\Models\AiConsultation;
use App\Models\AiRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SafeAiConsultant
{
    public const USE_CASES = [
        'assistente_gerencial',
        'resumo_diario',
        'analise_produtividade',
        'previsao_financeira',
        'analise_pecuaria',
        'recomendacao_climatica',
        'consulta_linguagem_natural',
        'deteccao_anomalias',
    ];

    public const DISCLAIMER = [
        'IA é apoio à decisão.',
        'IA não substitui agrônomo, veterinário, contador ou responsável técnico.',
        'IA não executa alterações críticas sem confirmação explícita.',
        'Consulte o responsável técnico antes de decisões agronômicas, veterinárias, contábeis ou operacionais críticas.',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function consult(array $scope, string $useCase, string $question, array $context, Request $request): AiRecommendation
    {
        if (! in_array($useCase, self::USE_CASES, true)) {
            $useCase = 'consulta_linguagem_natural';
        }

        $critical = $this->looksCritical($question, $context);

        $consultation = AiConsultation::create([
            'tenant_id' => $scope['tenant_id'],
            'farm_id' => $scope['farm_id'] ?? null,
            'user_id' => $scope['user_id'] ?? null,
            'use_case' => $useCase,
            'question' => $question,
            'context' => $this->sanitizeContext($context, $scope),
            'status' => 'answered',
            'consulted_at' => now(),
        ]);

        $recommendation = AiRecommendation::create([
            'ai_consultation_id' => $consultation->id,
            'tenant_id' => $scope['tenant_id'],
            'farm_id' => $scope['farm_id'] ?? null,
            'user_id' => $scope['user_id'] ?? null,
            'recommendation' => $this->buildRecommendation($useCase, $question, $critical),
            'risk_level' => $critical ? 'critical' : 'informational',
            'critical_action' => $critical,
            'requires_confirmation' => $critical,
            'safety_disclaimer' => self::DISCLAIMER,
            'recommended_at' => now(),
        ]);

        $this->auditLogger->record(
            'ai.consultation.created',
            $scope,
            [
                'use_case' => $useCase,
                'critical_action' => $critical,
                'ai_consultation_id' => $consultation->id,
                'ai_recommendation_id' => $recommendation->id,
            ],
            $request,
            AiConsultation::class,
            $consultation->id,
        );

        return $recommendation->load('consultation');
    }

    private function buildRecommendation(string $useCase, string $question, bool $critical): string
    {
        $base = 'Análise consultiva gerada para '.$useCase.': revise os indicadores disponíveis, compare com histórico da fazenda e valide com o responsável técnico antes de executar mudanças.';

        if ($critical) {
            return $base.' A solicitação menciona possível ação crítica; o sistema bloqueou execução automática e exige confirmação humana registrada.';
        }

        return $base.' Pergunta recebida: "'.Str::limit($question, 180).'".';
    }

    private function looksCritical(string $question, array $context): bool
    {
        $text = mb_strtolower($question.' '.json_encode($context, JSON_UNESCAPED_UNICODE));

        foreach (['aplicar', 'medicar', 'vacinar', 'vender', 'comprar', 'pagar', 'transferir', 'deletar', 'excluir', 'executar', 'matar', 'sacrificar'] as $term) {
            if (str_contains($text, $term)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeContext(array $context, array $scope): array
    {
        $context['tenant_id'] = $scope['tenant_id'];
        $context['farm_id'] = $scope['farm_id'] ?? null;

        unset($context['api_key'], $context['password'], $context['secret']);

        return $context;
    }
}
