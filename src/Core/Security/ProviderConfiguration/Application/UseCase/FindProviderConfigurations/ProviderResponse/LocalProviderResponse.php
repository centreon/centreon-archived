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

use Core\Security\ProviderConfiguration\Domain\Model\Provider;

class LocalProviderResponse implements ProviderResponseInterface
{
    /**
     * @var integer
     */
    public int $id;

    /**
     * @var string
     */
    public string $type;

    /**
     * @var string
     */
    public string $name;

    /**
     * @var bool
     */
    public bool $isActive;

    /**
     * @var bool
     */
    public bool $isForced;

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return Provider::LOCAL;
    }

    /**
     * @inheritDoc
     */
    public static function create(mixed $configuration): self
    {
        $response = new self();
        $response->id = $configuration->getId();
        $response->type = $configuration->getType();
        $response->name = $configuration->getName();
        $response->isActive = $configuration->isActive();
        $response->isForced = $configuration->isForced();

        return $response;
    }
}
