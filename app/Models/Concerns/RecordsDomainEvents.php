<?php

namespace App\Models\Concerns;

use App\Models\DomainEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

trait RecordsDomainEvents
{
    public function recordDomainEvent(string $eventName, array $payload = []): DomainEvent
    {
        /** @var Model $this */
        return DomainEvent::create([
            'tenant_id' => $this->getAttribute('tenant_id'),
            'farm_id' => $this->getAttribute('farm_id'),
            'event_name' => $eventName,
            'aggregate_type' => static::class,
            'aggregate_id' => $this->getKey(),
            'payload' => $payload,
            'occurred_at' => Carbon::now(),
        ]);
    }
}
