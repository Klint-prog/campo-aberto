<?php

return [
    'production' => [
        'app_debug' => false,
        'force_https' => true,
        'pgadmin_enabled' => false,
        'backup_retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'healthcheck_token' => env('HEALTHCHECK_TOKEN'),
    ],

    'security_headers' => [
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        'Cross-Origin-Opener-Policy' => 'same-origin',
        'Cross-Origin-Resource-Policy' => 'same-origin',
    ],

    'lgpd' => [
        'legal_basis' => [
            'contract_execution',
            'legitimate_interest',
            'legal_obligation',
            'consent_when_required',
        ],
        'minimum_personal_data' => [
            'name',
            'email',
            'role',
            'tenant_id',
            'farm_id_when_required',
        ],
        'critical_events' => [
            'user.created',
            'user.updated',
            'user.deleted_or_anonymized',
            'user.data_exported',
            'user.role_changed',
            'tenant.access_changed',
            'backup.created',
            'backup.restore_tested',
        ],
    ],

    'observability' => [
        'logs' => [
            'laravel',
            'nginx',
            'php_fpm',
            'postgresql',
            'worker',
            'scheduler',
        ],
        'metrics' => [
            'cpu',
            'ram',
            'disk',
            'response_time',
            'backup_freshness',
        ],
    ],
];
