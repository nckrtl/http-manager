<?php

namespace NckRtl\HttpManager\Services;

use NckRtl\HttpManager\Exceptions\ValidationException;
use NckRtl\HttpManager\Models\HttpProvider;

class CredentialValidator
{
    /**
     * Validate credential configuration against provider credential config
     *
     *
     * @throws ValidationException
     */
    public function validate(HttpProvider $provider, array $credentialConfig): void
    {
        $requiredPlaceholders = $this->extractPlaceholders($provider->credential_config);

        foreach ($requiredPlaceholders as $placeholder) {
            if (! isset($credentialConfig[$placeholder])) {
                throw new ValidationException(
                    "Missing required credential value: {$placeholder}"
                );
            }
        }
    }

    /**
     * Extract placeholders from provider credential config
     */
    protected function extractPlaceholders(array $config): array
    {
        $placeholders = [];

        if (isset($config['headers'])) {
            foreach ($config['headers'] as $template) {
                preg_match_all('/\{\{(\w+)\}\}/', $template, $matches);
                $placeholders = array_merge($placeholders, $matches[1]);
            }
        }

        return array_unique($placeholders);
    }
}
