<?php

namespace App\Http\Controllers;

class FinancialAccountWebController extends OperationalCrudWebController
{
    protected function module(): string
    {
        return 'finance.accounts';
    }
}
