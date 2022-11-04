<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Security\Authentication\Domain\Exception;

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
        return new self(sprintf(_('[%s]: Both provider and refresh tokens have expired'), $providerName));
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

    /**
     * Exception thrown when the username can't be extract with matching regexp
     *
     * @return self
     */
    public static function unableToRetrieveUsernameFromLoginClaim(): self
    {
        return new self(_('Can\'t resolve username from login claim using configured regular expression'));
    }

    /**
     * Exception thrown when bind attributes for auto import are not found in user informations from external provider
     *
     * @param array<string> $missingAttributes
     * @return self
     */
    public static function autoImportBindAttributeNotFound(array $missingAttributes): self
    {
        return new self(sprintf(
            _('The following bound attributes are missing: %s'),
            implode(", ", $missingAttributes)
        ));
    }

    /**
     * Exception thrown when the id_token couldn't be decoded
     *
     * @return self
     */
    public static function unableToDecodeIdToken(): self
    {
        return new self(_("An error occured while decoding Identity Provider ID Token"));
    }

    /**
     * @return SSOAuthenticationException
     */
    public static function requestForCustomACLConditionsEndpointFail(): self
    {
        return new self(_('The request for roles mapping on custom endpoint has failed'));
    }

    /**
     * Exception thrown when the request to authentication condition fail
     *
     * @return self
     */
    public static function requestForCustomAuthenticationConditionsEndpointFail(): self
    {
        return new self(_('Request for authentication conditions custom endpoint has failed'));
    }
}
