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

namespace Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations;

use Centreon\Domain\Log\LoggerTrait;
use Core\Application\Common\UseCase\ErrorResponse;
use Core\Security\ProviderConfiguration\Application\Repository\ReadConfigurationRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\Repository\ReadProviderConfigurationsRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\ProviderResponse\{
    ProviderResponseInterface
};

class FindProviderConfigurations
{
    use LoggerTrait;

    /**
     * @var ProviderResponseInterface[]
     */
    private array $providerResponses;

    /**
     * @param \Traversable<ProviderResponseInterface> $providerResponses
     * @param ReadConfigurationRepositoryInterface $readConfigurationFactory
     */
    public function __construct(
        \Traversable $providerResponses,
        private ReadConfigurationRepositoryInterface $readConfigurationFactory
    ) {
        $this->providerResponses = iterator_to_array($providerResponses);
    }

    /**
     * @param FindProviderConfigurationsPresenterInterface $presenter
     */
    public function __invoke(FindProviderConfigurationsPresenterInterface $presenter): void
    {
        try {
            $configurations = $this->readConfigurationFactory->findConfigurations();
        } catch (\Throwable $ex) {
            $this->error($ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            $presenter->setResponseStatus(new ErrorResponse($ex->getMessage()));
            return;
        }

        /**
         * match configuration type and response type to bind automatically corresponding configuration and response.
         * e.g configuration type 'local' will match response type 'local',
         * LocalProviderResponse::create will take LocalConfiguration.
         */
        $responses = [];
        foreach ($configurations as $configuration) {
            foreach ($this->providerResponses as $providerResponse) {
                if ($configuration->getType() === $providerResponse->getType()) {
                    $responses[] = $providerResponse->create($configuration);
                }
            }
        }

        $presenter->present($responses);
    }
}
