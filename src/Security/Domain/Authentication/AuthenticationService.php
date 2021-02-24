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

use Centreon\Domain\Contact\Interfaces\ContactInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Model\ProviderFactory;
use Security\Domain\Authentication\Model\ProviderToken;

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
     * AuthenticationService constructor.
     *
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        ProviderFactory $providerFactory
    ) {
        $this->repository = $authenticationRepository;
        $this->providerFactory = $providerFactory;
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
            throw new \InvalidArgumentException(
                sprintf(_('Provider configuration (%) not found'), $providerConfigurationName)
            );
        }
        $this->repository->addAuthenticationTokens(
            $sessionToken,
            $providerConfiguration->getId(),
            $contact->getId(),
            $providerToken,
            $providerRefreshToken
        );
    }

    public function deleteSession(string $sessionToken): void
    {
        $this->repository->deleteSession($sessionToken);
    }

    /**
     * Check if the session is valid.
     *
     * @param string $sessionToken Session token
     * @param ProviderInterface $provider Provider that will be used to refresh the token if necessary
     * @return bool
     */
    public function hasValidSession(string $sessionToken, ProviderInterface $provider): bool
    {
        $authenticationToken = $this->repository->findAuthenticationTokenBySessionToken($sessionToken);
        if ($authenticationToken === null) {
            return false;
        }
        if ($authenticationToken->getProviderToken()->isExpired()) {
            if ($provider->canRefreshToken() && !$authenticationToken->getProviderRefreshToken()->isExpired()) {
                $newAuthenticationTokens = $provider->refreshToken($authenticationToken);
                if ($newAuthenticationTokens !== null && !$newAuthenticationTokens->getProviderToken()->isExpired()) {
                    $this->updateAuthenticationToken($newAuthenticationTokens);
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
    public function findProviderBySession(string $sessionToken): ?ProviderInterface
    {
        $authenticationToken = $this->repository->findAuthenticationTokenBySessionToken($sessionToken);
        if ($authenticationToken === null) {
            return null;
        }
        return $this->findProviderByConfigurationId($authenticationToken->getConfigurationProviderId());
    }

    /**
     * @inheritDoc
     */
    public function findProviderConfigurationByConfigurationName(string $providerConfigurationName): ?ProviderConfiguration
    {
        return $this->repository->findProviderConfigurationByConfigurationName($providerConfigurationName);
    }


    /**
     * @param string $sessionToken
     * @return AuthenticationTokens|null
     */
    public function findAuthenticationTokenBySessionToken(string $sessionToken): ?AuthenticationTokens
    {
        return $this->repository->findAuthenticationTokenBySessionToken($sessionToken);
    }

    /**
     * @inheritDoc
     */
    public function updateAuthenticationToken(AuthenticationTokens $authenticationTokens): void
    {
        $this->repository->updateAuthenticationTokens($authenticationTokens);
    }
}