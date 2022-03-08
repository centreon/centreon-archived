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

namespace Core\Infrastructure\Security\Api\FindProviderConfigurations;

use Core\Infrastructure\Common\Api\HttpUrlTrait;
use Core\Application\Common\UseCase\AbstractPresenter;
use Core\Infrastructure\Common\Presenter\PresenterFormatterInterface;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindProviderConfigurationsResponse;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindLocalProviderConfigurationResponse;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindOpenIdProviderConfigurationResponse;
use Core\Infrastructure\Security\Api\FindProviderConfigurations\ProviderPresenter\ProviderPresenterInterface;
use Core\Application\Security\UseCase\FindProviderConfigurations\FindProviderConfigurationsPresenterInterface;

class FindProviderConfigurationsPresenter extends AbstractPresenter implements
    FindProviderConfigurationsPresenterInterface
{
    use HttpUrlTrait;

    /**
     * @var ProviderPresenterInterface[]
     */
    private $providerPresenters;

    /**
     * @param PresenterFormatterInterface $presenterFormatter
     */
    public function __construct(
        \Traversable $presenters, // iterator
        protected PresenterFormatterInterface $presenterFormatter
    ) {
        if (iterator_count($presenters) === 0) {
            throw new \Exception('empty provider presenter');
        }
        $this->providerPresenters = iterator_to_array($presenters);
    }

    /**
     * {@inheritDoc}
     * @param FindProviderConfigurationsResponse $response
     */
    public function present(mixed $data): void
    {
        $formattedResponse = [];

        foreach ($data as $response) {
            foreach ($this->providerPresenters as $presenterProvider) {
                if ($presenterProvider->isValidFor($response)) {
                    $formattedResponse[] = $presenterProvider->format($response);
                }
            }
        }

        $formattedResponse[] = $this->getFormattedLocalConfiguration($response->localProviderConfiguration);

        if ($response->openIdProviderConfiguration) {
            $formattedResponse[] = $this->getFormattedOpenIdConfiguration($response->openIdProviderConfiguration);
        }

        $this->presenterFormatter->present($formattedResponse);
    }

    /**
     * format local provider configuration
     *
     * @param FindLocalProviderConfigurationResponse $configuration
     * @return array<string,mixed>
     */
    private function getFormattedLocalConfiguration(FindLocalProviderConfigurationResponse $configuration): array
    {
        return [
            'id' => $configuration->id,
            'type' => $configuration->type,
            'name' => $configuration->name,
            'authentication_uri' => $configuration->authenticationUri,
            'is_active' => $configuration->isActive,
            'is_forced' => $configuration->isForced,
        ];
    }

    /**
     * format open id provider configuration
     *
     * @param FindOpenIdProviderConfigurationResponse $configuration
     * @return array<string,mixed>
     */
    private function getFormattedOpenIdConfiguration(FindOpenIdProviderConfigurationResponse $configuration): array
    {
        return [
            'id' => $configuration->id,
            'type' => $configuration->type,
            'name' => $configuration->name,
            'authentication_uri' => $configuration->baseUrl . '/' . $configuration->authorizationEndpoint
                . '?'
                . http_build_query(
                    [
                        'client_id' => $configuration->clientId,
                        'response_type' => 'code',
                        'redirect_uri' => $this->getBaseUrl(), // @todo create full authentication endpoint
                        'state' => uniqid(),
                    ],
                ),
            'is_active' => $configuration->isActive,
            'is_forced' => $configuration->isForced,
        ];
    }
}
