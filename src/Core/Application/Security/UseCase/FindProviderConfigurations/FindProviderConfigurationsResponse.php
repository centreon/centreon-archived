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

namespace Core\Application\Security\UseCase\FindProviderConfigurations;

use Core\Domain\Security\ProviderConfiguration\Local\Model\Configuration as LocalConfiguration;
use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class FindProviderConfigurationsResponse
{
    /**
     * @var array<array<string,mixed>>
     */
    public array $configurations = [];

    /**
     * @param array<LocalConfiguration|OpenIdConfiguration> $configurations
     */
    public function __construct(array $configurations)
    {
        foreach ($configurations as $configuration) {
            switch (true) {
                case $configuration instanceof LocalConfiguration:
                    $this->configurations[] = $this->localConfigurationToArray($configuration);
                    break;
                case $configuration instanceof OpenIdConfiguration:
                    $this->configurations[] = $this->openIdConfigurationToArray($configuration);
                    break;
            }
        }
    }

    /**
     * Converts local provider configuration entity into an array
     *
     * @param LocalConfiguration $configuration
     * @return array<string,mixed>
     */
    private function localConfigurationToArray(LocalConfiguration $configuration): array
    {
        // @todo use model and add isActive & isForced properties
        return [
            'id' => 1,
            'type' => 'local',
            'name' => 'local',
            'authentication_uri' => '/authentication/providers/configurations/local',
            'centreon_base_uri' => '/centreon',
            'is_active' => true,
            'is_forced' => false,
        ];
    }

    /**
     * Converts open id provider configuration entity into an array
     *
     * @param OpenIdConfiguration $configuration
     * @return array<string,mixed>
     */
    private function openIdConfigurationToArray(OpenIdConfiguration $configuration): array
    {
        // @todo create dto instead of array
        return [
            'id' => 2,
            'type' => 'openid',
            'name' => 'openid',
            'base_url' => $configuration->getBaseUrl(),
            'authorization_endpoint' => $configuration->getAuthorizationEndpoint(),
            'client_id' => $configuration->getClientId(),
            'is_active' => $configuration->isActive(),
            'is_forced' => $configuration->isForced(),
        ];
    }
}
