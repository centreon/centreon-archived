<?php

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions;

class OpenIdConfigurationException extends \Exception
{
    /**
     * Exception thrown when token endpoint is needed but missing.
     * @return self
     */
    public static function missingTokenEndpoint()
    {
        return new self(_('Missing token endpoint in your configuration'));
    }

    /**
     * Exception thrown when both user information endpoints are missing.
     * @return self
     */
    public static function missingInformationEndpoint()
    {
        return new self(_('Missing userinfo and introspection token endpoint'));
    }
}
