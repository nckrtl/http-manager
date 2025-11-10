<?php

namespace NckRtl\HttpManager\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use NckRtl\HttpManager\Exceptions\HttpManagerException;
use NckRtl\HttpManager\Models\HttpEndpointConfiguration;

class HttpManager
{
    public function __construct(
        protected ConfigurationValidator $configurationValidator,
        protected CredentialValidator $credentialValidator,
    ) {}

    public function execute(int $configurationId): Response
    {
        $configuration = HttpEndpointConfiguration::with([
            'endpoint.provider',
            'credential.provider',
        ])->findOrFail($configurationId);

        return $this->executeWithConfiguration($configuration);
    }

    public function executeWithConfiguration(HttpEndpointConfiguration $configuration): Response
    {
        /** @var \NckRtl\HttpManager\Models\HttpEndpoint $endpoint */
        $endpoint = $configuration->endpoint;
        /** @var \NckRtl\HttpManager\Models\HttpProvider $provider */
        $provider = $endpoint->provider;
        /** @var \NckRtl\HttpManager\Models\HttpCredential $credential */
        $credential = $configuration->credential;

        // Validate credential against provider's credential config
        $this->credentialValidator->validate($provider, $credential->config);

        // Validate configuration against endpoint's options
        $this->configurationValidator->validate($endpoint, $configuration->configuration);

        // Build the complete URL
        $url = $this->buildUrl(
            $provider->base_url,
            $endpoint->endpoint,
            $configuration->configuration['url_params'] ?? []
        );

        // Build the HTTP client with authentication
        $client = $this->buildClient($provider->credential_config, $credential->config);

        // Prepare request parameters
        $options = $this->buildRequestOptions($endpoint, $configuration->configuration);

        // Execute the request
        return $this->executeRequest($client, $endpoint->method, $url, $options);
    }

    protected function buildUrl(string $baseUrl, string $endpoint, array $urlParams): string
    {
        $url = rtrim($baseUrl, '/').'/'.ltrim($endpoint, '/');

        // Replace URL parameters like {id} or {username}
        foreach ($urlParams as $key => $value) {
            $url = str_replace('{'.$key.'}', $value, $url);
        }

        return $url;
    }

    protected function buildClient(array $credentialConfig, array $credentialValues): PendingRequest
    {
        $client = Http::asJson()->acceptJson();

        // Apply authentication headers
        if (isset($credentialConfig['headers'])) {
            foreach ($credentialConfig['headers'] as $headerName => $headerTemplate) {
                $headerValue = $this->replacePlaceholders($headerTemplate, $credentialValues);
                $client = $client->withHeader($headerName, $headerValue);
            }
        }

        return $client;
    }

    protected function replacePlaceholders(string $template, array $values): string
    {
        foreach ($values as $key => $value) {
            $template = str_replace('{{'.$key.'}}', $value, $template);
        }

        return $template;
    }

    protected function buildRequestOptions(
        \NckRtl\HttpManager\Models\HttpEndpoint $endpoint,
        array $configuration
    ): array {
        $options = [];

        // Add query parameters
        if (isset($configuration['query_params'])) {
            $options['query'] = $configuration['query_params'];
        }

        // Add body parameters
        if (isset($configuration['body'])) {
            $options['body'] = $configuration['body'];
        }

        return $options;
    }

    protected function executeRequest(
        PendingRequest $client,
        string $method,
        string $url,
        array $options
    ): Response {
        $method = strtoupper($method);

        return match ($method) {
            'GET' => $client->get($url, $options['query'] ?? []),
            'POST' => $client->post($url, $options['body'] ?? []),
            'PUT' => $client->put($url, $options['body'] ?? []),
            'PATCH' => $client->patch($url, $options['body'] ?? []),
            'DELETE' => $client->delete($url, $options['body'] ?? []),
            default => throw new HttpManagerException("Unsupported HTTP method: {$method}"),
        };
    }
}
