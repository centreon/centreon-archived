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

namespace Core\Security\ProviderConfiguration\Application\UseCase\FindProviderConfigurations\ProviderResponse;

use Core\Security\ProviderConfiguration\Domain\Model\Configuration;
use Core\Security\ProviderConfiguration\Domain\Model\Provider;
use Core\Security\ProviderConfiguration\Domain\OpenId\Model\CustomConfiguration;

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
        return Provider::OPENID;
    }

    /**
     * @param Configuration $configuration
     * @inheritDoc
     */
    public static function create(mixed $configuration): self
    {
        /** @var CustomConfiguration $customConfiguration */
        $customConfiguration = $configuration->getCustomConfiguration();

        $response = new self();
        $response->isActive = $configuration->isActive();
        $response->isForced = $configuration->isForced();
        $response->baseUrl = $customConfiguration->getBaseUrl();
        $response->authorizationEndpoint = $customConfiguration->getAuthorizationEndpoint();
        $response->clientId = $customConfiguration->getClientId();
        $response->id = $configuration->getId();
        $response->connectionScopes = $customConfiguration->getConnectionScopes();

        return $response;
    }
}
