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

use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Annotation\EntityDescriptor;

/***
 * This class is designed to represent a host configuration.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class Host
{
    /**
     * Host template
     */
    public const TYPE_HOST_TEMPLATE = 0;
    /**
     * Host
     */
    public const TYPE_HOST = 1;
    /**
     * Host meta
     */
    public const TYPE_META = 2;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var MonitoringServer|null
     */
    private $monitoringServer;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $alias;

    /**
     * @var string|null Host display name
     */
    private $displayName;

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
     * @EntityDescriptor(column="is_activated", modifier="setActivated")
     */
    private $isActivated = true;

    /**
     * @var int Host type
     * @see Host::TYPE_HOST_TEMPLATE (0)
     * @see Host::TYPE_HOST (1)
     * @see Host::TYPE_META (2)
     */
    private $type = self::TYPE_HOST;

    /**
     * @var ExtendedHost|null
     */
    private $extendedHost;

    /**
     * @var Host[] Host templates
     */
    private $templates = [];

    /**
     * @var HostMacro[]
     */
    private $macros = [];

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Host
     */
    public function setId(?int $id): Host
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
     * @return Host
     */
    public function setName(?string $name): Host
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
     * @return Host
     */
    public function setAlias(?string $alias): Host
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return Host
     */
    public function setDisplayName(?string $displayName): Host
    {
        $this->displayName = $displayName;
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
     * @return Host
     */
    public function setIpAddress(?string $ipAddress): Host
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
     * @return Host
     */
    public function setComment(?string $comment): Host
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
     * @return Host
     */
    public function setGeoCoords(?string $geoCoords): Host
    {
        $this->geoCoords = $geoCoords;
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
     * @return Host
     */
    public function setActivated(bool $isActivated): Host
    {
        $this->isActivated = $isActivated;
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
     * @return Host
     */
    public function setExtendedHost(?ExtendedHost $extendedHost): Host
    {
        $this->extendedHost = $extendedHost;
        return $this;
    }

    /**
     * @return MonitoringServer|null
     */
    public function getMonitoringServer(): ?MonitoringServer
    {
        return $this->monitoringServer;
    }

    /**
     * @param MonitoringServer|null $monitoringServer
     * @return Host
     */
    public function setMonitoringServer(?MonitoringServer $monitoringServer): Host
    {
        $this->monitoringServer = $monitoringServer;
        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Host
     */
    public function setType(int $type): Host
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return Host[]
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }

    /**
     * Add a host template.
     *
     * @param Host $hostTemplate
     * @return Host
     * @throws \InvalidArgumentException
     */
    public function addTemplate(Host $hostTemplate): Host
    {
        if ($hostTemplate->getType() !== Host::TYPE_HOST_TEMPLATE) {
            throw new \InvalidArgumentException('This host is not a template');
        }
        $this->templates[] = $hostTemplate;
        return $this;
    }

    /**
     * Clear all templates.
     *
     * @return Host
     */
    public function clearTemplates(): Host
    {
        $this->templates = [];
        return $this;
    }

    /**
     * @return HostMacro[]
     */
    public function getMacros(): array
    {
        return $this->macros;
    }

    /**
     * @param HostMacro[] $macros
     * @return Host
     */
    public function setMacros(array $macros): Host
    {
        $this->macros = $macros;
        return $this;
    }

    /**
     * Add a host macro.
     *
     * @param HostMacro $hostMacro Host macro to be added
     * @return Host
     */
    public function addMacro(HostMacro $hostMacro): Host
    {
        $this->macros[] = $hostMacro;
        return $this;
    }
}
