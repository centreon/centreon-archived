<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Monitoring;

/**
 * Resource group model for resource repository
 *
 * @package Centreon\Domain\Monitoring
 */
class ResourceGroup
{
    /**
     * Redirection URI to group configuration
     */
    private ?string $configurationUri = null;

    /**
     * Contructor for ResourceGroup entity
     *
     * @param integer $id
     * @param string $name
     */
    public function __construct(private int $id, private string $name)
    {
    }


    /**
     * Get resource group id.
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the resource group name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the resource group id.
     */
    public function setId(int $resourceGroupId): self
    {
        $this->id = $resourceGroupId;

        return $this;
    }

    /**
     * Set the resource group name.
     */
    public function setName(string $resourceGroupName): self
    {
        $this->name = $resourceGroupName;

        return $this;
    }

    public function getConfigurationUri(): ?string
    {
        return $this->configurationUri;
    }

    public function setConfigurationUri(?string $configurationUri): self
    {
        $this->configurationUri = $configurationUri;
        return $this;
    }
}
