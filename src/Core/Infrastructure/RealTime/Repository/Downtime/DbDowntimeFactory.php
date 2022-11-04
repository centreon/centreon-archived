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

namespace Core\Infrastructure\RealTime\Repository\Downtime;

use Core\Domain\RealTime\Model\Downtime;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbDowntimeFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string,int|string|null> $data
     * @return Downtime
     */
    public static function createFromRecord(array $data): Downtime
    {
        /** @var string|null */
        $authorName = $data['author'];

        /** @var string|null */
        $comment = $data['comment_data'];

        return (new Downtime((int) $data['downtime_id'], (int) $data['host_id'], (int) $data['service_id']))
            ->setAuthorId((int) $data['author_id'])
            ->setAuthorName($authorName)
            ->setComment($comment)
            ->setCancelled((int) $data['cancelled'] === 1)
            ->setFixed((int) $data['fixed'] === 1)
            ->setStarted((int) $data['started'] === 1)
            ->setInstanceId(self::getIntOrNull($data['instance_id']))
            ->setEngineDowntimeId(self::getIntOrNull($data['internal_id']))
            ->setDuration(self::getIntOrNull($data['duration']))
            ->setDeletionTime(self::createDateTimeFromTimestamp((int) $data['deletion_time']))
            ->setEndTime(self::createDateTimeFromTimestamp((int) $data['end_time']))
            ->setStartTime(self::createDateTimeFromTimestamp((int) $data['start_time']))
            ->setActualStartTime(self::createDateTimeFromTimestamp((int) $data['actual_start_time']))
            ->setActualEndTime(self::createDateTimeFromTimestamp((int) $data['actual_end_time']))
            ->setEntryTime(self::createDateTimeFromTimestamp((int) $data['entry_time']));
    }
}
