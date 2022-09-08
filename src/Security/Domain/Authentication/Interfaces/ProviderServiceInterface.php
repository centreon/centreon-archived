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

use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Model\ProviderConfiguration;

/**
 * @deprecated
 */
interface ProviderServiceInterface
{
    /**
     * Find a provider by configuration id.
     *
     * @param int $providerConfigurationId
     * @return ProviderAuthenticationInterface|null
     * @throws ProviderException
     */
    public function findProviderByConfigurationId(int $providerConfigurationId): ?ProviderAuthenticationInterface;

    /**
     * Find a provider by the provider name.
     *
     * @param string $providerAuthenticationName
     * @return ProviderAuthenticationInterface|null
     * @throws ProviderException
     */
    public function findProviderByConfigurationName(
        string $providerAuthenticationName
    ): ?ProviderAuthenticationInterface;

    /**
     * @param string $providerConfigurationName
     * @return ProviderConfiguration|null
     * @throws ProviderException
     */
    public function findProviderConfigurationByConfigurationName(
        string $providerConfigurationName
    ): ?ProviderConfiguration;

    /**
     * @param string $sessionToken
     * @return ProviderAuthenticationInterface|null
     * @throws ProviderException
     */
    public function findProviderBySession(string $sessionToken): ?ProviderAuthenticationInterface;
}
