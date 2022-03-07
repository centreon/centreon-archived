<?php

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions;

class OpenIdConfigurationException extends \Exception
{
    /**
     * Exception thrown when token endpoint is needed but missing.
     */
    public static function missingTokenEndpoint()
    {
        return new self(_('Missing token endpoint in your configuration'));
    }

    /**
     * Exception thrown when both user information endpoints are missing.
     */
    public static function missingInformationEndpoint()
    {
        return new self(_('Missing userinfo and introspection token endpoint'));
    }
}
