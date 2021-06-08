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

namespace Security\Domain\Authentication\Interfaces;

use Security\Domain\Authentication\Model\ProviderConfiguration;
use Security\Domain\Authentication\Interfaces\ProviderInterface;

interface ProviderServiceInterface
{
    /**
     * Find providers configurations
     *
     * @return ProviderConfiguration[]
     * @throws ProviderServiceException
     */
    public function findProvidersConfigurations(): array;

    /**
     * Find a provider by configuration id.
     *
     * @param int $providerConfigurationId
     * @return ProviderInterface|null
     * @throws ProviderServiceException
     */
    public function findProviderByConfigurationId(int $providerConfigurationId): ?ProviderInterface;

    /**
     * Find a provider by the configuration name.
     *
     * @param string $providerConfigurationName
     * @return ProviderInterface|null
     * @throws ProviderServiceException
     */
    public function findProviderByConfigurationName(string $providerConfigurationName): ?ProviderInterface;

    /**
     * @param string $providerConfigurationName
     * @return ProviderConfiguration|null
     * @throws ProviderServiceException
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration;

    /**
     * @param string $sessionToken
     * @return ProviderInterface|null
     * @throws ProviderServiceException
     */
    public function findProviderBySession(string $sessionToken): ?ProviderInterface;
}
