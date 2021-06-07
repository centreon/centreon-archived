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
use Security\Domain\Authentication\Model\AuthenticationTokens;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Exceptions\AuthenticationServiceException;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\AuthenticationServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;
use Security\Domain\Authentication\Interfaces\LocalProviderRepositoryInterface;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;

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
     * @var LocalProviderRepositoryInterface
     */
    private $localProviderRepository;

    /**
     * @var ProviderServiceInterface
     */
    private $providerService;

    /**
     * AuthenticationService constructor.
     *
     * @param AuthenticationRepositoryInterface $authenticationRepository
     * @param ProviderServiceInterface $providerService
     * @param LocalProviderRepositoryInterface $localProviderRepository
     */
    public function __construct(
        AuthenticationRepositoryInterface $authenticationRepository,
        ProviderServiceInterface $providerService,
        LocalProviderRepositoryInterface $localProviderRepository
    ) {
        $this->repository = $authenticationRepository;
        $this->localProviderRepository = $localProviderRepository;
        $this->providerService = $providerService;
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

        $provider = $this->providerService->findProviderByConfigurationId(
            $authenticationTokens->getConfigurationProviderId()
        );

        if ($provider === null) {
            throw ProviderServiceException::providerNotFound();
        }

        if ($authenticationTokens->getProviderToken()->isExpired()) {
            if (
                !$provider->canRefreshToken()
                || ($authenticationTokens->getProviderRefreshToken() !== null
                && $authenticationTokens->getProviderRefreshToken()->isExpired())
            ) {
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
        $providerConfiguration = $this->providerService->findProviderConfigurationByConfigurationName(
            $providerConfigurationName
        );
        if ($providerConfiguration === null || ($providerConfigurationId = $providerConfiguration->getId()) === null) {
            throw ProviderServiceException::providerConfigurationNotFound($providerConfigurationName);
        }
        $this->repository->addAuthenticationTokens(
            $sessionToken,
            $providerConfigurationId,
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
        $providerConfiguration = $this->providerService->findProviderConfigurationByConfigurationName('local');
        if ($providerConfiguration === null || ($providerConfigurationId = $providerConfiguration->getId()) === null) {
            throw ProviderServiceException::providerConfigurationNotFound('local');
        }
        $this->repository->addAPIAuthenticationTokens(
            $token,
            $providerConfigurationId,
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
     * @param string $token Session token
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
     * @param string $token
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
