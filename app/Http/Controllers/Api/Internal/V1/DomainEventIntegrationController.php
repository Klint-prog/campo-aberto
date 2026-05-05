<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\DomainEvent;
use App\Services\EventHandlers\ProcessDomainEventIntegrations;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainEventIntegrationController extends Controller
{
    public function store(Request $request, DomainEvent $domainEvent, ProcessDomainEventIntegrations $processor): JsonResponse
    {
        abort_unless($domainEvent->tenant_id === $request->user()->tenant_id, 404);

        $results = $processor->process($domainEvent);

        return response()->json([
            'success' => true,
            'processed' => $results->count(),
            'data' => $results,
        ]);
    }
}
