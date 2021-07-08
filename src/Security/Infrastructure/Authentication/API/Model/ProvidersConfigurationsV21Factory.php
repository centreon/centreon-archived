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

namespace Security\Infrastructure\Authentication\API\Model;

use Centreon\Domain\Authentication\UseCase\FindProvidersConfigurationsResponse;

class ProvidersConfigurationsV21Factory
{

    /**
     * @return \stdClass[]
     */
    public static function createFromResponse(FindProvidersConfigurationsResponse $response): array
    {
        $providersConfigurations = [];
        foreach ($response->getProvidersConfigurations() as $providerConfiguration) {
            $newProviderConfiguration = self::createEmptyClass();
            $newProviderConfiguration->id = $providerConfiguration['id'];
            $newProviderConfiguration->type = $providerConfiguration['type'];
            $newProviderConfiguration->name = $providerConfiguration['name'];
            $newProviderConfiguration->centreonBaseUri = $providerConfiguration['centreon_base_uri'];
            $newProviderConfiguration->isForced = $providerConfiguration['is_forced'];
            $newProviderConfiguration->isActive = $providerConfiguration['is_active'];
            $newProviderConfiguration->authenticationUri = $providerConfiguration['authentication_uri'];

            $providersConfigurations[] = $newProviderConfiguration;
        }
        return $providersConfigurations;
    }
    /**
     * @return \stdClass
     */
    private static function createEmptyClass(): \stdClass
    {
        return new class extends \stdClass {
            /**
             * @var int
             */
            public $id;

            /**
             * @var string
             */
            public $type;

            /**
             * @var string
             */
            public $name;

            /**
             * @var string
             */
            public $centreonBaseUri;

            /**
             * @var string
             */
            public $authenticationUri;

            /**
             * @var bool
             */
            public $isActive;

            /**
             * @var bool
             */
            public $isForced;
        };
    }
}
