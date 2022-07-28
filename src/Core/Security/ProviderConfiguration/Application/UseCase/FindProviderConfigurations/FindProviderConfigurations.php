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

use Core\Application\Common\UseCase\ErrorResponse;
use Core\Security\ProviderConfiguration\Application\Repository\ReadProviderConfigurationsRepositoryInterface;
use Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\ProviderResponse\{
    ProviderResponseInterface
};
use Centreon\Infrastructure\Service\Exception\NotFoundException;

class FindProviderConfigurations
{
    /**
     * @var ReadProviderConfigurationsRepositoryInterface[]
     */
    private array $providerRepositories;

    /**
     * @var ProviderResponseInterface[]
     */
    private array $providerResponses;

    /**
     * @param \Traversable<ReadProviderConfigurationsRepositoryInterface> $providerRepositories
     * @param \Traversable<ProviderResponseInterface> $providerResponses
     */
    public function __construct(
        \Traversable $providerRepositories,
        \Traversable $providerResponses,
    ) {
        if (iterator_count($providerRepositories) === 0) {
            throw new NotFoundException(_('No provider repositories could be found'));
        }
        $this->providerRepositories = iterator_to_array($providerRepositories);

        if (iterator_count($providerResponses) === 0) {
            throw new NotFoundException(_('No provider responses could be found'));
        }
        $this->providerResponses = iterator_to_array($providerResponses);
    }

    /**
     * @param FindProviderConfigurationsPresenterInterface $presenter
     */
    public function __invoke(FindProviderConfigurationsPresenterInterface $presenter): void
    {
        $configurations = [];

        try {
            foreach ($this->providerRepositories as $providerRepository) {
                $configurations = [
                    ...$configurations,
                    ...$providerRepository->findConfigurations()
                ];
            }
        } catch (\Throwable $ex) {
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
