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

namespace Core\Infrastructure\Configuration\NotificationPolicy\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Domain\Configuration\Notification\Model\NotifiedContact;
use Core\Domain\Configuration\Notification\Model\NotifiedContactGroup;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

abstract class AbstractDbReadNotificationRepository extends AbstractRepositoryDRB
{
    use LoggerTrait;

    /**
     * Find contacts from ids
     *
     * @param int[] $contactIds
     * @return NotifiedContact[]
     */
    protected function findContactsByIds(array $contactIds): array
    {
        $this->info('Fetching contacts from database');

        $contacts = [];

        if (empty($contactIds)) {
            return $contacts;
        }

        $request = $this->translateDbName(
            "SELECT
                c.contact_id,
                c.contact_alias,
                c.contact_name,
                c.contact_email,
                c.contact_admin,
                c.contact_host_notification_options,
                c.contact_service_notification_options,
                t1.tp_id as host_timeperiod_id,
                t1.tp_name as host_timeperiod_name,
                t1.tp_alias as host_timeperiod_alias,
                t2.tp_id as service_timeperiod_id,
                t2.tp_name as service_timeperiod_name,
                t2.tp_alias as service_timeperiod_alias
            FROM `:db`.contact c
            INNER JOIN `:db`.timeperiod t1
                ON t1.tp_id = c.timeperiod_tp_id
            INNER JOIN `:db`.timeperiod t2
                ON t2.tp_id = c.timeperiod_tp_id2"
        );

        $collector = new StatementCollector();

        $bindKeys = [];
        foreach ($contactIds as $index => $contactId) {
            $key = ":contactId_{$index}";

            $bindKeys[] = $key;
            $collector->addValue($key, $contactId, \PDO::PARAM_INT);
        }

        $request .= ' WHERE contact_id IN (' . implode(', ', $bindKeys) . ')
            AND contact_activate = \'1\'';

        $statement = $this->db->prepare($request);

        $collector->bind($statement);
        $statement->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /** @var array<string,int|string|null> $row */
            $contacts[] = DbNotifiedContactFactory::createFromRecord($row);
        }

        return $contacts;
    }

    /**
     * Find contact groups from ids
     *
     * @param int[] $contactGroupIds
     * @return NotifiedContactGroup[]
     */
    protected function findContactGroupsByIds(array $contactGroupIds): array
    {
        $this->info('Fetching contact groups from database');

        $contactGroups = [];

        if (empty($contactGroupIds)) {
            return $contactGroups;
        }

        $collector = new StatementCollector();

        $request = $this->translateDbName(
            'SELECT
                cg_id AS `id`,
                cg_name AS `name`,
                cg_alias AS `alias`,
                cg_activate AS `activated`
            FROM `:db`.contactgroup'
        );

        $bindKeys = [];
        foreach ($contactGroupIds as $index => $contactGroupId) {
            $key = ":contactGroupId_{$index}";

            $bindKeys[] = $key;
            $collector->addValue($key, $contactGroupId, \PDO::PARAM_INT);
        }

        $request .= ' WHERE cg_id IN (' . implode(', ', $bindKeys) . ')';

        $statement = $this->db->prepare($request);

        $collector->bind($statement);
        $statement->execute();

        $contactGroups = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /** @var array<string,int|string|null> $row */
            $contactGroups[] = DbNotifiedContactGroupFactory::createFromRecord($row);
        }

        return $contactGroups;
    }
}
