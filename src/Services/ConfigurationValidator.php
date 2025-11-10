<?php

namespace NckRtl\HttpManager\Services;

use NckRtl\HttpManager\Exceptions\ValidationException;
use NckRtl\HttpManager\Models\HttpEndpoint;

class ConfigurationValidator
{
    /**
     * Validate configuration against endpoint options schema
     *
     * @param  HttpEndpoint  $endpoint
     * @param  array  $configuration
     * @return void
     *
     * @throws ValidationException
     */
    public function validate(HttpEndpoint $endpoint, array $configuration): void
    {
        if (empty($endpoint->options)) {
            return; // No validation needed if no options defined
        }

        $options = $endpoint->options;

        // Validate URL parameters
        if (isset($options['url_params'])) {
            $this->validateParameters(
                $options['url_params'],
                $configuration['url_params'] ?? [],
                'url_params'
            );
        }

        // Validate query parameters
        if (isset($options['query_params'])) {
            $this->validateParameters(
                $options['query_params'],
                $configuration['query_params'] ?? [],
                'query_params'
            );
        }

        // Validate body parameters
        if (isset($options['body'])) {
            $this->validateParameters(
                $options['body'],
                $configuration['body'] ?? [],
                'body'
            );
        }
    }

    /**
     * Validate parameters against schema
     *
     * @param  array  $schema
     * @param  array  $values
     * @param  string  $context
     * @return void
     *
     * @throws ValidationException
     */
    protected function validateParameters(
        array $schema,
        array $values,
        string $context
    ): void {
        foreach ($schema as $key => $rules) {
            $required = $rules['required'] ?? false;
            $type = $rules['type'] ?? 'string';

            // Check if required parameter is missing
            if ($required && ! isset($values[$key])) {
                throw new ValidationException(
                    "Missing required parameter '{$key}' in {$context}"
                );
            }

            // Validate type if value is present
            if (isset($values[$key])) {
                $this->validateType($values[$key], $type, $key, $context);
            }
        }
    }

    /**
     * Validate parameter type
     *
     * @param  mixed  $value
     * @param  string  $type
     * @param  string  $key
     * @param  string  $context
     * @return void
     *
     * @throws ValidationException
     */
    protected function validateType($value, string $type, string $key, string $context): void
    {
        $valid = match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'boolean' => is_bool($value),
            'array' => is_array($value),
            'object' => is_array($value), // JSON objects are arrays in PHP
            default => true,
        };

        if (! $valid) {
            throw new ValidationException(
                "Invalid type for parameter '{$key}' in {$context}. Expected {$type}, got ".gettype($value)
            );
        }
    }
}
