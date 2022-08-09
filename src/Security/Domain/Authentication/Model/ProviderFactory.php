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

namespace Security\Domain\Authentication\Model;

use Core\Security\Authentication\Application\Provider\ProviderAuthenticationInterface;
use Core\Security\ProviderConfiguration\Application\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration as OpenIdConfiguration;
use Core\Security\ProviderConfiguration\Domain\WebSSO\Model\WebSSOConfiguration;
use Security\Domain\Authentication\Exceptions\ProviderException;

/**
 * @package Security\Domain\Authentication\Model
 */
class ProviderFactory
{
    /**
     * @var ProviderAuthenticationInterface[]
     */
    private $providers;

    /**
     * @param \Traversable<ProviderAuthenticationInterface> $providers
     * @throws ProviderException
     */
    public function __construct(
        \Traversable $providers,
        private ReadOpenIdConfigurationRepositoryInterface $openIdRepository,
        private ReadWebSSOConfigurationRepositoryInterface $webSSORepository,
    ) {
        if (iterator_count($providers) === 0) {
            throw ProviderException::emptyAuthenticationProvider();
        }
        $this->providers = iterator_to_array($providers);
    }

    /**
     * @param Configuration $configuration
     * @return ?ProviderAuthenticationInterface
     * @throws \Throwable
     */
    public function create(Configuration $configuration): ?ProviderAuthenticationInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->getName() === $configuration->getName()) {
                switch ($configuration->getName()) {
                    case OpenIdConfiguration::NAME:
                        $configuration = $this->openIdRepository->findConfiguration();
                        break;
                    case WebSSOConfiguration::NAME:
                        $configuration = $this->webSSORepository->findConfiguration();
                        break;
                }
                $provider->setConfiguration($configuration);
                return $provider;
            }
        }
        return null;
    }
}
