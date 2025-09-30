<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PDF Digital Signature Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PDF digital signing using lsnepomuceno/laravel-a1-pdf-sign
    |
    */

    'certificate_path' => env('PDF_CERTIFICATE_PATH', storage_path('app/certificates/a31_factory.pfx')),
    'certificate_password' => env('PDF_CERTIFICATE_PASSWORD', 'A31Factory2025'),
    
    /*
    |--------------------------------------------------------------------------
    | Signature Appearance
    |--------------------------------------------------------------------------
    */
    'signature_appearance' => [
        'reason' => 'Document approval',
        'location' => 'A31 Factory',
        'contact_info' => 'admin@a31factory.com',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Visual Signature Settings
    |--------------------------------------------------------------------------
    */
    'visual_signature' => [
        'x' => 400,        // X position
        'y' => 150,        // Y position  
        'width' => 100,    // Width
        'height' => 50,    // Height
        'opacity' => 0.8,  // Image opacity
        'page' => 1,       // Page number (1-based)
    ],
];
