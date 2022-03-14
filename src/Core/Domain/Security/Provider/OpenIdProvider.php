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

use Centreon\Domain\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Core\Domain\Security\Authentication\SSOAuthenticationException;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;

class OpenIdProvider implements OpenIdProviderInterface
{
    use LoggerTrait;

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
    private array $userInformations = [];

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
     * @inheritDoc
     */
    public function getConfiguration(): OpenIdConfiguration
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public function getProviderToken(): ProviderToken
    {
        return $this->providerToken;
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(): ProviderToken
    {
        return $this->refreshToken;
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(ProviderConfigurationInterface $configuration): void
    {
        if (!is_a($configuration, OpenIdConfiguration::class)) {
            throw new \InvalidArgumentException('Bad provider configuration');
        }
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function canCreateUser(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
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
     * @inheritDoc
     */
    public function authenticateOrFail(?string $authorizationCode, string $clientIp): void
    {
        $this->info('Start authenticating user...', [
            'provider' => OpenIdConfiguration::NAME
        ]);
        if (empty($authorizationCode)) {
            $this->error(
                'No authorization code return from external provider',
                [
                    'provider' => OpenIdConfiguration::NAME
                ]
            );
            throw SSOAuthenticationException::noAuthorizationCode(OpenIdConfiguration::NAME);
        }

        if ($this->configuration->getTokenEndpoint() === null) {
            throw OpenIdConfigurationException::missingTokenEndpoint();
        }
        if (
            $this->configuration->getIntrospectionTokenEndpoint() === null
            && $this->configuration->getUserInformationEndpoint() === null
        ) {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }

        $this->verifyThatClientIsAllowedToConnectOrFail($clientIp);

        $this->sendRequestForConnectionTokenOrFail($authorizationCode);
        if ($this->providerToken->isExpired() && $this->refreshToken->isExpired()) {
            throw SSOAuthenticationException::tokensExpired(OpenIdConfiguration::NAME);
        }
        if ($this->configuration->getIntrospectionTokenEndpoint() !== null) {
            $this->sendRequestForIntrospectionTokenOrFail();
        }

        $this->username = $this->getUsernameFromLoginClaim();
    }

    /**
     * @inheritDoc
     */
    public function getUser(): ?ContactInterface
    {
        $this->info('Searching user : ' . $this->username);
        $user = $this->contactService->findByName($this->username);
        if ($user === null) {
            $user = $this->contactService->findByEmail($this->username);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): AuthenticationTokens
    {
        if ($authenticationTokens->getProviderRefreshToken() === null) {
            throw SSOAuthenticationException::noRefreshToken();
        }
        $this->info(
            'Refreshing token using refresh token',
            [
                'refresh_token' => substr($authenticationTokens->getProviderRefreshToken()->getToken(), -10)
            ]
        );
        // Define parameters for the request
        $data = [
            "grant_type" => "refresh_token",
            "refresh_token" => $authenticationTokens->getProviderRefreshToken()->getToken(),
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
                $this->error(sprintf(
                    "[Error] Unable to get Token Refresh Information:, message: %s",
                    $e->getMessage()
                ));
                throw SSOAuthenticationException::requestForRefreshTokenFail();
        }

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            throw SSOAuthenticationException::requestForRefreshTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }

        $this->info(
            'Access Token return by external provider',
            [
                'provider_token' => '...' . substr($content['access_token'], -10),
                'refresh_token' => '...' . substr($content['refresh_token'], -10),
            ]
        );
        $creationDate = new \DateTime();
        $providerTokenExpiration = (new \DateTime())->add(new \DateInterval('PT' . $content ['expires_in'] . 'S'));
        $refreshTokenExpiration = (new \DateTime())
            ->add(new \DateInterval('PT' . $content ['refresh_expires_in'] . 'S'));
        $this->providerToken =  new ProviderToken(
            $authenticationTokens->getProviderToken()->getId(),
            $content['access_token'],
            $creationDate,
            $providerTokenExpiration
        );
        $this->refreshToken = new ProviderToken(
            $authenticationTokens->getProviderRefreshToken()->getId(),
            $content['refresh_token'],
            $creationDate,
            $refreshTokenExpiration
        );

        return new AuthenticationTokens(
            $authenticationTokens->getUserId(),
            $authenticationTokens->getConfigurationProviderId(),
            $authenticationTokens->getSessionToken(),
            $this->providerToken,
            $this->refreshToken
        );
    }

    /**
     * Get Connection Token from OpenId Provider.
     *
     * @param string $authorizationCode
     */
    private function sendRequestForConnectionTokenOrFail(string $authorizationCode): void
    {
        $this->info('Send request to external provider for connection token...');

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
                $this->error(
                    sprintf("[Error] Unable to get Token Access Information:, message: %s", $e->getMessage())
                );
                throw SSOAuthenticationException::requestForConnectionTokenFail();
        }

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            throw SSOAuthenticationException::requestForConnectionTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }

        $this->info(
            'Access Token return by external provider',
            [
                'provider_token' => '...' . substr($content['access_token'], -10),
                'refresh_token' => '...' . substr($content['refresh_token'], -10),
            ]
        );
        // Create Provider and Refresh Tokens
        $creationDate = new \DateTime();
        $providerTokenExpiration = (new \DateTime())->add(new \DateInterval('PT' . $content['expires_in'] . 'S'));
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
     * Send a request to get introspection token information.
     */
    private function sendRequestForIntrospectionTokenOrFail(): void
    {
        $this->info('Sending request for introspection token information');
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
        } catch (\Exception $e) {
            $this->error(sprintf(
                "[Error] Unable to get Token Introspection Information:, message: %s",
                $e->getMessage()
            ));
            throw SSOAuthenticationException::requestForIntrospectionTokenFail();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            throw SSOAuthenticationException::requestForIntrospectionTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }
        $this->info('Introspection token information found');
        $this->userInformations = $content;
    }

    /**
     * Send a request to get user information.
     */
    private function sendRequestForUserInformationOrFail(): void
    {
        $this->info('Send Request for User Information...');
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
            throw SSoAuthenticationException::requestForUserInformationFail();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            throw SSoAuthenticationException::requestForUserInformationFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }
        $this->info('User information found');
        $this->userInformations = $content;
    }

    /**
     * Validate that Client IP is allowed to connect to external provider.
     *
     * @param string $clientIp
     */
    private function verifyThatClientIsAllowedToConnectOrFail(string $clientIp): void
    {
        $this->info('Check Client IP from blacklist/whitelist addresses');
        foreach ($this->configuration->getBlacklistClientAddresses() as $blackListedAddress) {
            if ($blackListedAddress !== "" && preg_match('/' . $blackListedAddress . '/', $clientIp)) {
                $this->error('IP Blacklisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSoAuthenticationException::blackListedClient();
            }
        }

        foreach ($this->configuration->getTrustedClientAddresses() as $trustedClientAddress) {
            if (
                $trustedClientAddress !== ""
                && preg_match('/' . $trustedClientAddress . '/', $clientIp)
            ) {
                $this->error('IP not  Whitelisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSoAuthenticationException::notWhiteListedClient();
            }
        }
    }

    /**
     * Return username from login claim.
     *
     * @return string
     */
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
            $this->error('Login Claim not found', ['login_claim' => $loginClaim]);
            throw SSOAuthenticationException::loginClaimNotFound(OpenIdConfiguration::NAME, $loginClaim);
        }
        return $this->userInformations[$loginClaim];
    }

    /**
     * Log error when response from external provider contains error or is empty
     *
     * @param array<string,string> $content
     */
    private function logErrorFromExternalProvider(array $content): void
    {
        $this->error(
            'error from external provider :' . (array_key_exists('error', $content)
                ? $content['error']
                : 'No content in response')
        );
    }

    /**
     * Log error when response from external provider has an invalid status code
     *
     * @param integer $codeReceived
     * @param integer $codeExpected
     */
    private function logErrorForInvalidStatusCode(int $codeReceived, int $codeExpected): void
    {
        $this->error(
            sprintf(
                "invalid status code return by external provider, [%d] returned, [%d] expected",
                $codeReceived,
                $codeExpected
            )
        );
    }
}
