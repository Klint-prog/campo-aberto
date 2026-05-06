<?php

namespace App\Http\Controllers;

class FarmWebController extends MasterDataWebController
{
    protected function module(): string
    {
        return 'farms';
    }
}
