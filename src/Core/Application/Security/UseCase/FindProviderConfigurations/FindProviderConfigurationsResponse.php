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
     * @var FindLocalProviderConfigurationResponse
     */
    public FindLocalProviderConfigurationResponse $localProviderConfiguration;

    /**
     * @var FindOpenIdProviderConfigurationResponse|null
     */
    public ?FindOpenIdProviderConfigurationResponse $openIdProviderConfiguration = null;

    /**
     * @param LocalConfiguration $localConfiguration
     * @param OpenIdConfiguration|null $openIdConfiguration
     */
    public function __construct(
        LocalConfiguration $localConfiguration,
        ?OpenIdConfiguration $openIdConfiguration,
    ) {
        $this->localProviderConfiguration = new FindLocalProviderConfigurationResponse(
            1,
            'local',
            'local',
            true, // $localConfiguration->isActive(),
            true, // $localConfiguration->isForced(),
            '/authentication/providers/configurations/local',
        );

        if (
            $openIdConfiguration !== null
            && $openIdConfiguration->getBaseUrl() !== null
            && $openIdConfiguration->getAuthorizationEndpoint() !== null
            && $openIdConfiguration->getClientId() !== null
        ) {
            $this->openIdProviderConfiguration = new FindOpenIdProviderConfigurationResponse(
                $openIdConfiguration->getId(),
                OpenIdConfiguration::TYPE,
                OpenIdConfiguration::NAME,
                $openIdConfiguration->isActive(),
                $openIdConfiguration->isForced(),
                $openIdConfiguration->getBaseUrl(),
                $openIdConfiguration->getAuthorizationEndpoint(),
                $openIdConfiguration->getClientId(),
            );
        }
    }
}
