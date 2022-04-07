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
use CentreonUserLog;
use Core\Domain\Security\Authentication\SSOAuthenticationException;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Domain\Security\ProviderConfiguration\OpenId\Exceptions\OpenIdConfigurationException;
use Pimple\Container;
use Security\Domain\Authentication\Interfaces\ProviderConfigurationInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

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
    private \Centreon $legacySession;

    /**
     * @var CentreonUserLog
     */
    private CentreonUserLog $centreonLog;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(
        private HttpClientInterface $client,
        private UrlGeneratorInterface $router,
        private ContactServiceInterface $contactService,
        private Container $dependencyInjector,
    ) {
        $pearDB = $this->dependencyInjector['configuration_db'];
        $this->centreonLog = new CentreonUserLog(-1, $pearDB);
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
     * {@inheritDoc}
     * @throws SSOAuthenticationException
     * @throws OpenIdConfigurationException
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

        $response = $this->sendRequestToTokenEndpoint($data);

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            $this->logExceptionInLoginLogFile(
                "Unable to get Refresh Token Information: %s, message: %s",
                SSOAuthenticationException::requestForRefreshTokenFail()
            );
            throw SSOAuthenticationException::requestForRefreshTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorInLoginLogFile('Refresh Token Info:', $content);
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }
        $this->logAuthenticationInfoInLoginLogFile('Token Access Information:', $content);
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
     * @throws SSOAuthenticationException
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

        $response = $this->sendRequestToTokenEndpoint($data);

        // Get the status code and throw an Exception if not a 200
        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            $this->logExceptionInLoginLogFile(
                "Unable to get Token Access Information: %s, message: %s",
                SSOAuthenticationException::requestForConnectionTokenFail()
            );
            throw SSOAuthenticationException::requestForConnectionTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorInLoginLogFile('Connection Token Info: ', $content);
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }
        $this->logAuthenticationInfoInLoginLogFile('Token Access Information:', $content);
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
     * @throws SSOAuthenticationException
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
            $this->logExceptionInLoginLogFile("Unable to get Introspection Information: %s, message: %s", $e);
            $this->error(sprintf(
                "[Error] Unable to get Token Introspection Information:, message: %s",
                $e->getMessage()
            ));
            throw SSOAuthenticationException::requestForIntrospectionTokenFail();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            $this->logExceptionInLoginLogFile(
                "Unable to get Introspection Information: %s, message: %s",
                SSOAuthenticationException::requestForIntrospectionTokenFail()
            );
            throw SSOAuthenticationException::requestForIntrospectionTokenFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorInLoginLogFile('Introspection Token Info: ', $content);
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider(OpenIdConfiguration::NAME);
        }
        $this->logAuthenticationInfoInLoginLogFile('Token Introspection Information: ', $content);
        $this->info('Introspection token information found');
        $this->userInformations = $content;
    }

    /**
     * Send a request to get user information.
     * @throws SSOAuthenticationException
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
            throw SSOAuthenticationException::requestForUserInformationFail();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            $this->logExceptionInLoginLogFile(
                "Unable to get User Information: %s, message: %s",
                SSOAuthenticationException::requestForUserInformationFail()
            );
            throw SSOAuthenticationException::requestForUserInformationFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorInLoginLogFile('User Information Info: ', $content);
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
     * @throws SSOAuthenticationException
     */
    private function verifyThatClientIsAllowedToConnectOrFail(string $clientIp): void
    {
        $this->info('Check Client IP from blacklist/whitelist addresses');
        foreach ($this->configuration->getBlacklistClientAddresses() as $blackListedAddress) {
            if ($blackListedAddress !== "" && preg_match('/' . $blackListedAddress . '/', $clientIp)) {
                $this->error('IP Blacklisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSOAuthenticationException::blackListedClient();
            }
        }

        foreach ($this->configuration->getTrustedClientAddresses() as $trustedClientAddress) {
            if (
                $trustedClientAddress !== ""
                && preg_match('/' . $trustedClientAddress . '/', $clientIp)
            ) {
                $this->error('IP not  Whitelisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSOAuthenticationException::notWhiteListedClient();
            }
        }
    }

    /**
     * Return username from login claim.
     *
     * @return string
     * @throws SSOAuthenticationException
     */
    private function getUsernameFromLoginClaim(): string
    {
        $loginClaim = !empty($this->configuration->getLoginClaim())
            ? $this->configuration->getLoginClaim()
            : OpenIdConfiguration::DEFAULT_LOGIN_GLAIM;
        if (
            !array_key_exists($loginClaim, $this->userInformations)
            && $this->configuration->getUserInformationEndpoint() !== null
        ) {
            $this->sendRequestForUserInformationOrFail();
        }
        if (!array_key_exists($loginClaim, $this->userInformations)) {
            $this->centreonLog->insertLog(
                CentreonUserLog::TYPE_LOGIN,
                "[Openid] [Error] Unable to get login from claim: " . $loginClaim
            );
            $this->error('Login Claim not found', ['login_claim' => $loginClaim]);
            throw SSOAuthenticationException::loginClaimNotFound(OpenIdConfiguration::NAME, $loginClaim);
        }
        return $this->userInformations[$loginClaim];
    }

    /**
     * Define authentication type based on configuration
     *
     * @param array<string,mixed> $data
     * @return ResponseInterface
     * @throws SSOAuthenticationException
     */
    private function sendRequestToTokenEndpoint(array $data): ResponseInterface
    {
        $headers = [
            'Content-Type' => "application/x-www-form-urlencoded"
        ];

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
            return $this->client->request(
                'POST',
                $this->configuration->getBaseUrl() . '/' . ltrim($this->configuration->getTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $this->configuration->verifyPeer()
                ]
            );
        } catch (\Exception $e) {
            $this->logExceptionInLoginLogFile('Unable to get Token Access Information: %s, message: %s', $e);
            if (array_key_exists('refresh_token', $data)) {
                $this->error(
                    sprintf("[Error] Unable to get Token Refresh Information:, message: %s", $e->getMessage())
                );
                throw SSOAuthenticationException::requestForRefreshTokenFail();
            } else {
                $this->error(
                    sprintf("[Error] Unable to get Token Access Information:, message: %s", $e->getMessage())
                );
                throw SSOAuthenticationException::requestForConnectionTokenFail();
            }
        }
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

    /**
     * Log error in login.log file
     *
     * @param string $message
     * @param array<string,string> $content
     */
    private function logErrorInLoginLogFile(string $message, array $content): void
    {
        if (array_key_exists('error', $content)) {
            $this->centreonLog->insertLog(
                CentreonUserLog::TYPE_LOGIN,
                "[Openid] [Error] $message" . json_encode($content)
            );
        }
    }

    /**
     * Log Authentication informations in login.log file
     *
     * @param string $message
     * @param array<string,string> $content
     */
    private function logAuthenticationInfoInLoginLogFile(string $message, array $content): void
    {
        if (isset($content['jti'])) {
            $content['jti'] = substr($content['jti'], -10);
        }
        if (isset($content['access_token'])) {
            $content['access_token'] = substr($content['access_token'], -10);
        }
        if (isset($content['refresh_token'])) {
            $content['refresh_token'] = substr($content['refresh_token'], -10);
        }
        $this->centreonLog->insertLog(
            CentreonUserLog::TYPE_LOGIN,
            "[Openid] [Debug] $message " . json_encode($content)
        );
    }

    /**
     * Log Exception in login.log file
     *
     * @param string $message
     * @param \Exception $e
     */
    private function logExceptionInLoginLogFile(string $message, \Exception $e): void
    {
        $this->centreonLog->insertLog(
            CentreonUserLog::TYPE_LOGIN,
            sprintf(
                "[Openid] [Error] $message",
                get_class($e),
                $e->getMessage()
            )
        );
    }
}
