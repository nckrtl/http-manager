# HTTP Manager for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nckrtl/http-manager.svg?style=flat-square)](https://packagist.org/packages/nckrtl/http-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/http-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nckrtl/http-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nckrtl/http-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nckrtl/http-manager/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nckrtl/http-manager.svg?style=flat-square)](https://packagist.org/packages/nckrtl/http-manager)

A Laravel package for managing HTTP API integrations with database-driven configuration. Store API credentials, endpoints, and configurations securely in your database with support for multi-tenancy.

## Features

- 🔐 **Database-Driven Configuration**: Store API providers, credentials, and endpoints in your database
- 🔒 **Secure Credential Storage**: Automatic encryption for sensitive data
- ✅ **Validation**: Built-in validation for credentials and endpoint parameters
- 👥 **Multi-Tenancy**: Optional team scoping for multi-tenant applications
- 🛡️ **Type-Safe DTOs**: Leverage Spatie Laravel Data for type-safe data transfer objects
- 🔑 **Flexible Authentication**: Extensible authentication system (currently supports API Key)

## Installation

You can install the package via composer:

```bash
composer require nckrtl/http-manager
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="http-manager-migrations"
php artisan migrate
```

Optionally, publish the config file:

```bash
php artisan vendor:publish --tag="http-manager-config"
```

## Basic Usage

### 1. Create an HTTP Provider

```php
use NckRtl\HttpManager\Models\HttpProvider;

$provider = HttpProvider::create([
    'name' => 'GitHub API',
    'base_url' => 'https://api.github.com',
    'credential_config' => [
        'headers' => [
            'Authorization' => 'token {{api_key}}',
            'Accept' => 'application/vnd.github.v3+json',
        ],
    ],
]);
```

### 2. Create Credentials

```php
use NckRtl\HttpManager\Models\HttpCredential;

$credential = HttpCredential::create([
    'name' => 'GitHub Personal Token',
    'http_provider_id' => $provider->id,
    'config' => [
        'api_key' => 'ghp_your_token_here',
    ],
]);
```

### 3. Define an Endpoint

```php
use NckRtl\HttpManager\Models\HttpEndpoint;

$endpoint = HttpEndpoint::create([
    'name' => 'Get User',
    'http_provider_id' => $provider->id,
    'method' => 'GET',
    'endpoint' => '/users/{username}',
    'options' => [
        'url_params' => [
            'username' => [
                'type' => 'string',
                'required' => true,
                'description' => 'GitHub username',
            ],
        ],
    ],
]);
```

### 4. Create an Endpoint Configuration

```php
use NckRtl\HttpManager\Models\HttpEndpointConfiguration;

$configuration = HttpEndpointConfiguration::create([
    'name' => 'Get Octocat User',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $credential->id,
    'configuration' => [
        'url_params' => [
            'username' => 'octocat',
        ],
    ],
]);
```

### 5. Execute the Request

```php
use NckRtl\HttpManager\Services\HttpManager;

$httpManager = app(HttpManager::class);
$response = $httpManager->execute($configuration->id);

$userData = $response->json();
```

## Advanced Usage

### Working with Query Parameters

```php
$endpoint = HttpEndpoint::create([
    'name' => 'Search Repositories',
    'http_provider_id' => $provider->id,
    'method' => 'GET',
    'endpoint' => '/search/repositories',
    'options' => [
        'query_params' => [
            'q' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Search query',
            ],
            'per_page' => [
                'type' => 'integer',
                'required' => false,
                'default' => 30,
            ],
        ],
    ],
]);

$configuration = HttpEndpointConfiguration::create([
    'name' => 'Search Laravel Repos',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $credential->id,
    'configuration' => [
        'query_params' => [
            'q' => 'laravel',
            'per_page' => 10,
        ],
    ],
]);
```

### Working with Request Body

```php
$endpoint = HttpEndpoint::create([
    'name' => 'Create Issue',
    'http_provider_id' => $provider->id,
    'method' => 'POST',
    'endpoint' => '/repos/{owner}/{repo}/issues',
    'options' => [
        'url_params' => [
            'owner' => ['type' => 'string', 'required' => true],
            'repo' => ['type' => 'string', 'required' => true],
        ],
        'body' => [
            'title' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Issue title',
            ],
            'body' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Issue description',
            ],
        ],
    ],
]);

$configuration = HttpEndpointConfiguration::create([
    'name' => 'Create Bug Report',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $credential->id,
    'configuration' => [
        'url_params' => [
            'owner' => 'laravel',
            'repo' => 'framework',
        ],
        'body' => [
            'title' => 'Bug: Something is broken',
            'body' => 'Description of the bug...',
        ],
    ],
]);
```

### Direct Execution with Configuration Object

```php
$httpManager = app(HttpManager::class);
$response = $httpManager->executeWithConfiguration($configuration);
```

## Multi-Tenancy Support

Enable team scoping for multi-tenant applications:

### 1. Enable Teams in Config

```php
// config/http-manager.php
return [
    'teams' => [
        'enabled' => true,
        'team_model' => 'App\\Models\\Team',
        'team_foreign_key' => 'team_id',
    ],
];
```

Or via environment:

```env
HTTPMANAGER_TEAMS_ENABLED=true
HTTPMANAGER_TEAM_MODEL="App\Models\Team"
```

### 2. Publish and Run Team Migrations

```bash
php artisan vendor:publish --tag="http-manager-teams-migrations"
php artisan migrate
```

### 3. Configure Team Resolver (Optional)

```php
// config/http-manager.php
return [
    'teams' => [
        'enabled' => true,
        'team_resolver' => function () {
            return auth()->user()?->current_team_id;
        },
    ],
];
```

### 4. Use Team-Scoped Queries

```php
// Automatically filtered by current team
$providers = HttpProvider::all();

// Query without team scope
$allProviders = HttpProvider::withoutTeamScope()->get();

// Query for specific team
$teamProviders = HttpProvider::forTeam(5)->get();

// Query for all teams
$globalProviders = HttpProvider::forAllTeams()->get();
```

## Validation

The package includes two validators:

### Credential Validator

Validates that credentials contain all required placeholder values:

```php
use NckRtl\HttpManager\Services\CredentialValidator;

$validator = app(CredentialValidator::class);
$validator->validate($provider, $credentialConfig);
// Throws ValidationException if credential is missing required values
```

### Configuration Validator

Validates endpoint configurations against endpoint options schema:

```php
use NckRtl\HttpManager\Services\ConfigurationValidator;

$validator = app(ConfigurationValidator::class);
$validator->validate($endpoint, $configuration);
// Throws ValidationException if configuration is invalid
```

Validation is automatically performed by `HttpManager` before executing requests.

## Parameter Types

Supported parameter types for endpoint options:

- `string`: String values
- `integer`: Integer values
- `boolean`: Boolean values (true/false)
- `array`: Array values
- `object`: Object/associative array values

## Security

Sensitive configuration data in `HttpEndpointConfiguration` is automatically encrypted using Laravel's `encrypted:array` cast. Make sure your `APP_KEY` is set in your `.env` file.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Claude](https://github.com/nckrtl)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
