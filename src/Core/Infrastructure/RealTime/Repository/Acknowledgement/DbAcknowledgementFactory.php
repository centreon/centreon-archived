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

namespace Core\Infrastructure\RealTime\Repository\Acknowledgement;

use Core\Domain\RealTime\Model\Acknowledgement;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbAcknowledgementFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $data
     * @return Acknowledgement
     */
    public static function createFromRecord(array $data): Acknowledgement
    {
        $entryTime = (new \DateTime())->setTimestamp((int) $data['entry_time']);

        return (new Acknowledgement(
            (int) $data['acknowledgement_id'],
            (int) $data['host_id'],
            (int) $data['service_id'],
            $entryTime
        ))->setAuthorId((int) $data['author_id'])
            ->setAuthorName($data['author'])
            ->setSticky((int) $data['sticky'] === 1)
            ->setPersistentComment((int) $data['persistent_comment'] === 1)
            ->setNotifyContacts((int) $data['notify_contacts'] === 1)
            ->setSticky((int) $data['sticky'] === 1)
            ->setType((int) $data['type'])
            ->setState((int) $data['state'])
            ->setInstanceId((int) $data['instance_id'])
            ->setDeletionTime(self::createDateTimeFromTimestamp((int) $data['deletion_time']))
            ->setComment($data['comment_data']);
    }
}
