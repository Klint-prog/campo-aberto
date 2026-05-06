<?php

namespace App\Http\Controllers;

class MachineWebController extends OperationalCrudWebController
{
    protected function module(): string
    {
        return 'machines';
    }
}
