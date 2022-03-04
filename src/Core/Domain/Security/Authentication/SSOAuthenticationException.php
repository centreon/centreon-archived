<?php

namespace Core\Domain\Security\Authentication;

class SSOAuthenticationException extends \Exception
{
    public static function tokensExpired(string $providerName): self
    {
        return new self(sprintf(_('[%s]: Both provider and refresh token have expired'), $providerName));
    }

    public static function requestForConnectionTokenFail(): self
    {
        return new self(_('Request for connection token to external provider has failed'));
    }

    public static function errorFromExternalProvider(string $providerName): self
    {
        return new self(sprintf(_('[%s]: An error occured during your request'), $providerName));
    }

    public static function requestForRefreshTokenFail(): self
    {
        return new self(_('Request for refresh token to external provider has failed'));
    }

    public static function requestForIntrospectionTokenFail(): self
    {
        return new self(_('Request for introspection token to external provider has failed'));
    }

    public static function requestForUserInformationFail(): self
    {
        return new self(_('Request for user information to external provider has failed'));
    }

    public static function blackListedClient(): self
    {
        return new self(_('Your IP is blacklisted'));
    }

    public static function notWhiteListedClient(): self
    {
        return new self(_('Your IP is not whitelisted'));
    }

    public static function loginClaimNotFound(string $providerName, string $loginClaim): self
    {
        return new self(sprintf(
            _('[%s]: Login claim [%s] not found from external provider user'),
            $providerName,
            $loginClaim
        ));
    }

    public static function noAuthorizationCode(string $providerName): self
    {
        return new self(sprintf(_('[%s]: No authorization code return by external provider'), $providerName));
    }
}
