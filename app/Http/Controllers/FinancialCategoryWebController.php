<?php

namespace App\Http\Controllers;

class FinancialCategoryWebController extends OperationalCrudWebController
{
    protected function module(): string
    {
        return 'finance.categories';
    }
}
