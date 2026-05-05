<?php

namespace App\Support;

final class LivestockDomainEvent
{
    public const ANIMAL_PURCHASED = 'animal.purchased';
    public const ANIMAL_SOLD = 'animal.sold';
    public const ANIMAL_DIED = 'animal.died';
    public const ANIMAL_WEIGHT_RECORDED = 'animal.weight_recorded';
    public const ANIMAL_VACCINATION_RECORDED = 'animal.vaccination_recorded';
    public const ANIMAL_TREATMENT_RECORDED = 'animal.treatment_recorded';
    public const ANIMAL_FEED_CONSUMED = 'animal.feed_consumed';
}
