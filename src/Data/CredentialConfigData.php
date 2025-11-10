<?php

namespace NckRtl\HttpManager\Data;

use NckRtl\HttpManager\Enums\AuthenticationType;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Data;

class CredentialConfigData extends Data
{
    public function __construct(
        #[Enum(AuthenticationType::class)]
        public AuthenticationType $type,
        public array $headers,
    ) {}

    public static function rules(): array
    {
        return [
            'headers' => ['required', 'array', 'min:1'],
            'headers.*' => ['required', 'string'],
        ];
    }
}
