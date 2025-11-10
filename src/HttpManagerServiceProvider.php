<?php

namespace NckRtl\HttpManager;

use NckRtl\HttpManager\Services\ConfigurationValidator;
use NckRtl\HttpManager\Services\CredentialValidator;
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
            ->hasConfigFile('http-manager')
            ->hasMigrations([
                'create_http_providers_table',
                'create_http_credentials_table',
                'create_http_endpoints_table',
                'create_http_endpoint_configurations_table',
            ]);
    }

    public function packageRegistering(): void
    {
        // Register validator services as singletons
        $this->app->singleton(CredentialValidator::class);
        $this->app->singleton(ConfigurationValidator::class);
    }

    public function packageBooted(): void
    {
        // Publish team migrations separately
        $this->publishes([
            __DIR__.'/../database/migrations/teams' => database_path('migrations'),
        ], 'http-manager-teams-migrations');
    }
}
