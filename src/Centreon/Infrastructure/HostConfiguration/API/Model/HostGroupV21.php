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

use Centreon\Domain\HostConfiguration\UseCase\v2_1\FindHostGroupsResponse;

/**
 * This class is designed to represent the formatted response of the API request.
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostGroupV21
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
    public $parents;

    /**
     * @param FindHostTemplatesResponse $response
     * @return HostTemplateV21[]
     */
    public static function createFromResponse(FindHostTemplatesResponse $response): array
    {
        $hostTemplates = [];
        foreach ($response->getHostTemplates() as $hostTemplate) {
            $newHostTemplate = new self();
            $newHostTemplate->id = $hostTemplate['id'];
            $newHostTemplate->name = $hostTemplate['name'];
            $newHostTemplate->alias = $hostTemplate['alias'];
            $newHostTemplate->displayName = $hostTemplate['display_name'];
            $newHostTemplate->address = $hostTemplate['address'];
            $newHostTemplate->isActivated = $hostTemplate['is_activated'];
            $newHostTemplate->isLocked = $hostTemplate['is_locked'];
            $newHostTemplate->activeChecksStatus = $hostTemplate['active_checks_status'];
            $newHostTemplate->passiveChecksStatus = $hostTemplate['passive_checks_status'];
            $newHostTemplate->maxCheckAttemps = $hostTemplate['max_check_attemps'];
            $newHostTemplate->checkInterval = $hostTemplate['check_interval'];
            $newHostTemplate->retryCheckInterval = $hostTemplate['retry_check_interval'];
            $newHostTemplate->notificationsStatus = $hostTemplate['notifications_status'];
            $newHostTemplate->notificationInterval = $hostTemplate['notification_interval'];
            $newHostTemplate->firstNotificationDelay = $hostTemplate['first_notification_delay'];
            $newHostTemplate->recoveryNotificationDelay = $hostTemplate['recovery_notification_delay'];
            $newHostTemplate->notificationOptions = $hostTemplate['notification_options'];
            $newHostTemplate->stalkingOptions = $hostTemplate['stalking_options'];
            $newHostTemplate->snmpCommunity = $hostTemplate['snmp_community'];
            $newHostTemplate->snmpVersion = $hostTemplate['snmp_version'];
            $newHostTemplate->icon = $hostTemplate['icon'];
            $newHostTemplate->alternativeIcon = $hostTemplate['alternative_icon'];
            $newHostTemplate->statusMapImage = $hostTemplate['status_map_image'];
            $newHostTemplate->urlNotes = $hostTemplate['url_notes'];
            $newHostTemplate->actionUrl = $hostTemplate['action_url'];
            $newHostTemplate->notes = $hostTemplate['notes'];
            $newHostTemplate->parents = $hostTemplate['parent_ids'];

            $hostTemplates[] = $newHostTemplate;
        }
        return $hostTemplates;
    }
}
