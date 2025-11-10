<?php

namespace NckRtl\HttpManager\Data;

use Spatie\LaravelData\Data;

class EndpointConfigurationData extends Data
{
    public function __construct(
        public ?array $url_params = null,
        public ?array $query_params = null,
        public ?array $body = null,
    ) {}

    public static function rules(): array
    {
        // Base rules - all parameters are optional at this level
        // The ConfigurationValidator service will handle schema validation
        return [
            'url_params' => ['nullable', 'array'],
            'query_params' => ['nullable', 'array'],
            'body' => ['nullable', 'array'],
        ];
    }
}
