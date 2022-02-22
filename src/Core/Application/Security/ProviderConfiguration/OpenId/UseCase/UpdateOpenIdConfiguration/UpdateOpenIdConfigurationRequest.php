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

namespace Core\Application\Security\ProviderConfiguration\OpenId\UseCase\UpdateOpenIdConfiguration;

class UpdateOpenIdConfigurationRequest
{
    /**
     * @var boolean
     */
    public bool $isActive;

    /**
     * @var boolean
     */
    public bool $isForced;

    /**
     * @var array
     */
    public array $trustedClientAddresses;

    /**
     * @var array
     */
    public array $blacklistClientAddresses;

    /**
     * @var string|null
     */
    public ?string $baseUrl;

    /**
     * @var string|null
     */
    public ?string $authorizationEndpoint;

    /**
     * @var string|null
     */
    public ?string $tokenEndpoint;

    /**
     * @var string|null
     */
    public ?string $introspectionTokenEndpoint;

    /**
     * @var string|null
     */
    public ?string $userInformationsEndpoint;

    /**
     * @var string|null
     */
    public ?string $endSessionEndpoint;

    /**
     * @var array
     */
    public array $connectionScope;

    /**
     * @var string|null
     */
    public ?string $loginClaim;

    /**
     * @var string|null
     */
    public ?string $clientId;

    /**
     * @var string|null
     */
    public ?string $clientSecret;

    /**
     * @var string|null
     */
    public ?string $authenticationType;

    /**
     * @var boolean
     */
    public bool $verifyPeer;
}
