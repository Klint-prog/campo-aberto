<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    protected $fillable = [
        'tenant_id',
        'farm_id',
        'user_id',
        'report_key',
        'format',
        'filters',
        'status',
        'file_path',
        'row_count',
        'generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'generated_at' => 'datetime',
    ];
}
