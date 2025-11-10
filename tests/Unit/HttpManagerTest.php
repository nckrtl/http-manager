<?php

use Illuminate\Support\Facades\Http;
use NckRtl\HttpManager\Exceptions\HttpManagerException;
use NckRtl\HttpManager\Models\HttpCredential;
use NckRtl\HttpManager\Models\HttpEndpoint;
use NckRtl\HttpManager\Models\HttpEndpointConfiguration;
use NckRtl\HttpManager\Models\HttpProvider;
use NckRtl\HttpManager\Services\ConfigurationValidator;
use NckRtl\HttpManager\Services\CredentialValidator;
use NckRtl\HttpManager\Services\HttpManager;

beforeEach(function () {
    $this->credentialValidator = new CredentialValidator;
    $this->configurationValidator = new ConfigurationValidator;
    $this->httpManager = new HttpManager(
        $this->configurationValidator,
        $this->credentialValidator
    );
});

it('executes a simple GET request with API key authentication', function () {
    Http::fake([
        'api.example.com/users/octocat' => Http::response(['login' => 'octocat'], 200),
    ]);

    $provider = new HttpProvider([
        'name' => 'GitHub',
        'base_url' => 'https://api.example.com',
        'credential_config' => [
            'headers' => [
                'Authorization' => 'token {{api_key}}',
            ],
        ],
    ]);

    $credential = new HttpCredential([
        'name' => 'GitHub Token',
        'config' => [
            'api_key' => 'ghp_test123',
        ],
    ]);
    $credential->setRelation('provider', $provider);

    $endpoint = new HttpEndpoint([
        'name' => 'Get User',
        'method' => 'GET',
        'endpoint' => '/users/{username}',
        'options' => [
            'url_params' => [
                'username' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ],
    ]);
    $endpoint->setRelation('provider', $provider);

    $configuration = new HttpEndpointConfiguration([
        'name' => 'Get Octocat',
        'configuration' => [
            'url_params' => [
                'username' => 'octocat',
            ],
        ],
    ]);
    $configuration->setRelation('endpoint', $endpoint);
    $configuration->setRelation('credential', $credential);

    $response = $this->httpManager->executeWithConfiguration($configuration);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBe(['login' => 'octocat']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/users/octocat'
            && $request->method() === 'GET'
            && $request->hasHeader('Authorization', 'token ghp_test123');
    });
});

it('executes a POST request with body parameters', function () {
    Http::fake([
        'api.stripe.com/v1/charges' => Http::response(['id' => 'ch_123'], 200),
    ]);

    $provider = new HttpProvider([
        'name' => 'Stripe',
        'base_url' => 'https://api.stripe.com',
        'credential_config' => [
            'headers' => [
                'Authorization' => 'Bearer {{secret_key}}',
            ],
        ],
    ]);

    $credential = new HttpCredential([
        'name' => 'Stripe Secret',
        'config' => [
            'secret_key' => 'sk_test_123',
        ],
    ]);
    $credential->setRelation('provider', $provider);

    $endpoint = new HttpEndpoint([
        'name' => 'Create Charge',
        'method' => 'POST',
        'endpoint' => '/v1/charges',
        'options' => [
            'body' => [
                'amount' => [
                    'type' => 'integer',
                    'required' => true,
                ],
                'currency' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ],
    ]);
    $endpoint->setRelation('provider', $provider);

    $configuration = new HttpEndpointConfiguration([
        'name' => 'Charge $50',
        'configuration' => [
            'body' => [
                'amount' => 5000,
                'currency' => 'usd',
            ],
        ],
    ]);
    $configuration->setRelation('endpoint', $endpoint);
    $configuration->setRelation('credential', $credential);

    $response = $this->httpManager->executeWithConfiguration($configuration);

    expect($response->status())->toBe(200)
        ->and($response->json())->toBe(['id' => 'ch_123']);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.stripe.com/v1/charges'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer sk_test_123')
            && $request->data() === ['amount' => 5000, 'currency' => 'usd'];
    });
});

it('executes a request with query parameters', function () {
    Http::fake([
        'api.example.com/search*' => Http::response(['results' => []], 200),
    ]);

    $provider = new HttpProvider([
        'name' => 'Search API',
        'base_url' => 'https://api.example.com',
        'credential_config' => [
            'headers' => [
                'X-API-Key' => '{{api_key}}',
            ],
        ],
    ]);

    $credential = new HttpCredential([
        'name' => 'API Key',
        'config' => [
            'api_key' => 'test_key',
        ],
    ]);
    $credential->setRelation('provider', $provider);

    $endpoint = new HttpEndpoint([
        'name' => 'Search',
        'method' => 'GET',
        'endpoint' => '/search',
        'options' => [
            'query_params' => [
                'q' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'limit' => [
                    'type' => 'integer',
                    'required' => false,
                ],
            ],
        ],
    ]);
    $endpoint->setRelation('provider', $provider);

    $configuration = new HttpEndpointConfiguration([
        'name' => 'Search Laravel',
        'configuration' => [
            'query_params' => [
                'q' => 'laravel',
                'limit' => 10,
            ],
        ],
    ]);
    $configuration->setRelation('endpoint', $endpoint);
    $configuration->setRelation('credential', $credential);

    $response = $this->httpManager->executeWithConfiguration($configuration);

    expect($response->status())->toBe(200);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'api.example.com/search')
            && $request->method() === 'GET'
            && $request->hasHeader('X-API-Key', 'test_key')
            && str_contains($request->url(), 'q=laravel')
            && str_contains($request->url(), 'limit=10');
    });
});

it('throws exception for unsupported HTTP methods', function () {
    $provider = new HttpProvider([
        'name' => 'Test Provider',
        'base_url' => 'https://api.example.com',
        'credential_config' => [
            'headers' => [
                'Authorization' => 'Bearer {{token}}',
            ],
        ],
    ]);

    $credential = new HttpCredential([
        'name' => 'Test Credential',
        'config' => [
            'token' => 'test_token',
        ],
    ]);
    $credential->setRelation('provider', $provider);

    $endpoint = new HttpEndpoint([
        'name' => 'Invalid Method',
        'method' => 'INVALID',
        'endpoint' => '/test',
    ]);
    $endpoint->setRelation('provider', $provider);

    $configuration = new HttpEndpointConfiguration([
        'name' => 'Test Config',
        'configuration' => [],
    ]);
    $configuration->setRelation('endpoint', $endpoint);
    $configuration->setRelation('credential', $credential);

    $this->httpManager->executeWithConfiguration($configuration);
})->throws(HttpManagerException::class, 'Unsupported HTTP method: INVALID');

it('builds URLs correctly with multiple parameters', function () {
    Http::fake([
        'api.example.com/repos/laravel/framework/issues/123' => Http::response(['id' => 123], 200),
    ]);

    $provider = new HttpProvider([
        'name' => 'GitHub',
        'base_url' => 'https://api.example.com',
        'credential_config' => [
            'headers' => [
                'Authorization' => 'token {{api_key}}',
            ],
        ],
    ]);

    $credential = new HttpCredential([
        'name' => 'GitHub Token',
        'config' => [
            'api_key' => 'ghp_test',
        ],
    ]);
    $credential->setRelation('provider', $provider);

    $endpoint = new HttpEndpoint([
        'name' => 'Get Issue',
        'method' => 'GET',
        'endpoint' => '/repos/{owner}/{repo}/issues/{issue_number}',
        'options' => [
            'url_params' => [
                'owner' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'repo' => [
                    'type' => 'string',
                    'required' => true,
                ],
                'issue_number' => [
                    'type' => 'integer',
                    'required' => true,
                ],
            ],
        ],
    ]);
    $endpoint->setRelation('provider', $provider);

    $configuration = new HttpEndpointConfiguration([
        'name' => 'Get Laravel Issue',
        'configuration' => [
            'url_params' => [
                'owner' => 'laravel',
                'repo' => 'framework',
                'issue_number' => 123,
            ],
        ],
    ]);
    $configuration->setRelation('endpoint', $endpoint);
    $configuration->setRelation('credential', $credential);

    $response = $this->httpManager->executeWithConfiguration($configuration);

    expect($response->status())->toBe(200);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.example.com/repos/laravel/framework/issues/123';
    });
});
