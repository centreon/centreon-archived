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

namespace Core\Domain\Security\Provider;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Core\Domain\Security\Authentication\AuthenticationException;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class OpenIdProvider implements ProviderInterface
{
    /**
     * @var OpenIdConfiguration
     */
    private OpenIdConfiguration $configuration;

    /**
     * @var ProviderToken
     */
    private ProviderToken $providerToken;

    /**
     * @var ProviderToken
     */
    private ProviderToken $refreshToken;

    /**
     * @var array<string,mixed>
     */
    private array $userInformations;

    /**
     * @var string
     */
    private string $username;

    /**
     * @var \Centreon
     */
    private $legacySession;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(
        private HttpClientInterface $client,
        private UrlGeneratorInterface $router,
        private ContactServiceInterface $contactService
    ) {
    }

    /**
     * @return OpenIdConfiguration
     */
    public function getConfiguration(): OpenIdConfiguration
    {
        return $this->configuration;
    }

    /**
     * @return ProviderToken
     */
    public function getProviderToken(): ProviderToken
    {
        return $this->providerToken;
    }

    /**
     * @return ProviderToken
     */
    public function getProviderRefreshToken(): ProviderToken
    {
        return $this->refreshToken;
    }

    /**
     * @return OpenIdProvider
     */
    public function setConfiguration(OpenIdConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function canCreateUser(): bool
    {
        return true;
    }

    public function createUser(): ?ContactInterface
    {
        // @todo: implement this method when handling autoimport
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getLegacySession(): \Centreon
    {
        return $this->legacySession;
    }

    /**
     * @inheritDoc
     */
    public function setLegacySession(\Centreon $legacySession): void
    {
        $this->legacySession = $legacySession;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return OpenIdConfiguration::NAME;
    }

    /**
     * @inheritDoc
     */
    public function canRefreshToken(): bool
    {
        return true;
    }

    /**
     * Authenticate the user using OpenId Provider.
     *
     * @param string|null $authorizationCode
     */
    public function authenticateOrFail(?string $authorizationCode): void
    {
        if (empty($authorizationCode['code']) || $authorizationCode['code'] === null) {
            //throw exception
        }

        $this->verifyThatClientIsAllowedToConnectOrFail();

        $this->sendRequestForConnectionTokenOrFail($authorizationCode);
        if ($this->providerToken->isExpired() && !$this->refreshToken->isExpired()) {
            $this->refreshToken();
        }
        if ($this->providerToken->isExpired() && $this->refreshToken->isExpired()) {
            throw AuthenticationException::notAuthenticated();
        }
        if ($this->configuration->getIntrospectionTokenEndpoint() !== null) {
            $this->sendRequestForIntrospectionTokenOrFail();
        }

        $loginClaim = $this->configuration->getLoginClaim() ?? OpenIdConfiguration::DEFAULT_LOGIN_GLAIM;
        if (
            !array_key_exists($loginClaim, $this->userInformations)
            && $this->configuration->getUserInformationEndpoint() !== null
        ) {
            $this->sendRequestForUserInformationOrFail();
        }
        if (!array_key_exists($loginClaim, $this->userInformations)) {
            throw AuthenticationException::notAuthenticated();
        }

        $this->username = $this->getUsernameFromLoginClaim();
    }

    /**
     * Get User
     *
     * @return ContactInterface|null
     */
    public function getUser(): ?ContactInterface
    {
        $user = $this->contactService->findByName($this->username);
        if ($user === null) {
            $user = $this->contactService->findByEmail($this->username);
        }

        return $user;
    }

    /**
     * Get Connection Token from OpenId Provider.
     *
     * @param string $authorizationCode
     */
    private function sendRequestForConnectionTokenOrFail(string $authorizationCode): void
    {
        // Define parameters for the request
        $redirectUri = $this->router->generate(
            'centreon_security_authentication_openid_login',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $data = [
            "grant_type" => "authorization_code",
            "code" => $authorizationCode,
            "redirect_uri" => $redirectUri
        ];
        $headers = [
            'Content-Type' => "application/x-www-form-urlencoded"
        ];

        // Define authentication type based on configuration
        if ($this->configuration->getAuthenticationType() === OpenIdConfiguration::AUTHENTICATION_BASIC) {
            $headers['Authorization'] = "Basic " . base64_encode(
                $this->configuration->getClientId() . ":" . $this->configuration->getClientSecret()
            );
        } else {
            $data["client_id"] = $this->configuration->getClientId();
            $data["client_secret"] = $this->configuration->getClientSecret();
        }

        // Send the request to IDP
        try {
            $response = $this->client->request(
                'POST',
                $this->configuration->getBaseUrl() . '/' . ltrim($this->configuration->getTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $this->configuration->verifyPeer()
                ]
            );
        } catch (\Exception $e) {
                sprintf(
                    "[Error] Unable to get Token Access Information:, message: %s",
                    $e->getMessage()
                );
                throw AuthenticationException::notAuthenticated();
        }

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            throw AuthenticationException::notAuthenticated();
        }
        $content = json_decode($response->getContent(false), true);
        if (array_key_exists('error', $content) || empty($content)) {
            //add logs

            //throw exception
            throw AuthenticationException::notAuthenticated();
        }
        // Create Provider and Refresh Tokens
        $creationDate = new \DateTime();
        $providerTokenExpiration = (new \DateTime())->add(new \DateInterval('PT' . 60 . 'S'));
        $refreshTokenExpiration = (new \DateTime())
            ->add(new \DateInterval('PT' . $content['refresh_expires_in'] . 'S'));
        $this->providerToken =  new ProviderToken(
            null,
            $content['access_token'],
            $creationDate,
            $providerTokenExpiration
        );
        $this->refreshToken = new ProviderToken(
            null,
            $content['refresh_token'],
            $creationDate,
            $refreshTokenExpiration
        );
    }

    /**
     * Refresh Access Token
     */
    public function refreshToken(?AuthenticationTokens $authenticationToken = null): AuthenticationTokens
    {
        // Define parameters for the request
        $data = [
            "grant_type" => "refresh_token",
            "refresh_token" => $authenticationToken !== null
                ? $authenticationToken->getProviderRefreshToken()->getToken()
                : $this->refreshToken->getToken(),
            "scope" => !empty($this->configuration->getConnectionScopes())
                ? implode(' ', $this->configuration->getConnectionScopes())
                : null
        ];
        $headers = [
            'Content-Type' => "application/x-www-form-urlencoded"
        ];

        // Define authentication type based on configuration
        if ($this->configuration->getAuthenticationType() === OpenIdConfiguration::AUTHENTICATION_BASIC) {
            $headers['Authorization'] = "Basic " . base64_encode(
                $this->configuration->getClientId() . ":" . $this->configuration->getClientSecret()
            );
        } else {
            $data["client_id"] = $this->configuration->getClientId();
            $data["client_secret"] = $this->configuration->getClientSecret();
        }

        // Send the request to IDP
        try {
            $response = $this->client->request(
                'POST',
                $this->configuration->getBaseUrl() . '/' . ltrim($this->configuration->getTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $this->configuration->verifyPeer()
                ]
            );
        } catch (\Exception $e) {
                sprintf(
                    "[Error] Unable to get Token Access Information:, message: %s",
                    $e->getMessage()
                );
                throw AuthenticationException::notAuthenticated();
        }

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            throw AuthenticationException::notAuthenticated();
        }
        $content = json_decode($response->getContent(false), true);
        if (array_key_exists('error', $content) || empty($content)) {
            //add logs

            //throw exception
            throw AuthenticationException::notAuthenticated();
        }
        $creationDate = new \DateTime();
        $providerTokenExpiration = (new \DateTime())->add(new \DateInterval('PT' . $content ['expires_in'] . 'S'));
        $refreshTokenExpiration = (new \DateTime())
            ->add(new \DateInterval('PT' . $content ['refresh_expires_in'] . 'S'));
        $this->providerToken =  new ProviderToken(
            $authenticationToken !== null ? $authenticationToken->getProviderToken()->getId() : null,
            $content['access_token'],
            $creationDate,
            $providerTokenExpiration
        );
        $this->refreshToken = new ProviderToken(
            $authenticationToken !== null ? $authenticationToken->getProviderRefreshToken()->getId() : null,
            $content['refresh_token'],
            $creationDate,
            $refreshTokenExpiration
        );

        return new AuthenticationTokens(
            $authenticationToken->getUserId(),
            $authenticationToken->getConfigurationProviderId(),
            $authenticationToken->getSessionToken(),
            $this->providerToken,
            $this->refreshToken
        );
    }

    private function sendRequestForIntrospectionTokenOrFail(): void
    {
        // Define parameters for the request
        $data = [
            "token" => $this->providerToken->getToken(),
            "client_id" => $this->configuration->getClientId(),
            "client_secret" => $this->configuration->getClientSecret()
        ];
        $headers = [
            'Authorization' => 'Bearer ' . trim($this->providerToken->getToken())
        ];
        try {
            $response = $this->client->request(
                'POST',
                $this->configuration->getBaseUrl() . '/'
                . ltrim($this->configuration->getIntrospectionTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $this->configuration->verifyPeer()
                ]
            );
        } catch (\Exception $ex) {
            throw AuthenticationException::notAuthenticated();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            throw AuthenticationException::notAuthenticated();
        }
        $content = json_decode($response->getContent(false), true);
        if (array_key_exists('error', $content) || empty($content)) {
            //add logs

            //throw exception
            throw AuthenticationException::notAuthenticated();
        }
        $this->userInformations = $content;
    }

    private function sendRequestForUserInformationOrFail(): void
    {
        $headers = [
            'Authorization' => "Bearer " . trim($this->providerToken->getToken())
        ];
        try {
            $response = $this->client->request(
                'GET',
                $this->configuration->getBaseUrl() . '/'
                . ltrim($this->configuration->getUserInformationEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'verify_peer' => $this->configuration->verifyPeer()
                ]
            );
        } catch (\Exception $ex) {
            throw AuthenticationException::notAuthenticated();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            throw AuthenticationException::notAuthenticated();
        }
        $content = json_decode($response->getContent(false), true);
        if (array_key_exists('error', $content) || empty($content)) {
            //add logs

            //throw exception
            throw AuthenticationException::notAuthenticated();
        }
        $this->userInformations = $content;
    }

    private function verifyThatClientIsAllowedToConnectOrFail(): void
    {
        foreach ($this->configuration->getBlacklistClientAddresses() as $blackListedAddress) {
            if ($blackListedAddress !== "" && preg_match('/' . $blackListedAddress . '/', $_SERVER['REMOTE_ADDR'])) {
                throw AuthenticationException::notAuthenticated();
            }
        }

        //Si les whitelist c'est rempli et que j'ai pas mon ip dedans
        foreach ($this->configuration->getTrustedClientAddresses() as $trustedClientAddress) {
            if (
                $trustedClientAddress !== ""
                && preg_match('/' . $trustedClientAddress . '/', $_SERVER['REMOTE_ADDR'])
            ) {
                throw AuthenticationException::notAuthenticated();
            }
        }
    }

    private function getUsernameFromLoginClaim(): string
    {
        $loginClaim = $this->configuration->getLoginClaim() ?? OpenIdConfiguration::DEFAULT_LOGIN_GLAIM;
        if (
            !array_key_exists($loginClaim, $this->userInformations)
            && $this->configuration->getUserInformationEndpoint() !== null
        ) {
            $this->sendRequestForUserInformationOrFail();
        }
        if (!array_key_exists($loginClaim, $this->userInformations)) {
            throw AuthenticationException::notAuthenticated();
        }
        return $this->userInformations[$loginClaim];
    }
}
