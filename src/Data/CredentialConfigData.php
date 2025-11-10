<?php

namespace NckRtl\HttpManager\Data;

use Spatie\LaravelData\Data;

class CredentialConfigData extends Data
{
    public function __construct(
        public string $type,
        public array $headers,
    ) {}

    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:Bearer,ApiKey,Basic,Custom'],
            'headers' => ['required', 'array', 'min:1'],
            'headers.*' => ['required', 'string'],
        ];
    }
}
