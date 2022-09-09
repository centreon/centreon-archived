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
     * Id of the resource group
     *
     * @var int
     */
    private $id;

    /**
     * Name of the resource group
     *
     * @var string
     */
    private $name;

    /**
     * Redirection URI to group configuration
     *
     * @var string|null
     */
    private $configurationUri;

    /**
     * Contructor for ResourceGroup entity
     *
     * @param integer $resourceGroupId
     * @param string $resourceGroupName
     */
    public function __construct(int $resourceGroupId, string $resourceGroupName)
    {
        $this->id = $resourceGroupId;
        $this->name = $resourceGroupName;
    }


    /**
     * Get resource group id.
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the resource group name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the resource group id.
     *
     * @param integer $resourceGroupId
     * @return ResourceGroup
     */
    public function setId(int $resourceGroupId): self
    {
        $this->id = $resourceGroupId;

        return $this;
    }

    /**
     * Set the resource group name.
     *
     * @param string $resourceGroupName
     * @return ResourceGroup
     */
    public function setName(string $resourceGroupName): self
    {
        $this->name = $resourceGroupName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfigurationUri(): ?string
    {
        return $this->configurationUri;
    }

    /**
     * @param string|null $configurationUri
     * @return self
     */
    public function setConfigurationUri(?string $configurationUri): self
    {
        $this->configurationUri = $configurationUri;
        return $this;
    }
}
