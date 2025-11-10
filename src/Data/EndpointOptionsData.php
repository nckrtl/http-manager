<?php

namespace NckRtl\HttpManager\Data;

use Illuminate\Validation\Rule;
use NckRtl\HttpManager\Enums\ParameterType;
use Spatie\LaravelData\Data;

class EndpointOptionsData extends Data
{
    public function __construct(
        public ?array $url_params = null,
        public ?array $query_params = null,
        public ?array $body = null,
    ) {}

    public static function rules(): array
    {
        $parameterTypeRule = Rule::enum(ParameterType::class);

        return [
            'url_params' => ['nullable', 'array'],
            'url_params.*.type' => ['required', 'string', $parameterTypeRule],
            'url_params.*.required' => ['required', 'boolean'],
            'url_params.*.description' => ['nullable', 'string'],

            'query_params' => ['nullable', 'array'],
            'query_params.*.type' => ['required', 'string', $parameterTypeRule],
            'query_params.*.required' => ['required', 'boolean'],
            'query_params.*.description' => ['nullable', 'string'],
            'query_params.*.default' => ['nullable'],

            'body' => ['nullable', 'array'],
            'body.*.type' => ['required', 'string', $parameterTypeRule],
            'body.*.required' => ['required', 'boolean'],
            'body.*.description' => ['nullable', 'string'],
            'body.*.properties' => ['nullable', 'array'],
        ];
    }
}

