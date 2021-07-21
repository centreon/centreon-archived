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

use Centreon\Domain\HostConfiguration\UseCase\V2110\HostTemplate\FindHostTemplatesResponse;

/**
 * This class is designed to create the hostTemplateV21 entity
 *
 * @package Centreon\Infrastructure\HostConfiguration\API\Model
 */
class HostTemplateV2110Factory
{
    /**
     * @param FindHostTemplatesResponse $response
     * @return HostTemplateV2110[]
     */
    public static function createFromResponse(FindHostTemplatesResponse $response): array
    {
        $hostTemplates = [];
        foreach ($response->getHostTemplates() as $hostTemplate) {
            $newHostTemplate = new HostTemplateV2110();
            $newHostTemplate->id = $hostTemplate['id'];
            $newHostTemplate->name = $hostTemplate['name'];
            $newHostTemplate->alias = $hostTemplate['alias'];
            $newHostTemplate->displayName = $hostTemplate['display_name'];
            $newHostTemplate->address = $hostTemplate['address'];
            $newHostTemplate->comment = $hostTemplate['comment'];
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
            $newHostTemplate->parentIds = $hostTemplate['parent_ids'];

            $hostTemplates[] = $newHostTemplate;
        }
        return $hostTemplates;
    }
}
