<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Teams Support
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenant team scoping for providers, credentials, endpoints,
    | and endpoint configurations. When enabled, you must publish the team
    | migrations using: php artisan vendor:publish --tag=httpmanager-teams-migrations
    |
    */
    'teams' => [
        'enabled' => env('HTTPMANAGER_TEAMS_ENABLED', false),

        // The model used for teams in your application
        'team_model' => env('HTTPMANAGER_TEAM_MODEL', 'App\\Models\\Team'),

        // The foreign key column name
        'team_foreign_key' => env('HTTPMANAGER_TEAM_FOREIGN_KEY', 'team_id'),
    ],
];
