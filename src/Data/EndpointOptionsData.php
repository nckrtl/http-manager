<?php

namespace NckRtl\HttpManager\Data;

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
        return [
            'url_params' => ['nullable', 'array'],
            'url_params.*.type' => ['required', 'string', 'in:string,integer,boolean,array,object'],
            'url_params.*.required' => ['required', 'boolean'],
            'url_params.*.description' => ['nullable', 'string'],

            'query_params' => ['nullable', 'array'],
            'query_params.*.type' => ['required', 'string', 'in:string,integer,boolean,array,object'],
            'query_params.*.required' => ['required', 'boolean'],
            'query_params.*.description' => ['nullable', 'string'],
            'query_params.*.default' => ['nullable'],

            'body' => ['nullable', 'array'],
            'body.*.type' => ['required', 'string', 'in:string,integer,boolean,array,object'],
            'body.*.required' => ['required', 'boolean'],
            'body.*.description' => ['nullable', 'string'],
            'body.*.properties' => ['nullable', 'array'],
        ];
    }
}
