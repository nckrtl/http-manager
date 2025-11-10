<?php

use NckRtl\HttpManager\Exceptions\ValidationException;
use NckRtl\HttpManager\Models\HttpProvider;
use NckRtl\HttpManager\Services\CredentialValidator;

it('validates missing credential values', function () {
    $provider = new HttpProvider([
        'credential_config' => [
            'type' => 'ApiKey',
            'headers' => [
                'Authorization' => 'Bearer {{token}}',
            ],
        ],
    ]);

    $validator = new CredentialValidator;

    expect(fn () => $validator->validate($provider, []))
        ->toThrow(ValidationException::class, 'Missing required credential value: token');
});

it('passes with valid credentials', function () {
    $provider = new HttpProvider([
        'credential_config' => [
            'type' => 'ApiKey',
            'headers' => [
                'Authorization' => 'Bearer {{token}}',
            ],
        ],
    ]);

    $validator = new CredentialValidator;

    // Should not throw exception
    $validator->validate($provider, ['token' => 'abc123']);

    expect(true)->toBeTrue();
});

it('extracts multiple placeholders correctly', function () {
    $provider = new HttpProvider([
        'credential_config' => [
            'type' => 'ApiKey',
            'headers' => [
                'Authorization' => 'Bearer {{token}}',
                'X-Client-ID' => '{{client_id}}',
            ],
        ],
    ]);

    $validator = new CredentialValidator;

    // Missing both values
    expect(fn () => $validator->validate($provider, []))
        ->toThrow(ValidationException::class);

    // Missing client_id
    expect(fn () => $validator->validate($provider, ['token' => 'abc']))
        ->toThrow(ValidationException::class, 'Missing required credential value: client_id');

    // All values present
    $validator->validate($provider, ['token' => 'abc', 'client_id' => '123']);

    expect(true)->toBeTrue();
});
