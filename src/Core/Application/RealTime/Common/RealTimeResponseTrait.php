<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Core\Application\RealTime\Common;

use Core\Domain\RealTime\Model\Icon;
use Core\Domain\RealTime\Model\Status;
use Core\Domain\RealTime\Model\Downtime;
use Core\Domain\RealTime\Model\Acknowledgement;

trait RealTimeResponseTrait
{
    /**
     * Converts an Icon model into an array
     *
     * @param Icon|null $icon
     * @return array<string, string|null>
     */
    public function iconToArray(?Icon $icon): array
    {
        return is_null($icon)
            ? []
            : [
                'name' => $icon->getName(),
                'url' => $icon->getUrl()
            ];
    }

    /**
     * Converts an array of Downtimes entities into an array
     *
     * @param Downtime[] $downtimes
     * @return array<int, array<string, mixed>>
     */
    public function downtimesToArray(array $downtimes): array
    {
        return array_map(
            fn (Downtime $downtime) => [
                'start_time' => $downtime->getStartTime(),
                'end_time' => $downtime->getEndTime(),
                'actual_start_time' => $downtime->getActualStartTime(),
                'id' => $downtime->getId(),
                'entry_time' => $downtime->getEntryTime(),
                'author_id' => $downtime->getAuthorId(),
                'author_name' => $downtime->getAuthorName(),
                'host_id' => $downtime->getHostId(),
                'service_id' => $downtime->getServiceId(),
                'is_cancelled' => $downtime->isCancelled(),
                'comment' => $downtime->getComment(),
                'deletion_time' => $downtime->getDeletionTime(),
                'duration' => $downtime->getDuration(),
                'internal_id' => $downtime->getEngineDowntimeId(),
                'is_fixed' => $downtime->isFixed(),
                'poller_id' => $downtime->getInstanceId(),
                'is_started' => $downtime->isStarted()
            ],
            $downtimes
        );
    }

    /**
     * Converts an Acknowledgement entity into an array
     *
     * @param Acknowledgement|null $acknowledgement
     * @return array<string, mixed>
     */
    public function acknowledgementToArray(?Acknowledgement $acknowledgement): array
    {
        return is_null($acknowledgement)
            ? []
            : [
                'id' => $acknowledgement->getId(),
                'poller_id' => $acknowledgement->getInstanceId(),
                'host_id' => $acknowledgement->getHostId(),
                'service_id' => $acknowledgement->getServiceId(),
                'author_id' => $acknowledgement->getAuthorId(),
                'author_name' => $acknowledgement->getAuthorName(),
                'comment' => $acknowledgement->getComment(),
                'deletion_time' => $acknowledgement->getDeletionTime(),
                'entry_time' => $acknowledgement->getEntryTime(),
                'is_notify_contacts' => $acknowledgement->isNotifyContacts(),
                'is_persistent_comment' => $acknowledgement->isPersistentComment(),
                'is_sticky' => $acknowledgement->isSticky(),
                'state' => $acknowledgement->getState(),
                'type' => $acknowledgement->getType(),
                'with_services' => $acknowledgement->isWithServices()
            ];
    }

    /**
     * Converts Status model into an array for DTO
     *
     * @param Status $status
     * @return array<string, mixed>
     */
    private function statusToArray(Status $status): array
    {
        return [
            'name' => $status->getName(),
            'code' => $status->getCode(),
            'severity_code' => $status->getOrder(),
            'type' => $status->getType()
        ];
    }
}
