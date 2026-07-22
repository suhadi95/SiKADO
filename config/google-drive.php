<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google Drive Service Account
    |--------------------------------------------------------------------------
    |
    | Path to the Service Account JSON file. Defaults to storage/app/google/.
    |
    */

    'service_account_path' => env(
        'GOOGLE_DRIVE_SERVICE_ACCOUNT_PATH',
        storage_path('app/google/service-account.json')
    ),

    /*
    |--------------------------------------------------------------------------
    | Google Drive OAuth (required for file upload on personal Gmail)
    |--------------------------------------------------------------------------
    */

    'oauth_client_id' => env('GOOGLE_DRIVE_OAUTH_CLIENT_ID'),

    'oauth_client_secret' => env('GOOGLE_DRIVE_OAUTH_CLIENT_SECRET'),

    'oauth_refresh_token' => env('GOOGLE_DRIVE_OAUTH_REFRESH_TOKEN'),

];
