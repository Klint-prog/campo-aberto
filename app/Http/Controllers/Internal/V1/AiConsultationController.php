<?php

namespace App\Http\Controllers\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\AiRecommendation;
use App\Services\Phase08\AccessScope;
use App\Services\Phase08\AuditLogger;
use App\Services\Phase08\SafeAiConsultant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AiConsultationController extends Controller
{
    public function __construct(
        private readonly AccessScope $accessScope,
        private readonly SafeAiConsultant $consultant,
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $scope = $this->accessScope->fromRequest($request);
        $this->accessScope->assertPermission($scope, 'ai.consult');

        $validated = $request->validate([
            'use_case' => ['nullable', 'string', 'max:80'],
            'question' => ['required', 'string', 'max:4000'],
            'context' => ['nullable', 'array'],
        ]);

        $recommendation = $this->consultant->consult(
            $scope,
            $validated['use_case'] ?? 'consulta_linguagem_natural',
            $validated['question'],
            $validated['context'] ?? [],
            $request,
        );

        return response()->json([
            'consultation' => $recommendation->consultation,
            'recommendation' => $recommendation,
            'safety' => SafeAiConsultant::DISCLAIMER,
        ], 201);
    }

    public function confirm(Request $request, AiRecommendation $recommendation): JsonResponse
    {
        $scope = $this->accessScope->fromRequest($request);
        $this->accessScope->assertPermission($scope, 'ai.confirm_critical_action');

        if ((int) $recommendation->tenant_id !== $scope['tenant_id']) {
            abort(403, 'Recomendação pertence a outro tenant.');
        }

        if (($scope['farm_id'] ?? null) && (int) $recommendation->farm_id !== (int) $scope['farm_id']) {
            abort(403, 'Recomendação pertence a outra fazenda.');
        }

        if (! $recommendation->critical_action || ! $recommendation->requires_confirmation) {
            throw new BadRequestHttpException('Esta recomendação não exige confirmação crítica.');
        }

        $validated = $request->validate([
            'action_key' => ['required', 'string', 'max:120'],
            'confirmation_note' => ['required', 'string', 'max:2000'],
        ]);

        $confirmationId = DB::table('ai_action_confirmations')->insertGetId([
            'ai_recommendation_id' => $recommendation->id,
            'tenant_id' => $scope['tenant_id'],
            'farm_id' => $scope['farm_id'] ?? $recommendation->farm_id,
            'user_id' => $scope['user_id'],
            'action_key' => $validated['action_key'],
            'confirmation_note' => $validated['confirmation_note'],
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->auditLogger->record('ai.critical_action.confirmed', $scope, [
            'ai_recommendation_id' => $recommendation->id,
            'ai_action_confirmation_id' => $confirmationId,
            'action_key' => $validated['action_key'],
        ], $request, AiRecommendation::class, $recommendation->id);

        return response()->json([
            'confirmation_id' => $confirmationId,
            'message' => 'Confirmação registrada. A execução operacional deve continuar no módulo responsável e manter auditoria própria.',
        ], 201);
    }
}
