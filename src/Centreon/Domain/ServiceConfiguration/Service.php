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

use Centreon\Domain\Annotation\EntityDescriptor;

/**
 * This class is designed to represent a service configuration.
 *
 * @package Centreon\Domain\ServiceConfiguration
 */
class Service
{
    public const TYPE_TEMPLATE = 0;
    public const TYPE_SERVICE = 1;
    public const TYPE_META_SERVICE = 2;
    public const TYPE_BUSINESS_ACTIVITY = 2;
    public const TYPE_ANOMALY_DETECTION = 3;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var int|null Template id
     */
    private $templateId;

    /**
     * @var int|null
     */
    private $commandId;

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
     * @var int Service type
     * @see Service::TYPE_TEMPLATE          (0)
     * @see Service::TYPE_SERVICE           (1)
     * @see Service::TYPE_META_SERVICE      (2)
     * @see Service::TYPE_BUSINESS_ACTIVITY (2)
     * @see Service::TYPE_ANOMALY_DETECTION (3)
     */
    private $serviceType;

    /**
     * @var bool Indicates whether or not this service is activated
     * @EntityDescriptor(column="is_activated", modifier="setActivated")
     */
    private $isActivated;

    /**
     * @var ExtendedService
     */
    private $extendedService;

    public function __construct()
    {
        $this->isLocked = false;
        $this->serviceType = self::TYPE_SERVICE;
        $this->isActivated = true;
        $this->extendedService = new ExtendedService();
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
     * @return int|null
     */
    public function getCommandId(): ?int
    {
        return $this->commandId;
    }

    /**
     * @param int|null $commandId
     * @return Service
     */
    public function setCommandId(?int $commandId): Service
    {
        $this->commandId = $commandId;
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

    /**
     * @return int
     */
    public function getServiceType(): int
    {
        return $this->serviceType;
    }

    /**
     * @param int $serviceType
     * @return $this
     * @see Service::serviceType
     * @throws \InvalidArgumentException When the service type is not recognized
     */
    public function setServiceType(int $serviceType): Service
    {
        $allowedServiceType = [
            self::TYPE_TEMPLATE,
            self::TYPE_SERVICE,
            self::TYPE_META_SERVICE,
            self::TYPE_BUSINESS_ACTIVITY,
            self::TYPE_ANOMALY_DETECTION
        ];
        if (!in_array($serviceType, $allowedServiceType)) {
            throw new \InvalidArgumentException('This service type is not recognized');
        }
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * @return ExtendedService
     */
    public function getExtendedService(): ExtendedService
    {
        return $this->extendedService;
    }

    /**
     * @param ExtendedService $extendedService
     * @return Service
     */
    public function setExtendedService(ExtendedService $extendedService): Service
    {
        $this->extendedService = $extendedService;
        return $this;
    }
}
