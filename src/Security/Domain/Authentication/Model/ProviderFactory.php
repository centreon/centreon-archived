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

use Core\Security\Application\ProviderConfiguration\OpenId\Repository\ReadOpenIdConfigurationRepositoryInterface;
use Core\Security\Application\ProviderConfiguration\WebSSO\Repository\ReadWebSSOConfigurationRepositoryInterface;
use Core\Security\Domain\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;
use Core\Security\Domain\ProviderConfiguration\WebSSO\Model\WebSSOConfiguration;
use Security\Domain\Authentication\Exceptions\ProviderException;
use Security\Domain\Authentication\Interfaces\ProviderInterface;

/**
 * @package Security\Domain\Authentication\Model
 */
class ProviderFactory
{
    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @param \Traversable<ProviderInterface> $providers
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
     * @param ProviderConfiguration $configuration
     * @return ?ProviderInterface
     */
    public function create(ProviderConfiguration $configuration): ?ProviderInterface
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
