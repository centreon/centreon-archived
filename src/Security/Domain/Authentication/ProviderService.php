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

use Security\Domain\Authentication\Model\ProviderFactory;
use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Interfaces\ProviderInterface;
use Security\Domain\Authentication\Exceptions\ProviderServiceException;
use Security\Domain\Authentication\Interfaces\ProviderServiceInterface;
use Security\Domain\Authentication\Interfaces\AuthenticationRepositoryInterface;

class ProviderService implements ProviderServiceInterface
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
    public function findProvidersConfigurations(): array
    {
        try {
            return $this->repository->findProvidersConfigurations();
        } catch(\Exception $ex) {
            throw ProviderServiceException::findProvidersConfigurations($ex);
        }
    }

    /**
     * @inheritDoc
     */
    public function findProviderByConfigurationId(int $providerConfigurationId): ?ProviderInterface
    {
        try {
            $providerConfiguration = $this->repository->findProviderConfiguration($providerConfigurationId);
        } catch(\Exception $ex) {
            throw ProviderServiceException::findProvidersConfigurations($ex);
        }
        if ($providerConfiguration === null) {
            return null;
        }
        return $this->providerFactory->create($providerConfiguration);
    }

    /**
     * @inheritDoc
     */
    public function findProviderByConfigurationName(string $providerConfigurationName): ?ProviderInterface
    {
        try {
            $providerConfiguration = $this->repository->findProviderConfigurationByConfigurationName(
                $providerConfigurationName
            );
        } catch(\Exception $ex) {
            throw ProviderServiceException::findProvidersConfigurations($ex);
        }

        if ($providerConfiguration === null) {
            return null;
        }
        return $this->providerFactory->create($providerConfiguration);
    }

    /**
     * @inheritDoc
     */
    public function findProviderBySession(string $token): ?ProviderInterface
    {
        try {
            $authenticationToken = $this->repository->findAuthenticationTokensByToken($token);
        } catch(\Exception $ex) {
            throw ProviderServiceException::authenticationTokensNotFound($ex);
        }
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
        try {
            return $this->repository->findProviderConfigurationByConfigurationName($providerConfigurationName);
        } catch(\Exception $ex) {
            throw ProviderServiceException::findProvidersConfigurations($ex);
        }
    }
}
