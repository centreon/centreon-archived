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

class FindOpenIdProviderConfigurationResponse
{
    /**
     * @param int|null $id
     * @param string $type
     * @param string $name
     * @param bool $isActive
     * @param bool $isForced
     * @param string $baseUrl
     * @param string $authorizationEndpoint
     * @param string $clientId
     */
    public function __construct(
        public ?int $id,
        public string $type,
        public string $name,
        public bool $isActive,
        public bool $isForced,
        public string $baseUrl,
        public string $authorizationEndpoint,
        public string $clientId,
    ) {
    }
}
