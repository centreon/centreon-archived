<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\HostConfiguration\UseCase\V2110\HostTemplate;

use Centreon\Domain\HostConfiguration\Model\HostTemplate;
use Centreon\Domain\Media\Model\Image;

/**
 * This class is a DTO for the FindHostTemplates use case.
 *
 * @package Centreon\Domain\HostConfiguration\UseCase\V21\HostTemplate
 */
class FindHostTemplatesResponse
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private $hostTemplates = [];

    /**
     * @param HostTemplate[] $hostTemplates
     * @throws \Exception
     */
    public function setHostTemplates(array $hostTemplates): void
    {
        foreach ($hostTemplates as $hostTemplate) {
            $this->hostTemplates[] = [
                'id' => $hostTemplate->getId(),
                'name' => $hostTemplate->getName(),
                'alias' => $hostTemplate->getAlias(),
                'display_name' => $hostTemplate->getDisplayName(),
                'address' => $hostTemplate->getAddress(),
                'comment' => $hostTemplate->getComment(),
                'is_activated' => $hostTemplate->isActivated(),
                'is_locked' => $hostTemplate->isLocked(),
                'active_checks_status' => $hostTemplate->getActiveChecksStatus(),
                'passive_checks_status' => $hostTemplate->getPassiveChecksStatus(),
                'max_check_attemps' => $hostTemplate->getMaxCheckAttempts(),
                'check_interval' => $hostTemplate->getCheckInterval(),
                'retry_check_interval' => $hostTemplate->getRetryCheckInterval(),
                'notifications_status' => $hostTemplate->getNotificationsStatus(),
                'notification_interval' => $hostTemplate->getNotificationInterval(),
                'first_notification_delay' => $hostTemplate->getFirstNotificationDelay(),
                'recovery_notification_delay' => $hostTemplate->getRecoveryNotificationDelay(),
                'notification_options' => $hostTemplate->getNotificationOptions(),
                'stalking_options' => $hostTemplate->getStalkingOptions(),
                'snmp_community' => $hostTemplate->getSnmpCommunity(),
                'snmp_version' => $hostTemplate->getSnmpVersion(),
                'icon' => $this->imageToArray($hostTemplate->getIcon()),
                'alternative_icon' => $hostTemplate->getAlternativeIcon(),
                'status_map_image' => $this->imageToArray($hostTemplate->getStatusMapImage()),
                'url_notes' => $hostTemplate->getUrlNotes(),
                'action_url' => $hostTemplate->getActionUrl(),
                'notes' => $hostTemplate->getNotes(),
                'parent_ids' => $hostTemplate->getParentIds()
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getHostTemplates(): array
    {
        return $this->hostTemplates;
    }

    /**
     * @param Image|null $image
     * @return array<string, string|int|null>|null
     */
    private function imageToArray(?Image $image): ?array
    {
        if ($image !== null) {
            return [
                'id' => $image->getId(),
                'name' => $image->getName(),
                'path' => $image->getPath(),
                'comment' => $image->getComment()
            ];
        }
        return null;
    }
}
