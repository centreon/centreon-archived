<?php

namespace Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions;

class OpenIdConfigurationException extends \Exception
{
    public static function missingTokenEndpoint()
    {
        return new self(_('Missing token endpoint in your configuration'));
    }

    public static function missingInformationEndpoint()
    {
        return new self(_('Missing userinfo or introspection token endpoint'));
    }
}
