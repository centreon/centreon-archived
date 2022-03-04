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

namespace Centreon\Domain\HostConfiguration;

use Centreon\Domain\HostConfiguration\Model\HostCategory;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Domain\Annotation\EntityDescriptor;
use Centreon\Domain\Common\Assertion\Assertion;

/***
 * This class is designed to represent a host configuration.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class Host
{
    public const OPTION_NO = 0,
                 OPTION_YES = 1,
                 OPTION_DEFAULT = 2;

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

    public const NOTIFICATIONS_OPTION_DISABLED = 0,
                 NOTIFICATIONS_OPTION_ENABLED = 1,
                 NOTIFICATIONS_OPTION_DEFAULT_ENGINE_VALUE = 2;

    private const AVAILABLE_NOTIFICATIONS_OPTION = [
        self::NOTIFICATIONS_OPTION_DISABLED,
        self::NOTIFICATIONS_OPTION_ENABLED,
        self::NOTIFICATIONS_OPTION_DEFAULT_ENGINE_VALUE,
    ];

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
     * @var HostCategory[]
     */
    private $categories = [];

    /**
     * @var HostGroup[]
     */
    private $groups = [];

    /**
     * @var HostSeverity|null
     */
    private $severity;

    /**
     * @var int
     */
    private $notificationsEnabledOption = self::NOTIFICATIONS_OPTION_DEFAULT_ENGINE_VALUE;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return self
     */
    public function setId(?int $id): self
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
     * @return self
     */
    public function setName(?string $name): self
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
     * @return self
     */
    public function setAlias(?string $alias): self
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
     * @return self
     */
    public function setDisplayName(?string $displayName): self
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
     * @return self
     */
    public function setIpAddress(?string $ipAddress): self
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
     * @return self
     */
    public function setComment(?string $comment): self
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
     * @return self
     */
    public function setGeoCoords(?string $geoCoords): self
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
     * @return self
     */
    public function setActivated(bool $isActivated): self
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
     * @return self
     */
    public function setExtendedHost(?ExtendedHost $extendedHost): self
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
     * @return self
     */
    public function setMonitoringServer(?MonitoringServer $monitoringServer): self
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
     * @return self
     */
    public function setType(int $type): self
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
     * @return self
     * @throws \InvalidArgumentException
     */
    public function addTemplate(Host $hostTemplate): self
    {
        if ($hostTemplate->getType() !== Host::TYPE_HOST_TEMPLATE) {
            throw new \InvalidArgumentException(_('This host is not a host template'));
        }
        $this->templates[] = $hostTemplate;
        return $this;
    }

    /**
     * Clear and add all host templates.
     *
     * @param Host[] $hostTemplates
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setTemplates(array $hostTemplates): self
    {
        $this->clearTemplates();
        foreach ($hostTemplates as $hostTemplate) {
            if ($hostTemplate->getType() !== Host::TYPE_HOST_TEMPLATE) {
                throw new \InvalidArgumentException(_('This host is not a host template'));
            }
            $this->templates[] = $hostTemplate;
        }

        return $this;
    }

    /**
     * Clear all templates.
     *
     * @return self
     */
    public function clearTemplates(): self
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
     * @return self
     */
    public function setMacros(array $macros): self
    {
        $this->macros = $macros;
        return $this;
    }

    /**
     * Add a host macro.
     *
     * @param HostMacro $hostMacro Host macro to be added
     * @return self
     */
    public function addMacro(HostMacro $hostMacro): self
    {
        $this->macros[] = $hostMacro;
        return $this;
    }

    /**
     * @param HostCategory $category
     * @return self
     */
    public function addCategory(HostCategory $category): self
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @return HostCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @return self
     */
    public function clearCategories(): self
    {
        $this->categories = [];
        return $this;
    }

    /**
     * @param HostGroup $hostGroup
     * @return self
     */
    public function addGroup(HostGroup $hostGroup): self
    {
        $this->groups[] = $hostGroup;
        return $this;
    }

    /**
     * @return HostGroup[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * @return self
     */
    public function clearGroups(): self
    {
        $this->groups = [];
        return $this;
    }

    /**
     * @param HostSeverity|null $hostSeverity
     * @return self
     */
    public function setSeverity(?HostSeverity $hostSeverity): self
    {
        $this->severity = $hostSeverity;
        return $this;
    }

    /**
     * @return HostSeverity|null
     */
    public function getSeverity(): ?HostSeverity
    {
        return $this->severity;
    }

    /**
     * @return int
     */
    public function getNotificationsEnabledOption(): int
    {
        return $this->notificationsEnabledOption;
    }

    /**
     * @param int $notificationsEnabledOption
     * @return self
     */
    public function setNotificationsEnabledOption(int $notificationsEnabledOption): self
    {
        Assertion::inArray(
            $notificationsEnabledOption,
            self::AVAILABLE_NOTIFICATIONS_OPTION,
            'Engine::notificationsEnabledOption',
        );

        $this->notificationsEnabledOption = $notificationsEnabledOption;

        return $this;
    }
}
