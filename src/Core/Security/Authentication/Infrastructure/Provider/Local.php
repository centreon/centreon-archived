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
 * See the License for the spceific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */

declare(strict_types=1);

namespace Core\Security\Authentication\Infrastructure\Provider;

use Centreon\Domain\Authentication\Exception\AuthenticationException as LegacyAuthenticationException;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Centreon\Domain\Contact\Interfaces\ContactServiceInterface;
use Centreon\Domain\Log\LoggerTrait;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\Authentication\Application\UseCase\Login\LoginRequest;
use Core\Security\Authentication\Domain\Model\AuthenticationTokens;
use Core\Security\Authentication\Domain\Model\NewProviderToken;
use Core\Security\Authentication\Domain\Model\ProviderToken;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Security\Domain\Authentication\Interfaces\LocalProviderInterface;
use Security\Domain\Authentication\Model\LocalProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Throwable;

class Local implements ProviderAuthenticationInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    private string $username;

    /**
     * @param LocalProviderInterface $provider
     * @param SessionInterface $session
     * @param ContactServiceInterface $contactService
     */
    public function __construct(
        private LocalProviderInterface $provider,
        private SessionInterface $session,
        private ContactServiceInterface $contactService
    ) {
    }

    /**
     * @param LoginRequest $request
     * @throws Throwable
     */
    public function authenticateOrFail(LoginRequest $request): void
    {
        $this->debug(
            '[AUTHENTICATE] Authentication using provider',
            ['provider_name' => LocalProvider::NAME]
        );

        $this->provider->authenticateOrFail([
            'login' => $request->username,
            'password' => $request->password
        ]);

        $this->username = $request->username;
    }

    /**
     * @return ContactInterface
     * @throws \Exception
     */
    public function findUserOrFail(): ContactInterface
    {
        $this->info('[AUTHENTICATE] Retrieving user information from provider');
        $user = $this->getAuthenticatedUser();
        if ($user === null) {
            $this->critical(
                '[AUTHENTICATE] No contact could be found from provider',
                ['provider_name' => $this->provider->getConfiguration()->getName()]
            );
            throw LegacyAuthenticationException::userNotFound();
        }

        return $user;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function isAutoImportEnabled(): bool
    {
        return $this->provider->canCreateUser();
    }

    /**
     * @throws LegacyAuthenticationException
     * @throws \Exception
     */
    public function importUser(): void
    {
        throw new \Exception("Feature not available for Local provider");
    }

    /**
     * Update user in data storage
     */
    public function updateUser(): void
    {
        $this->contactService->updateUser($this->provider->getUser());
    }

    /**
     * @return \Centreon
     */
    public function getLegacySession(): \Centreon
    {
        return $this->provider->getLegacySession();
    }

    /**
     * @return NewProviderToken
     */
    public function getProviderToken(?string $token = null): NewProviderToken
    {
        return $this->provider->getProviderToken($token);
    }

    /**
     * @return NewProviderToken|null
     */
    public function getProviderRefreshToken(): ?NewProviderToken
    {
        return $this->provider->getProviderRefreshToken($this->session->getId());
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->provider->getConfiguration();
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration): void
    {
        $this->provider->setConfiguration($configuration);
    }

    /**
     * @return bool
     */
    public function isUpdateACLSupported(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function canRefreshToken(): bool
    {
        return $this->provider->canRefreshToken();
    }

    /**
     * @param AuthenticationTokens $authenticationTokens
     * @return AuthenticationTokens|null
     */
    public function refreshToken(AuthenticationTokens $authenticationTokens): ?AuthenticationTokens
    {
        return $this->provider->refreshToken($authenticationTokens);
    }

    /**
     * @return ContactInterface|null
     */
    public function getAuthenticatedUser(): ?ContactInterface
    {
        return $this->provider->getUser();
    }

    public function getUserInformation(): array
    {
        return [];
    }

    public function getIdTokenPayload(): array
    {
        return [];
    }
}
