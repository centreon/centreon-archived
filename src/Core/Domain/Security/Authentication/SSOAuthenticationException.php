<?php

namespace Core\Domain\Security\Authentication;

class SSOAuthenticationException extends \Exception
{
    /**
     * Exception thrown when tokens are expired
     *
     * @param string $providerName
     * @return self
     */
    public static function tokensExpired(string $providerName): self
    {
        return new self(sprintf(_('[%s]: Both provider and refresh token have expired'), $providerName));
    }

    /**
     * Exception thrown when request for connection token failed
     *
     * @return self
     */
    public static function requestForConnectionTokenFail(): self
    {
        return new self(_('Request for connection token to external provider has failed'));
    }

    /**
     * Exception thrown when the external provider return an error
     *
     * @param string $providerName
     * @return self
     */
    public static function errorFromExternalProvider(string $providerName): self
    {
        return new self(sprintf(_('[%s]: An error occured during your request'), $providerName));
    }

    /**
     * Exception thrown when the request for refresh token failed
     *
     * @return self
     */
    public static function requestForRefreshTokenFail(): self
    {
        return new self(_('Request for refresh token to external provider has failed'));
    }

    /**
     * Exception thrown when the request for introspection token failed
     *
     * @return self
     */
    public static function requestForIntrospectionTokenFail(): self
    {
        return new self(_('Request for introspection token to external provider has failed'));
    }

    /**
     * Exception thrown when the request for user information failed
     *
     * @return self
     */
    public static function requestForUserInformationFail(): self
    {
        return new self(_('Request for user information to external provider has failed'));
    }

    /**
     * Exception thrown when the IP is blacklisted
     *
     * @return self
     */
    public static function blackListedClient(): self
    {
        return new self(_('Your IP is blacklisted'));
    }

    /**
     * Exception thrown when the IP is not whitelisted
     *
     * @return self
     */
    public static function notWhiteListedClient(): self
    {
        return new self(_('Your IP is not whitelisted'));
    }

    /**
     * Exception thrown when the login claim was not found
     *
     * @param string $providerName
     * @param string $loginClaim
     * @return self
     */
    public static function loginClaimNotFound(string $providerName, string $loginClaim): self
    {
        return new self(sprintf(
            _('[%s]: Login claim [%s] not found from external provider user'),
            $providerName,
            $loginClaim
        ));
    }

    /**
     * Exception thrown when no Authorization Code has been return
     *
     * @param string $providerName
     * @return self
     */
    public static function noAuthorizationCode(string $providerName): self
    {
        return new self(sprintf(_('[%s]: No authorization code return by external provider'), $providerName));
    }

    /**
     * Exception thrown when no refresh token could be found
     *
     * @return self
     */
    public static function noRefreshToken(): self
    {
        return new self(_('No refresh token has been found'));
    }
}
