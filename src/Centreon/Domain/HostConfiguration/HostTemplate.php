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

/**
 * This class is designed to represent a host template.
 *
 * @package Centreon\Domain\HostConfiguration
 */
class HostTemplate
{
    public const STATUS_ENABLE = 1;
    public const STATUS_DISABLE = 0;
    public const STATUS_DEFAULT = 2;

    private const AVAILABLE_STATUS = [
        self::STATUS_ENABLE, self::STATUS_DISABLE, self::STATUS_DEFAULT
    ];

    /**
     * @var int|null
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
     * @var string|null Display name
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
     */
    private $isActivated = true;

    /**
     * @var int Enable or disable active checks. By default active host checks are enabled.
     */
    private $activeChecksStatus = self::STATUS_DEFAULT;

    /**
     * @var int Enable or disable passive checks here. When disabled submitted states will be not accepted.
     */
    private $passiveChecksStatus = self::STATUS_DEFAULT;

    /**
     * @var int|null Number of checks before considering verified state (HARD).<br/>
     * Define the number of times that monitoring engine will retry the host check command if it returns any non-OK
     * state.<br/>
     * Setting this value to 1 will cause monitoring engine to generate an alert immediately.<br/>
     * <b>Note: If you do not want to check the status of the host, you must still set this to a minimum value of 1.
     * <br/>
     * To bypass the host check, just leave the check command option blank.</b>
     */
    private $maxCheckAttempts = null;

    /**
     * @var int|null Define the number of "time units" between regularly scheduled checks of the host.<br/>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.
     */
    private $checkInterval = null;

    /**
     * @var int|null Define the number of "time units" to wait before scheduling a re-check for this host after a
     * non-UP state was detected.<br/>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br/>
     * Once the host has been retried max_check_attempts times without a change in its status,
     * it will revert to being scheduled at its "normal" check interval rate.
     */
    private $retryCheckInterval = null;

    /**
     * @var int Specify whether or not notifications for this host are enabled.
     */
    private $notificationsStatus = self::STATUS_DEFAULT;

    /**
     * @var int|null Define the number of "time units" to wait before re-notifying a contact that this host is still
     * down or unreachable.<br/>
     * With the default time unit of 60s, this number will mean multiples of 1 minute.<br/>
     * A value of 0 disables re-notifications of contacts about problems for this host - only one problem notification
     * will be sent out.
     */
    private $notificationInterval = null;

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
}