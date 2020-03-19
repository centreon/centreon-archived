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

namespace Centreon\Domain\HostConfiguration;

class HostConfiguration
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null
     */
    private $ipAddress;

    /**
     * @var string|null
     */
    private $comment;

    /**
     * @var string|null
     */
    private $geoCoords;

    /**
     * @var bool
     */
    private $isActivate;

    /**
     * @var ExtendedHost|null
     */
    private $extendedHost;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return HostConfiguration
     */
    public function setId(int $id): HostConfiguration
    {
        $this->id = $id;
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
     * @return HostConfiguration
     */
    public function setName(?string $name): HostConfiguration
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
     * @return HostConfiguration
     */
    public function setAlias(?string $alias): HostConfiguration
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    /**
     * @param string|null $ipAddress
     * @return HostConfiguration
     */
    public function setIpAddress(?string $ipAddress): HostConfiguration
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return HostConfiguration
     */
    public function setComment(?string $comment): HostConfiguration
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getGeoCoords(): ?string
    {
        return $this->geoCoords;
    }

    /**
     * @param string|null $geoCoords
     * @return HostConfiguration
     */
    public function setGeoCoords(?string $geoCoords): HostConfiguration
    {
        $this->geoCoords = $geoCoords;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActivate(): bool
    {
        return $this->isActivate;
    }

    /**
     * @param bool $isActivate
     * @return HostConfiguration
     */
    public function setIsActivate(bool $isActivate): HostConfiguration
    {
        $this->isActivate = $isActivate;
        return $this;
    }

    /**
     * @return ExtendedHost|null
     */
    public function getExtendedHost(): ?ExtendedHost
    {
        return $this->extendedHost;
    }

    /**
     * @param ExtendedHost|null $extendedHost
     * @return HostConfiguration
     */
    public function setExtendedHost(?ExtendedHost $extendedHost): HostConfiguration
    {
        $this->extendedHost = $extendedHost;
        return $this;
    }
}
