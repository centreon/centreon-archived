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

namespace Core\Application\Security\UseCase\FindProviderConfigurations\ProviderResponse;

use Core\Domain\Security\ProviderConfiguration\OpenId\Model\OpenIdConfiguration;

class OpenIdProviderResponse implements ProviderResponseInterface
{
    /**
     * @var integer
     */
    public int $id;

    /**
     * @var boolean
     */
    public bool $isActive;

    /**
     * @var boolean
     */
    public bool $isForced;

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
    public ?string $clientId;

    /**
     * @var string[]|null
     */
    public ?array $connectionScopes;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return OpenIdConfiguration::NAME;
    }

    /**
     * @inheritDoc
     */
    public static function create(mixed $configuration): self
    {
        $response = new self();
        $response->isActive = $configuration->isActive();
        $response->isForced = $configuration->isForced();
        $response->baseUrl = $configuration->getBaseUrl();
        $response->authorizationEndpoint = $configuration->getAuthorizationEndpoint();
        $response->clientId = $configuration->getClientId();
        $response->id = $configuration->getId();
        $response->connectionScopes = $configuration->getConnectionScopes();

        return $response;
    }
}
