<?php

namespace NckRtl\HttpManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class HttpManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('http-manager')
            ->hasConfigFile('http_manager')
            ->hasMigrations([
                'create_http_providers_table',
                'create_http_credentials_table',
                'create_http_endpoints_table',
                'create_http_endpoint_configurations_table',
            ]);
    }

    public function packageBooted(): void
    {
        // Publish team migrations separately
        $this->publishes([
            __DIR__.'/../database/migrations/teams' => database_path('migrations'),
        ], 'http-manager-teams-migrations');
    }
}
