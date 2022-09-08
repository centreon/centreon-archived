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

namespace Core\Security\Authentication\Infrastructure\Provider;

use Core\Security\Authentication\Application\Provider\ProviderAuthenticationFactoryInterface;
use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Security\Domain\Authentication\Exceptions\ProviderException;

class ProviderAuthenticationFactory implements ProviderAuthenticationFactoryInterface
{
    /**
     * @param Local $local
     * @param OpenId $openId
     * @param WebSSO $webSSO
     * @param ReadConfigurationRepositoryInterface $readConfigurationRepository
     */
    public function __construct(
        private Local $local,
        private OpenId $openId,
        private WebSSO $webSSO,
        private ReadConfigurationRepositoryInterface $readConfigurationRepository
    ) {
    }

    /**
     * @param string $providerName
     * @return ProviderAuthenticationInterface
     * @throws ProviderException
     */
    public function create(string $providerName): ProviderAuthenticationInterface
    {
        $provider = match ($providerName) {
            Provider::LOCAL => $this->local,
            Provider::OPENID => $this->openId,
            Provider::WEB_SSO => $this->webSSO,
            default => throw ProviderException::providerConfigurationNotFound($providerName)
        };

        $provider->setConfiguration($this->readConfigurationRepository->getConfigurationByName($providerName));

        return $provider;
    }
}
