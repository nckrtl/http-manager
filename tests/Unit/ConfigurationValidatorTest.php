<?php

use NckRtl\HttpManager\Exceptions\ValidationException;
use NckRtl\HttpManager\Models\HttpEndpoint;
use NckRtl\HttpManager\Services\ConfigurationValidator;

it('validates missing required parameters', function () {
    $endpoint = new HttpEndpoint([
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

    $validator = new ConfigurationValidator();

    expect(fn () => $validator->validate($endpoint, []))
        ->toThrow(ValidationException::class, "Missing required parameter 'amount' in body");
});

it('passes with valid configuration', function () {
    $endpoint = new HttpEndpoint([
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

    $validator = new ConfigurationValidator();

    $validator->validate($endpoint, [
        'body' => [
            'amount' => 5000,
            'currency' => 'usd',
        ],
    ]);

    expect(true)->toBeTrue();
});

it('validates parameter types', function () {
    $endpoint = new HttpEndpoint([
        'options' => [
            'body' => [
                'amount' => [
                    'type' => 'integer',
                    'required' => true,
                ],
            ],
        ],
    ]);

    $validator = new ConfigurationValidator();

    expect(fn () => $validator->validate($endpoint, [
        'body' => [
            'amount' => 'not-an-integer',
        ],
    ]))->toThrow(ValidationException::class, "Invalid type for parameter 'amount' in body. Expected integer, got string");
});

it('allows optional parameters to be missing', function () {
    $endpoint = new HttpEndpoint([
        'options' => [
            'body' => [
                'amount' => [
                    'type' => 'integer',
                    'required' => true,
                ],
                'description' => [
                    'type' => 'string',
                    'required' => false,
                ],
            ],
        ],
    ]);

    $validator = new ConfigurationValidator();

    // Should not throw - description is optional
    $validator->validate($endpoint, [
        'body' => [
            'amount' => 5000,
        ],
    ]);

    expect(true)->toBeTrue();
});

it('validates url_params correctly', function () {
    $endpoint = new HttpEndpoint([
        'options' => [
            'url_params' => [
                'username' => [
                    'type' => 'string',
                    'required' => true,
                ],
            ],
        ],
    ]);

    $validator = new ConfigurationValidator();

    expect(fn () => $validator->validate($endpoint, []))
        ->toThrow(ValidationException::class, "Missing required parameter 'username' in url_params");

    $validator->validate($endpoint, [
        'url_params' => [
            'username' => 'octocat',
        ],
    ]);

    expect(true)->toBeTrue();
});
