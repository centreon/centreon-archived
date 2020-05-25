<?php

/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\ServiceConfiguration;

/**
 * This class is designed to represent a service configuration.
 *
 * @package Centreon\Domain\ServiceConfiguration
 */
class Service
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int|null Template id
     */
    private $templateId;

    /**
     * @var string|null Service Name
     */
    private $name;

    /**
     * @var string|null Service alias
     */
    private $alias;

    /**
     * @var string|null Service description
     */
    private $description;

    /**
     * @var bool Indicates whether or not this service is locked
     */
    private $isLocked;

    /**
     * @var bool Indicates whether or not this service is registered
     */
    private $isRegistered;

    /**
     * @var bool Indicates whether or not this service is activated
     */
    private $isActivated;

    public function __construct()
    {
        $this->isLocked = false;
        $this->isRegistered = true;
        $this->isActivated = true;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Service
     */
    public function setId(?int $id): Service
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTemplateId(): ?int
    {
        return $this->templateId;
    }

    /**
     * @param int|null $templateId
     * @return Service
     */
    public function setTemplateId(?int $templateId): Service
    {
        $this->templateId = $templateId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Service
     */
    public function setName(?string $name): Service
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @param string|null $alias
     * @return Service
     */
    public function setAlias(?string $alias): Service
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Service
     */
    public function setDescription(?string $description): Service
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * @param bool $isLocked
     * @return Service
     */
    public function setLocked(bool $isLocked): Service
    {
        $this->isLocked = $isLocked;
        return $this;
    }

    /**
     * @return bool
     */
    public function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    /**
     * @param bool $isRegistered
     * @return Service
     */
    public function setRegistered(bool $isRegistered): Service
    {
        $this->isRegistered = $isRegistered;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->isActivated;
    }

    /**
     * @param bool $isActivated
     * @return Service
     */
    public function setActivated(bool $isActivated): Service
    {
        $this->isActivated = $isActivated;
        return $this;
    }
}
