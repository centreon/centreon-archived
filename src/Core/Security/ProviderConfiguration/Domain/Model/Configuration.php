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

namespace Core\Security\ProviderConfiguration\Domain\Model;

use Core\Security\ProviderConfiguration\Domain\CustomConfigurationInterface;

class Configuration
{
    protected CustomConfigurationInterface $customConfiguration;

    /**
     * @param int $id
     * @param string $type
     * @param string $name
     * @param string $jsonCustomConfiguration
     * @param bool $isActive
     * @param bool $isForced
     */
    public function __construct(
        private int $id,
        private string $type,
        private string $name,
        private string $jsonCustomConfiguration,
        private bool $isActive,
        private bool $isForced
    ) {
    }

    /**
     * @param bool|null $isActive
     * @param bool|null $isForced
     */
    public function update(?bool $isActive = null, ?bool $isForced = null): void
    {
        if ($isActive !== null) {
            $this->isActive = $isActive;
        }

        if ($isForced !== null) {
            $this->isForced = $isForced;
        }
    }


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return bool
     */
    public function isForced(): bool
    {
        return $this->isForced;
    }

    /**
     * @return string
     */
    public function getJsonCustomConfiguration(): string
    {
        return $this->jsonCustomConfiguration;
    }

    /**
     * @param CustomConfigurationInterface $customConfiguration
     */
    public function setCustomConfiguration(CustomConfigurationInterface $customConfiguration): void
    {
        $this->customConfiguration = $customConfiguration;
    }

    /**
     * @return CustomConfigurationInterface
     */
    public function getCustomConfiguration(): CustomConfigurationInterface
    {
        return $this->customConfiguration;
    }
}
