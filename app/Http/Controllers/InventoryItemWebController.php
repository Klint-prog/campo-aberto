<?php

namespace App\Http\Controllers;

class InventoryItemWebController extends OperationalCrudWebController
{
    protected function module(): string
    {
        return 'inventory';
    }
}
