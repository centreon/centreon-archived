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

namespace Core\Security\Authentication\Domain\Provider;

use Centreon;
use Exception;
use Throwable;
use DateInterval;
use CentreonUserLog;
use Pimple\Container;
use Centreon\Domain\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\Response;
use Core\Domain\Configuration\User\Model\NewUser;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Security\Domain\Authentication\Interfaces\OpenIdProviderInterface;
use Core\Security\Authentication\Domain\Exception\AuthenticationException;
use Core\Security\Authentication\Domain\Exception\SSOAuthenticationException;
use Core\Application\Configuration\User\Repository\WriteUserRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;
use Core\Security\Authentication\Domain\Exception\AuthenticationConditionsException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\AuthenticationConditions;
use Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions\OpenIdConfigurationException;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\Endpoint;

class OpenIdProvider implements OpenIdProviderInterface
{
    use LoggerTrait;

    public const NAME = 'openid';

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @var NewProviderToken
     */
    private NewProviderToken $providerToken;

    /**
     * @var NewProviderToken|null
     */
    private ?NewProviderToken $refreshToken = null;

    /**
     * @var array<string,mixed>
     */
    private array $userInformations = [];

    /**
     * @var string
     */
    private string $username;

    /**
     * @var Centreon
     */
    private Centreon $legacySession;

    /**
     * @var CentreonUserLog
     */
    private CentreonUserLog $centreonLog;

    /**
     * Array of information store in id_token JWT Payload
     *
     * @var array<string,mixed>
     */
    private array $idTokenPayload = [];

    /**
     * Content of the connexion token response.
     *
     * @var array<string,mixed>
     */
    private array $connectionTokenResponseContent = [];

    /**
     * @param HttpClientInterface $client
     * @param UrlGeneratorInterface $router
     * @param ContactServiceInterface $contactService
     * @param Container $dependencyInjector
     * @param WriteUserRepositoryInterface $userRepository
     */
    public function __construct(
        private HttpClientInterface $client,
        private UrlGeneratorInterface $router,
        private ContactServiceInterface $contactService,
        private Container $dependencyInjector,
        private WriteUserRepositoryInterface $userRepository,
    ) {
        $pearDB = $this->dependencyInjector['configuration_db'];
        $this->centreonLog = new CentreonUserLog(-1, $pearDB);
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @inheritDoc
     */
    public function getProviderToken(): NewProviderToken
    {
        return $this->providerToken;
    }

    /**
     * @inheritDoc
     */
    public function getProviderRefreshToken(): ?NewProviderToken
    {
        return $this->refreshToken;
    }

    /**
     * @inheritDoc
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function canCreateUser(): bool
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        return $customConfiguration->isAutoImportEnabled();
    }

    /**
     * {@inheritDoc}
     * @throws SSOAuthenticationException
     */
    public function createUser(): void
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        $this->info('Auto import starting...', [
            "user" => $this->username
        ]);
        $this->validateAutoImportAttributesOrFail();

        $user = new NewUser(
            $this->username,
            $this->userInformations[$customConfiguration->getUserNameBindAttribute()],
            $this->userInformations[$customConfiguration->getEmailBindAttribute()],
        );
        $user->setContactTemplate($customConfiguration->getContactTemplate());
        $this->userRepository->create($user);
        $this->info('Auto import complete', [
            "user_alias" => $this->username,
            "user_fullname" => $this->userInformations[
                $customConfiguration->getUserNameBindAttribute()
            ],
            "user_email" => $this->userInformations[
                $customConfiguration->getEmailBindAttribute()
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getLegacySession(): Centreon
    {
        return $this->legacySession;
    }

    /**
     * @inheritDoc
     */
    public function setLegacySession(Centreon $legacySession): void
    {
        $this->legacySession = $legacySession;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->configuration->getName();
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
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();

        $this->info('Start authenticating user...', [
            'provider' => $this->configuration->getName()
        ]);
        if (empty($authorizationCode)) {
            $this->error(
                'No authorization code returned from external provider',
                [
                    'provider' => $this->configuration->getName()
                ]
            );
            throw SSOAuthenticationException::noAuthorizationCode($this->configuration->getName());
        }

        if ($customConfiguration->getTokenEndpoint() === null) {
            throw OpenIdConfigurationException::missingTokenEndpoint();
        }
        if (
            $customConfiguration->getIntrospectionTokenEndpoint() === null
            && $customConfiguration->getUserInformationEndpoint() === null
        ) {
            throw OpenIdConfigurationException::missingInformationEndpoint();
        }

        $this->sendRequestForConnectionTokenOrFail($authorizationCode);
        $this->createAuthenticationTokens();
        $this->verifyThatClientIsAllowedToConnectOrFail($clientIp);
        if ($this->providerToken->isExpired() && $this->refreshToken->isExpired()) {
            throw SSOAuthenticationException::tokensExpired($this->configuration->getName());
        }
        if ($customConfiguration->getIntrospectionTokenEndpoint() !== null) {
            $this->getUserInformationFromIntrospectionEndpoint();
        }

        if (array_key_exists("id_token", $this->connectionTokenResponseContent)) {
            $this->idTokenPayload = $this->extractTokenPayload($this->connectionTokenResponseContent["id_token"]);
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
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();

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
            "scope" => !empty($customConfiguration->getConnectionScopes())
                ? implode(' ', $customConfiguration->getConnectionScopes())
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
            throw SSOAuthenticationException::errorFromExternalProvider($this->configuration->getName());
        }
        $this->logAuthenticationDebug('Token Access Information:', $content);
        $creationDate = new \DateTimeImmutable();
        $providerTokenExpiration =
            (new \DateTimeImmutable())->add(new DateInterval('PT' . $content ['expires_in'] . 'S'));

        /** @var ProviderToken $providerToken */
        $providerToken =  $authenticationTokens->getProviderToken();
        $this->providerToken =  new ProviderToken(
            $providerToken->getId(),
            $content['access_token'],
            $creationDate,
            $providerTokenExpiration
        );
        if (array_key_exists('refresh_token', $content)) {
            $expirationDelay = $content['expires_in'] + 3600;
            if (array_key_exists('refresh_expires_in', $content)) {
                $expirationDelay = $content['refresh_expires_in'];
            }
            $refreshTokenExpiration = (new \DateTimeImmutable())
                ->add(new DateInterval('PT' . $expirationDelay . 'S'));
            $this->refreshToken = new NewProviderToken(
                $content['refresh_token'],
                $creationDate,
                $refreshTokenExpiration
            );
        }

        return new AuthenticationTokens(
            $authenticationTokens->getUserId(),
            $authenticationTokens->getConfigurationProviderId(),
            $authenticationTokens->getSessionToken(),
            $this->providerToken,
            $this->refreshToken
        );
    }

    /**
     * @inheritDoc
     */
    public function getUserInformation(): array
    {
        return $this->userInformations;
    }

    /**
     * @inheritDoc
     */
    public function getIdTokenPayload(): array
    {
        return $this->idTokenPayload;
    }

    /**
     * Extract Payload from JWT token
     *
     * @param string $token
     * @return array<string,mixed>
     * @throws SSOAuthenticationException
     */
    private function extractTokenPayload(string $token): array
    {
        try {
            $tokenParts = explode(".", $token);
            return json_decode(base64_decode($tokenParts[1]), true);
        } catch (Throwable $ex) {
            $this->error(
                SSOAuthenticationException::unableToDecodeIdToken()->getMessage(),
                ['trace' => $ex->getTraceAsString()]
            );
            throw SSOAuthenticationException::unableToDecodeIdToken();
        }
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
            'centreon_security_authentication_login_openid',
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
            throw SSOAuthenticationException::errorFromExternalProvider($this->configuration->getName());
        }
        $this->logAuthenticationDebug('Token Access Information:', $content);
        $this->connectionTokenResponseContent = $content;
    }

    /**
     * Create Authentication Tokens
     */
    private function createAuthenticationTokens(): void
    {
        $creationDate = new \DateTimeImmutable();
        $expirationDelay = array_key_exists('expires_in', $this->connectionTokenResponseContent)
            ? $this->connectionTokenResponseContent['expires_in']
            : 3600;
        $providerTokenExpiration = (new \DateTimeImmutable())->add(
            new DateInterval('PT' . $expirationDelay . 'S')
        );
        $this->providerToken =  new NewProviderToken(
            $this->connectionTokenResponseContent['access_token'],
            $creationDate,
            $providerTokenExpiration
        );
        if (array_key_exists('refresh_token', $this->connectionTokenResponseContent)) {
            $expirationDelay = $this->connectionTokenResponseContent['expires_in'] + 3600;
            if (array_key_exists('refresh_expires_in', $this->connectionTokenResponseContent)) {
                $expirationDelay = $this->connectionTokenResponseContent['refresh_expires_in'];
            }
            $refreshTokenExpiration = (new \DateTimeImmutable())
                ->add(new DateInterval('PT' . $expirationDelay . 'S'));
            $this->refreshToken = new NewProviderToken(
                $this->connectionTokenResponseContent['refresh_token'],
                $creationDate,
                $refreshTokenExpiration
            );
        }
    }

    /**
     * Send a request to get introspection token information.
     * @throws SSOAuthenticationException
     */
    private function getUserInformationFromIntrospectionEndpoint(): void
    {
        $this->userInformations = $this->sendRequestForIntrospectionEndpoint();
    }

    /**
     * Send a request to get introspection token information.
     * @return array<string,mixed>
     * @throws SSOAuthenticationException
     */
    private function sendRequestForIntrospectionEndpoint(): array
    {
        $this->info('Sending request for introspection token information');

        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();

        // Define parameters for the request
        $data = [
            "token" => $this->providerToken->getToken(),
            "client_id" => $customConfiguration->getClientId(),
            "client_secret" => $customConfiguration->getClientSecret()
        ];
        $headers = [
            'Authorization' => 'Bearer ' . trim($this->providerToken->getToken())
        ];
        try {
            $response = $this->client->request(
                'POST',
                $customConfiguration->getBaseUrl() . '/'
                . ltrim($customConfiguration->getIntrospectionTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $customConfiguration->verifyPeer()
                ]
            );
        } catch (Exception $e) {
            $this->logExceptionInLoginLogFile("Unable to get Introspection Information: %s, message: %s", $e);
            $this->error(sprintf(
                "[Error] Unable to get Introspection Token Information:, message: %s",
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
            throw SSOAuthenticationException::errorFromExternalProvider($this->configuration->getName());
        }
        $this->logAuthenticationDebug('Token Introspection Information: ', $content);

        return $content;
    }

    /**
     * Send a request to get user information.
     * @throws SSOAuthenticationException
     */
    private function getUserInformationFromUserInfoEndpoint(): void
    {
        $this->userInformations = $this->sendRequestForUserInformationEndpoint();
    }

    /**
     * Send a request to get user information.
     * @return array<string,mixed>
     * @throws SSOAuthenticationException
     */
    private function sendRequestForUserInformationEndpoint(): array
    {
        $this->info('Send Request for User Information...');

        $headers = [
            'Authorization' => "Bearer " . trim($this->providerToken->getToken())
        ];
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        $url = str_starts_with($customConfiguration->getUserInformationEndpoint(), '/')
            ? $customConfiguration->getBaseUrl() . $customConfiguration->getUserInformationEndpoint()
            : $customConfiguration->getUserInformationEndpoint();
        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => $headers,
                    'verify_peer' => $customConfiguration->verifyPeer()
                ]
            );
        } catch (Exception $ex) {
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
            throw SSOAuthenticationException::errorFromExternalProvider($this->configuration->getName());
        }
        $this->logAuthenticationDebug('User Information: ', $content);

        return $content;
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
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        $authenticationConditions = $customConfiguration->getAuthenticationConditions();
        foreach ($authenticationConditions->getBlacklistClientAddresses() as $blackListedAddress) {
            if ($blackListedAddress !== "" && preg_match('/' . $blackListedAddress . '/', $clientIp)) {
                $this->error('IP Blacklisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSOAuthenticationException::blackListedClient();
            }
        }

        foreach ($authenticationConditions->getTrustedClientAddresses() as $trustedClientAddress) {
            if (
                $trustedClientAddress !== ""
                && preg_match('/' . $trustedClientAddress . '/', $clientIp)
            ) {
                $this->error('IP not  Whitelisted', [ 'ip' => '...' . substr($clientIp, -5)]);
                throw SSOAuthenticationException::notWhiteListedClient();
            }
        }

        $this->validateAuthenticationConditionsOrFail($authenticationConditions);
    }

    /**
     * Return username from login claim.
     *
     * @return string
     * @throws SSOAuthenticationException
     */
    private function getUsernameFromLoginClaim(): string
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        $loginClaim = ! empty($customConfiguration->getLoginClaim())
            ? $customConfiguration->getLoginClaim()
            : CustomConfiguration::DEFAULT_LOGIN_CLAIM;
        if (
            !array_key_exists($loginClaim, $this->userInformations)
            && $customConfiguration->getUserInformationEndpoint() !== null
        ) {
            $this->getUserInformationFromUserInfoEndpoint();
        }
        if (!array_key_exists($loginClaim, $this->userInformations)) {
            $this->centreonLog->insertLog(
                CentreonUserLog::TYPE_LOGIN,
                "[Openid] [Error] Unable to get login from claim: " . $loginClaim
            );
            $this->error('Login Claim not found', ['login_claim' => $loginClaim]);
            throw SSOAuthenticationException::loginClaimNotFound($this->configuration->getName(), $loginClaim);
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
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        if ($customConfiguration->getAuthenticationType() === CustomConfiguration::AUTHENTICATION_BASIC) {
            $headers['Authorization'] = "Basic " . base64_encode(
                $customConfiguration->getClientId() . ":" . $customConfiguration->getClientSecret()
            );
        } else {
            $data["client_id"] = $customConfiguration->getClientId();
            $data["client_secret"] = $customConfiguration->getClientSecret();
        }

        // Send the request to IDP
        try {
            return $this->client->request(
                'POST',
                $customConfiguration->getBaseUrl() . '/' .
                ltrim($customConfiguration->getTokenEndpoint(), '/'),
                [
                    'headers' => $headers,
                    'body' => $data,
                    'verify_peer' => $customConfiguration->verifyPeer()
                ]
            );
        } catch (Exception $e) {
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
     * Validate that auto import attributes are present in user informations from provider
     * @throws SSOAuthenticationException
     */
    private function validateAutoImportAttributesOrFail(): void
    {
        $missingAttributes = [];
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        if (! array_key_exists($customConfiguration->getEmailBindAttribute(), $this->userInformations)) {
            $missingAttributes[] = $customConfiguration->getEmailBindAttribute();
        }
        if (! array_key_exists($customConfiguration->getUserNameBindAttribute(), $this->userInformations)) {
            $missingAttributes[] = $customConfiguration->getUserNameBindAttribute();
        }

        if (! empty($missingAttributes)) {
            $ex = SSOAuthenticationException::autoImportBindAttributeNotFound($missingAttributes);
            $this->logExceptionInLoginLogFile(
                "Some bind attributes can't be found in user information: %s, message: %s",
                $ex
            );
            throw $ex;
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
     * Log Authentication informations
     *
     * @param string $message
     * @param array<string,string> $content
     */
    private function logAuthenticationDebug(string $message, array $content): void
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
        if (isset($content['id_token'])) {
            $content['id_token'] = substr($content['id_token'], -10);
        }
        if (isset($content['provider_token'])) {
            $content['provider_token'] = substr($content['provider_token'], -10);
        }
        $this->centreonLog->insertLog(
            CentreonUserLog::TYPE_LOGIN,
            "[Openid] [Debug] $message " . json_encode($content)
        );
        $this->debug('Authentication informations : ', $content);
    }

    private function logAuthenticationInfo(string $message, array $content): void
    {
        $this->centreonLog->insertLog(
            CentreonUserLog::TYPE_LOGIN,
            "[Openid] [INFO] $message : " . json_encode($content)
        );
        $this->info("$message : ", $content);
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

    /**
     * Validate Authentication Conditions or throw an exception.
     *
     * @param AuthenticationConditions $authenticationConditions
     * @throws OpenIdConfigurationException
     * @throws AuthenticationException
     * @throws AuthenticationConditionsException
     */
    private function validateAuthenticationConditionsOrFail(
        AuthenticationConditions $authenticationConditions
    ): void {
        if ($authenticationConditions->isEnabled()) {
            $conditions = $this->getConditionsFromProvider($authenticationConditions);
            $this->validateAuthenticationConditions($conditions, $authenticationConditions);
        }
    }

    /**
     * Get authentication conditions from Provider.
     *
     * @param AuthenticationConditions $authenticationConditions
     * @return array<string,mixed>
     */
    private function getConditionsFromProvider(
        AuthenticationConditions $authenticationConditions
    ): array {
        $conditionsEndpoint = $authenticationConditions->getEndpoint();
        switch($conditionsEndpoint->getType()) {
            case Endpoint::INTROSPECTION:
                $conditions = $this->sendRequestForIntrospectionEndpoint();
                break;
            case Endpoint::USER_INFORMATION:
                $conditions = $this->sendRequestForUserInformationEndpoint();
                break;
            default:
                $conditions = $this->sendRequestForCustomAuthenticationConditionEndpoint($conditionsEndpoint->getUrl());
                break;
        }

        return $conditions;
    }

    /**
     * Send Request to get conditions for conditions custom endpoint.
     *
     * @param string $customEndpoint
     * @return array<string,mixed>
     */
    private function sendRequestForCustomAuthenticationConditionEndpoint(string $customEndpoint): array
    {
        $this->info('Send Request for authentication conditions...');

        $headers = [
            'Authorization' => "Bearer " . trim($this->providerToken->getToken())
        ];
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $this->configuration->getCustomConfiguration();
        $url = str_starts_with($customEndpoint, '/')
            ? $customConfiguration->getBaseUrl() . $customEndpoint
            : $customEndpoint;
        try {
            $response = $this->client->request(
                'GET',
                $url,
                [
                    'headers' => $headers,
                    'verify_peer' => $customConfiguration->verifyPeer()
                ]
            );
        } catch (Exception $ex) {
            throw SSOAuthenticationException::requestForUserInformationFail();
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            $this->logErrorForInvalidStatusCode($statusCode, Response::HTTP_OK);
            $this->logExceptionInLoginLogFile(
                "Unable to get authentication conditions: %s, message: %s",
                SSOAuthenticationException::requestForCustomAuthenticationConditionsEndpointFail()
            );
            throw SSOAuthenticationException::requestForCustomAuthenticationConditionsEndpointFail();
        }
        $content = json_decode($response->getContent(false), true);
        if (empty($content) || array_key_exists('error', $content)) {
            $this->logErrorInLoginLogFile('Authentication Conditions Info: ', $content);
            $this->logErrorFromExternalProvider($content);
            throw SSOAuthenticationException::errorFromExternalProvider($this->configuration->getName());
        }
        $this->logAuthenticationDebug('Authentication conditions: ', $content);

        return $content;
    }

    /**
     * Validate Authentication conditions or throw an exception.
     *
     * @param array<string,mixed> $conditions
     * @param string $attributePath
     */
    private function validateAuthenticationConditions(
        array $conditions,
        AuthenticationConditions $authenticationConditions
    ): void {
        $authenticationAttributePath = explode(".", $authenticationConditions->getAttributePath());
        foreach($authenticationAttributePath as $attribute) {
            $providerAuthenticationConditions = [];
            if (array_key_exists($attribute, $conditions)) {
                $providerAuthenticationConditions = $conditions[$attribute];
                $conditions = $conditions[$attribute];
            } else {
                break;
            }
        }
        if (is_string($providerAuthenticationConditions)) {
            $providerAuthenticationConditions = explode(",", $providerAuthenticationConditions);
        }

        $this->validateAttributeOrFail(
            $providerAuthenticationConditions,
            $authenticationConditions->getAuthorizedValues()
        );
    }

    /**
     * Undocumented function
     *
     * @param array<mixed> $providerAuthenticationConditions
     * @param string[] $configuredAuthorizedValues
     * @throws AuthenticationConditionsException
     */
    private function validateAttributeOrFail(array $providerAuthenticationConditions, array $configuredAuthorizedValues): void
    {
        //@TODO: Remove this polyfill when php 8.1 is supported
        if (!function_exists("array_is_list")) {
            function array_is_list(array $array): bool
            {
                $i = 0;
                foreach ($array as $k => $v) {
                    if ($k !== $i++) {
                        return false;
                    }
                }
                return true;
            }
        }
        if (array_is_list($providerAuthenticationConditions) === false) {
            $errorMessage = "Invalid Authentication conditions format, array of string expected";
            $this->error($errorMessage, [
                "authentication_condition_from_provider" => $providerAuthenticationConditions
            ]);
            $this->logExceptionInLoginLogFile(
                $errorMessage,
                AuthenticationConditionsException::invalidAuthenticationConditions()
            );
            throw AuthenticationConditionsException::invalidAuthenticationConditions();
        }

        $conditionMatches = array_intersect($providerAuthenticationConditions, $configuredAuthorizedValues);
        if (empty ($conditionMatches)) {
            $errorMessage = "Configured attribute path not found in conditions endpoint";
            $this->error($errorMessage, [
                "configured_authorized_values" => $configuredAuthorizedValues
            ]);
            $this->logExceptionInLoginLogFile($errorMessage, AuthenticationConditionsException::conditionsNotFound());
            throw AuthenticationConditionsException::conditionsNotFound();
        }
        $this->info("Conditions found", ["conditions" => $conditionMatches]);
        $this->logAuthenticationInfo("Conditions found", $conditionMatches);
    }
}
