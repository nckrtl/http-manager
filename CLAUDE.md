# CLAUDE.md - AI Assistant Guide for HTTP Manager Package

This document provides instructions for AI assistants (like Claude) on how to help users work with the HTTP Manager package.

## Package Overview

**HTTP Manager** is a Laravel package for managing HTTP API integrations with database-driven configuration. It stores API providers, credentials, endpoints, and configurations in the database with automatic encryption for sensitive data.

**For human-readable documentation, see [README.md](README.md).**

## Core Concepts

### 1. Data Flow Architecture

```
HttpProvider (API base configuration)
    ↓
HttpCredential (stores API keys/tokens)
    ↓
HttpEndpoint (defines API endpoint structure)
    ↓
HttpEndpointConfiguration (combines endpoint + credential + actual values)
    ↓
HttpManager->execute() (executes the HTTP request)
```

### 2. Models & Their Purpose

- **HttpProvider**: Defines an API provider (base_url, credential template with placeholders)
- **HttpCredential**: Stores actual credential values that fill the placeholders
- **HttpEndpoint**: Defines an endpoint structure (method, path, parameter schema)
- **HttpEndpointConfiguration**: Combines endpoint + credential + parameter values (encrypted storage)

## Common Usage Patterns

### Pattern 1: Creating a Complete API Integration

When a user asks to integrate with an external API, follow these steps:

```php
// Step 1: Create Provider with credential template
$provider = HttpProvider::create([
    'name' => 'Service Name',
    'base_url' => 'https://api.example.com',
    'credential_config' => [
        'headers' => [
            'Authorization' => 'Bearer {{api_key}}',  // Use {{placeholder}} syntax
            'Accept' => 'application/json',
        ],
    ],
]);

// Step 2: Create Credential with actual values
$credential = HttpCredential::create([
    'name' => 'Production API Key',
    'http_provider_id' => $provider->id,
    'config' => [
        'api_key' => 'actual_secret_key_here',  // Matches {{api_key}} placeholder
    ],
]);

// Step 3: Define Endpoint with parameter schema
$endpoint = HttpEndpoint::create([
    'name' => 'Get Resource',
    'http_provider_id' => $provider->id,
    'method' => 'GET',  // GET, POST, PUT, PATCH, DELETE
    'endpoint' => '/api/v1/resources/{id}',  // Use {param} for URL params
    'options' => [
        'url_params' => [
            'id' => [
                'type' => 'string',  // string, integer, boolean, array, object
                'required' => true,
                'description' => 'Resource ID',
            ],
        ],
        'query_params' => [
            'include' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Related resources to include',
            ],
        ],
    ],
]);

// Step 4: Create Configuration with actual parameter values
$configuration = HttpEndpointConfiguration::create([
    'name' => 'Get Resource 123',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $credential->id,
    'configuration' => [
        'url_params' => ['id' => '123'],
        'query_params' => ['include' => 'author,comments'],
    ],
]);

// Step 5: Execute the request
$httpManager = app(\NckRtl\HttpManager\Services\HttpManager::class);
$response = $httpManager->execute($configuration->id);

// Handle response
$data = $response->json();
$statusCode = $response->status();
```

### Pattern 2: POST/PUT Requests with Body

```php
$endpoint = HttpEndpoint::create([
    'name' => 'Create Resource',
    'http_provider_id' => $provider->id,
    'method' => 'POST',
    'endpoint' => '/api/v1/resources',
    'options' => [
        'body' => [
            'title' => [
                'type' => 'string',
                'required' => true,
            ],
            'status' => [
                'type' => 'string',
                'required' => false,
            ],
            'metadata' => [
                'type' => 'object',  // For nested objects
                'required' => false,
            ],
        ],
    ],
]);

$configuration = HttpEndpointConfiguration::create([
    'name' => 'Create New Post',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $credential->id,
    'configuration' => [
        'body' => [
            'title' => 'My New Post',
            'status' => 'published',
            'metadata' => ['tags' => ['laravel', 'php']],
        ],
    ],
]);
```

### Pattern 3: Multiple Credentials for Same Provider

Different environments or users may need different credentials:

```php
// Production credential
$prodCredential = HttpCredential::create([
    'name' => 'Production API Key',
    'http_provider_id' => $provider->id,
    'config' => ['api_key' => 'prod_key_xxx'],
]);

// Staging credential
$stagingCredential = HttpCredential::create([
    'name' => 'Staging API Key',
    'http_provider_id' => $provider->id,
    'config' => ['api_key' => 'staging_key_xxx'],
]);

// Use same endpoint with different credentials
$prodConfig = HttpEndpointConfiguration::create([
    'name' => 'Production Config',
    'http_endpoint_id' => $endpoint->id,
    'http_credential_id' => $prodCredential->id,
    'configuration' => ['url_params' => ['id' => '123']],
]);
```

## Important Implementation Details

### Authentication Headers

The package uses a **placeholder system** for credentials:

1. **Provider credential_config**: Define header templates with `{{placeholder}}` syntax
2. **Credential config**: Provide actual values matching the placeholder names
3. **HttpManager**: Automatically replaces placeholders at execution time

Example:
```php
// Provider template
'credential_config' => [
    'headers' => [
        'Authorization' => 'Bearer {{token}}',
        'X-API-Key' => '{{api_key}}',
    ],
]

// Credential values (must match placeholder names)
'config' => [
    'token' => 'actual_bearer_token',
    'api_key' => 'actual_api_key',
]
```

### Parameter Types

When defining `options` in HttpEndpoint, use these types:

- `string`: Text values
- `integer`: Numeric values (no quotes)
- `boolean`: true/false
- `array`: List of values
- `object`: Nested key-value pairs (JSON objects)

### URL Parameter Substitution

URL parameters use **single braces** `{param}` in the endpoint path:

```php
'endpoint' => '/users/{userId}/posts/{postId}'

// Configuration provides values
'url_params' => [
    'userId' => '123',
    'postId' => '456',
]

// Results in: https://api.example.com/users/123/posts/456
```

## Multi-Tenancy Support

If team scoping is enabled, all models are automatically filtered by team_id.

### Check if Teams Are Enabled

```php
$teamsEnabled = config('http-manager.teams.enabled', false);
```

### Working with Team-Scoped Models

```php
// Normal queries are automatically scoped to current team
$providers = HttpProvider::all();  // Only current team's providers

// Query without team scope
$allProviders = HttpProvider::withoutTeamScope()->get();

// Query specific team
$team5Providers = HttpProvider::forTeam(5)->get();

// Query all teams
$globalProviders = HttpProvider::forAllTeams()->get();
```

### Manual Team Assignment

```php
// Automatically assigned if team resolver is configured
$provider = HttpProvider::create([
    'name' => 'Provider',
    'base_url' => 'https://api.example.com',
    'credential_config' => ['headers' => ['Auth' => '{{key}}']],
]);

// Or manually specify
$provider = HttpProvider::create([
    'team_id' => 5,
    'name' => 'Provider',
    // ...
]);
```

## Validation

The package includes automatic validation:

### Credential Validation

Validates that all `{{placeholders}}` in provider credential_config have matching values in credential config.

```php
// This will throw ValidationException if 'api_key' is missing
$validator = app(\NckRtl\HttpManager\Services\CredentialValidator::class);
$validator->validate($provider, $credential->config);
```

### Configuration Validation

Validates that endpoint configuration matches the endpoint options schema (required params, types, etc.).

```php
// This will throw ValidationException if required params are missing or types are wrong
$validator = app(\NckRtl\HttpManager\Services\ConfigurationValidator::class);
$validator->validate($endpoint, $configuration->configuration);
```

**Both validators run automatically** when executing requests via HttpManager.

## Security Considerations

### Encrypted Storage

`HttpEndpointConfiguration.configuration` uses Laravel's `encrypted:array` cast. Ensure:
1. `APP_KEY` is set in `.env`
2. Never commit unencrypted credentials to version control
3. Use Laravel's built-in encryption (automatic)

### Credential Config vs Configuration

- **Credential config** (HttpCredential): Stores API keys/tokens (sensitive)
- **Configuration** (HttpEndpointConfiguration): Stores parameter values (encrypted automatically)

Both are stored securely, but configuration has additional encryption layer.

## Common Mistakes to Avoid

### ❌ Wrong: Placeholder Syntax Mismatch

```php
// Provider uses {{api_token}}
'credential_config' => ['headers' => ['Authorization' => 'Bearer {{api_token}}']]

// But credential provides 'api_key' (won't work!)
'config' => ['api_key' => 'xxx']
```

### ✅ Correct: Matching Names

```php
// Provider uses {{api_token}}
'credential_config' => ['headers' => ['Authorization' => 'Bearer {{api_token}}']]

// Credential provides 'api_token' (works!)
'config' => ['api_token' => 'xxx']
```

### ❌ Wrong: URL Param Syntax Confusion

```php
// Don't use {{}} for URL params
'endpoint' => '/users/{{userId}}'  // Wrong!

// Don't use {} for credential placeholders
'credential_config' => ['headers' => ['Auth' => 'Bearer {token}']]  // Wrong!
```

### ✅ Correct: Proper Syntax

```php
// URL params use single braces
'endpoint' => '/users/{userId}'  // Correct

// Credential placeholders use double braces
'credential_config' => ['headers' => ['Auth' => 'Bearer {{token}}']]  // Correct
```

### ❌ Wrong: Type Mismatches

```php
// Endpoint expects integer
'url_params' => ['id' => ['type' => 'integer', 'required' => true]]

// But configuration provides string (validation fails!)
'configuration' => ['url_params' => ['id' => '123']]
```

### ✅ Correct: Matching Types

```php
// Endpoint expects integer
'url_params' => ['id' => ['type' => 'integer', 'required' => true]]

// Configuration provides integer (works!)
'configuration' => ['url_params' => ['id' => 123]]
```

## Troubleshooting Guide

### Issue: "Missing required credential value"

**Cause**: Placeholder in provider credential_config doesn't have matching value in credential config.

**Solution**: Ensure all `{{placeholders}}` have corresponding keys in credential config.

### Issue: "Invalid type for parameter"

**Cause**: Configuration provides wrong type (e.g., string instead of integer).

**Solution**: Match the types defined in endpoint options schema.

### Issue: "No application encryption key"

**Cause**: `APP_KEY` not set in `.env` file.

**Solution**: Run `php artisan key:generate` to set encryption key.

### Issue: "Could not find driver" (SQLite)

**Cause**: SQLite PDO extension not installed (testing environment).

**Solution**: Tests don't require database. The package uses in-memory validation. PHPStan and Pest tests pass without database connection.

## Response Handling

The `execute()` method returns Laravel's `Illuminate\Http\Client\Response`:

```php
$response = $httpManager->execute($configId);

// Check success
if ($response->successful()) {
    $data = $response->json();  // Parse JSON response
}

// Check specific status
if ($response->status() === 200) {
    // Handle 200 OK
}

// Handle errors
if ($response->failed()) {
    $error = $response->body();
    $statusCode = $response->status();
}

// Get headers
$headers = $response->headers();
$contentType = $response->header('Content-Type');
```

## Testing Considerations

When writing tests that use this package:

```php
use Illuminate\Support\Facades\Http;

// Fake HTTP responses in tests
Http::fake([
    'api.example.com/*' => Http::response(['data' => 'test'], 200),
]);

// Create models without database
$provider = new HttpProvider([...]);
$provider->setRelation('credentials', collect());
```

## Quick Reference

### Model Relationships

```php
// Provider has many credentials
$provider->credentials;

// Provider has many endpoints
$provider->endpoints;

// Endpoint belongs to provider
$endpoint->provider;

// Endpoint has many configurations
$endpoint->configurations;

// Configuration belongs to endpoint
$configuration->endpoint;

// Configuration belongs to credential
$configuration->credential;
```

### Service Methods

```php
// Execute by configuration ID
$httpManager->execute($configurationId);

// Execute with configuration object
$httpManager->executeWithConfiguration($configuration);

// Validate credential
$credentialValidator->validate($provider, $credentialConfig);

// Validate configuration
$configurationValidator->validate($endpoint, $configuration);
```

## When to Use This Package

**Good Use Cases:**
- Multiple API integrations that need centralized management
- Different credentials per environment or user
- Multi-tenant applications with per-team API credentials
- Database-driven API configuration (no code deploys for new endpoints)

**Not Ideal For:**
- Simple one-off API calls (use Laravel HTTP client directly)
- Hardcoded API configurations (no need for database storage)
- Real-time high-throughput APIs (database queries add overhead)

## Additional Resources

- **README.md**: Human-readable documentation with examples
- **Tests**: `tests/Unit/*Test.php` for usage examples
- **PRD.md**: Original product requirements document

---

**Note for AI Assistants**: When helping users implement API integrations with this package, always:
1. Start with the provider (base configuration)
2. Create credentials (actual secrets)
3. Define endpoints (structure and schema)
4. Create configurations (combine everything with values)
5. Execute via HttpManager
6. Handle the Response object appropriately

Remember: **Placeholders use `{{double}}` braces, URL params use `{single}` braces.**
