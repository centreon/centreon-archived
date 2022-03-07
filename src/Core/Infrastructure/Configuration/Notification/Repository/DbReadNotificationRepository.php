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

namespace Core\Infrastructure\Configuration\Notification\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Core\Application\Configuration\Notification\Repository\ReadNotificationRepositoryInterface;

class DbReadNotificationRepository extends AbstractRepositoryDRB implements ReadNotificationRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findHostNotificationSettingsByUserIds(array $userIds): array
    {
        $this->info('Fetching notifications from database');
        $notifications = [];

        if (empty($userIds)) {
            return $notifications;
        }

        $request = $this->translateDbName(
            "SELECT
                c.contact_id,
                c.contact_host_notification_options,
                c.timeperiod_tp_id,
                t.tp_name,
                t.tp_alias
            FROM `:db`.contact c
            INNER JOIN `:db`.timeperiod t
                ON t.tp_id = c.timeperiod_tp_id"
        );

        $collector = new StatementCollector();

        foreach ($userIds as $index => $userId) {
            $key = ":contactId_{$index}";

            $userIdList[] = $key;
            $collector->addValue($key, $userId, \PDO::PARAM_INT);
        }
        $request .= ' WHERE contact_id IN (' . implode(', ', $userIdList) . ')';
        $statement = $this->db->prepare($request);
        $collector->bind($statement);
        $statement->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $notifications[$row['contact_id']] = DbHostNotificationFactory::createFromRecord($row);
        }

        return $notifications;
    }
}
