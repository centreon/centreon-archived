<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Security\Domain\Authentication;

use Security\Domain\Authentication\Model\ProviderToken;
use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Model\ProviderFactory;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\LocalProviderRepositoryInterface;

/**
 * @package Security\Authentication
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var AuthenticationRepositoryInterface
     */
    private $repository;

    /**
     * @var ProviderFactory
     */
    private $providerFactory;

    /**
     * @var LocalProviderRepositoryInterface
     */
    private $localProviderRepository;

    /**
     * AuthenticationService constructor.
     *
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        ProviderFactory $providerFactory,
        LocalProviderRepositoryInterface $localProviderRepository
    ) {
        $this->repository = $authenticationRepository;
        $this->providerFactory = $providerFactory;
        $this->localProviderRepository = $localProviderRepository;
    }

    /**
     * @inheritDoc
     */
    public function checkToken(string $token): bool
    {
        $authenticationTokens = $this->findAuthenticationTokensByToken($token);
        if ($authenticationTokens === null) {
            throw AuthenticationServiceException::sessionNotFound();
        }

        $provider = $this->findProviderByConfigurationId(
            $authenticationTokens->getConfigurationProviderId()
        );

        if ($provider === null) {
            throw AuthenticationServiceException::providerNotFound();
        }

        if ($authenticationTokens->getProviderToken()->isExpired()) {
            if (!$provider->canRefreshToken() || $authenticationTokens->getProviderRefreshToken()->isExpired()) {
                throw AuthenticationServiceException::sessionExpired();
            }
            $newAuthenticationTokens = $provider->refreshToken($authenticationTokens);
            if ($newAuthenticationTokens === null) {
                throw AuthenticationServiceException::refreshToken();
            }
            $this->updateAuthenticationTokens($newAuthenticationTokens);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function createAuthenticationTokens(
        string $sessionToken,
        string $providerConfigurationName,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $providerConfiguration = $this->findProviderConfigurationByConfigurationName($providerConfigurationName);
        if ($providerConfiguration === null) {
            AuthenticationServiceException::providerConfigurationNotFound($providerConfigurationName);
        }
        $this->repository->addAuthenticationTokens(
            $sessionToken,
            $providerConfiguration->getId(),
            $contact->getId(),
            $providerToken,
            $providerRefreshToken
        );
    }

    /**
     * @inheritDoc
     */
    public function createAPIAuthenticationTokens(
        string $token,
        ContactInterface $contact,
        ProviderToken $providerToken,
        ?ProviderToken $providerRefreshToken
    ): void {
        $providerConfiguration = $this->findProviderConfigurationByConfigurationName('local');
        if ($providerConfiguration === null) {
            AuthenticationServiceException::providerConfigurationNotFound('local');
        }
        $this->repository->addAPIAuthenticationTokens(
            $token,
            $providerConfiguration->getId(),
            $contact->getId(),
            $providerToken,
            $providerRefreshToken
        );
    }

    /**
     * @inheritDoc
     */
    public function deleteSession(string $sessionToken): void
    {
        $this->repository->deleteSession($sessionToken);
    }

    /**
     * @inheritDoc
     */
    public function deleteExpiredAPITokens(): void
    {
        $this->localProviderRepository->deleteExpiredAPITokens();
    }

    /**
     * Check if the session is valid.
     *
     * @param string $sessionToken Session token
     * @param ProviderInterface $provider Provider that will be used to refresh the token if necessary
     * @return bool
     */
    public function hasValidSession(string $token, ProviderInterface $provider): bool
    {
        $authenticationTokens = $this->repository->findAuthenticationTokensByToken($token);
        if ($authenticationTokens === null) {
            return false;
        }
        if ($authenticationTokens->getProviderToken()->isExpired()) {
            if (
                $provider->canRefreshToken()
                && $authenticationTokens->getProviderRefreshToken() !== null
                && !$authenticationTokens->getProviderRefreshToken()->isExpired()
            ) {
                $newAuthenticationTokens = $provider->refreshToken($authenticationTokens);
                if ($newAuthenticationTokens !== null && !$newAuthenticationTokens->getProviderToken()->isExpired()) {
                    $this->updateAuthenticationTokens($newAuthenticationTokens);
                    return true;
                }
            }
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function findProvidersConfigurations(): array
    {
        return $this->repository->findProvidersConfigurations();
    }

    /**
     * @inheritDoc
     */
    public function findProviderByConfigurationId(int $providerConfigurationId): ?ProviderInterface
    {
        $providerConfiguration = $this->repository->findProviderConfiguration(
            $providerConfigurationId
        );
        if ($providerConfiguration !== null) {
            return $this->providerFactory->create($providerConfiguration);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findProviderByConfigurationName(string $providerConfigurationName): ?ProviderInterface
    {
        $providerConfiguration = $this->findProviderConfigurationByConfigurationName(
            $providerConfigurationName
        );

        if ($providerConfiguration === null) {
            AuthenticationServiceException::providerConfigurationNotFound($providerConfigurationName);
        }
        return $this->providerFactory->create($providerConfiguration);
    }

    /**
     * @inheritDoc
     */
    public function findProviderBySession(string $token): ?ProviderInterface
    {
        $authenticationToken = $this->repository->findAuthenticationTokensByToken($token);
        if ($authenticationToken === null) {
            return null;
        }
        return $this->findProviderByConfigurationId($authenticationToken->getConfigurationProviderId());
    }

    /**
     * @inheritDoc
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration {
        return $this->repository->findProviderConfigurationByConfigurationName($providerConfigurationName);
    }


    /**
     * @param string $sessionToken
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokensByToken(string $token): ?AuthenticationTokens
    {
        return $this->repository->findAuthenticationTokensByToken($token);
    }

    /**
     * @inheritDoc
     */
    public function updateAuthenticationTokens(AuthenticationTokens $authenticationTokens): void
    {
        $this->repository->updateAuthenticationTokens($authenticationTokens);
    }
}
