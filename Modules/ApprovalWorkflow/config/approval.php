<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Workflow Type
    |--------------------------------------------------------------------------
    |
    | Default workflow type if not specified
    | Options: 'single', 'two_level', 'three_level'
    |
    */
    'default_workflow_type' => 'two_level',

    /*
    |--------------------------------------------------------------------------
    | Workflow Levels Configuration
    |--------------------------------------------------------------------------
    |
    | Define workflow levels and their properties
    |
    */
    'workflow_levels' => [
        'single' => [
            'steps' => [
                'pending' => ['label' => 'Chờ duyệt', 'next' => 'approved'],
                'approved' => ['label' => 'Đã phê duyệt', 'next' => null],
                'rejected' => ['label' => 'Đã từ chối', 'next' => null],
            ]
        ],
        'two_level' => [
            'steps' => [
                'pending' => ['label' => 'Chờ duyệt', 'next' => 'level1_approved'],
                'level1_approved' => ['label' => 'Cấp 1 đã duyệt', 'next' => 'approved'],
                'approved' => ['label' => 'Đã phê duyệt', 'next' => null],
                'rejected' => ['label' => 'Đã từ chối', 'next' => null],
            ]
        ],
        'three_level' => [
            'steps' => [
                'pending' => ['label' => 'Chờ duyệt', 'next' => 'level1_approved'],
                'level1_approved' => ['label' => 'Cấp 1 đã duyệt', 'next' => 'level2_approved'],
                'level2_approved' => ['label' => 'Cấp 2 đã duyệt', 'next' => 'approved'],
                'approved' => ['label' => 'Đã phê duyệt', 'next' => null],
                'rejected' => ['label' => 'Đã từ chối', 'next' => null],
            ]
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Digital Signature Settings
    |--------------------------------------------------------------------------
    */
    'digital_signature' => [
        'enabled' => true,
        'require_pin' => true,
        'certificate_password' => env('CERTIFICATE_PASSWORD', 'A31Factory2025'),
        'default_certificate_path' => storage_path('app/certificates/a31_factory.pfx'),
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Generation Settings
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'engine' => 'tcpdf', // tcpdf or dompdf
        'paper' => 'A4',
        'orientation' => 'portrait',
        'default_storage_disk' => 'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Permissions
    |--------------------------------------------------------------------------
    |
    | Define permission patterns for approval actions
    |
    */
    'permissions' => [
        'approve_pattern' => '{module}.approve',
        'reject_pattern' => '{module}.reject',
        'view_pattern' => '{module}.view',
    ],
];


