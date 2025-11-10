<?php

namespace NckRtl\HttpManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NckRtl\HttpManager\Commands\HttpManagerCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_http_manager_table')
            ->hasCommand(HttpManagerCommand::class);
    }
}
