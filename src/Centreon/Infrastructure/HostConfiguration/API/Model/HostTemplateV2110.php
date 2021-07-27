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

namespace Centreon\Infrastructure\HostConfiguration\API\Model;

/**
 * This class is designed to represent the formatted response of the API request.
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostTemplateV2110
{
    /**
     * @var int|null;
     */
    public $id;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $alias;

    /**
     * @var string|null
     */
    public $displayName;

    /**
     * @var string|null
     */
    public $address;

    /**
     * @var string|null
     */
    public $comment;

    /**
     * @var bool
     */
    public $isActivated;

    /**
     * @var bool
     */
    public $isLocked;

    /**
     * @var int
     */
    public $activeChecksStatus;

    /**
     * @var int
     */
    public $passiveChecksStatus;

    /**
     * @var int|null
     */
    public $maxCheckAttemps;

    /**
     * @var int|null
     */
    public $checkInterval;

    /**
     * @var int|null
     */
    public $retryCheckInterval;

    /**
     * @var int
     */
    public $notificationsStatus;

    /**
     * @var int|null
     */
    public $notificationInterval;

    /**
     * @var int|null
     */
    public $firstNotificationDelay;

    /**
     * @var int|null
     */
    public $recoveryNotificationDelay;

    /**
     * @var int
     */
    public $notificationOptions;

    /**
     * @var int
     */
    public $stalkingOptions;

    /**
     * @var string|null
     */
    public $snmpCommunity;

    /**
     * @var string|null
     */
    public $snmpVersion;

    /**
     * @var int|null
     */
    public $icon;

    /**
     * @var string|null
     */
    public $alternativeIcon;

    /**
     * @var int|null
     */
    public $statusMapImage;

    /**
     * @var string|null
     */
    public $urlNotes;

    /**
     * @var string|null
     */
    public $actionUrl;

    /**
     * @var string|null
     */
    public $notes;

    /**
     * @var int[]
     */
    public $parentIds;
}
