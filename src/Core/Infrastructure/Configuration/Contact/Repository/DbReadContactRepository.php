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

namespace Core\Infrastructure\Configuration\Contact\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;
use Core\Application\Configuration\Contact\Repository\ReadContactRepositoryInterface;

class DbReadContactRepository extends AbstractRepositoryDRB implements ReadContactRepositoryInterface
{
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
    public function findByIds(array $contactIds): array
    {
        $contacts = [];

        if (empty($contactIds)) {
            return $contacts;
        }

        $collector = new StatementCollector();

        $request = $this->translateDbName(
            'SELECT
                c.contact_id AS `id`,
                c.contact_name AS `name`,
                c.contact_alias AS `alias`,
                c.contact_email AS `mail`,
                c.contact_host_notification_options AS `notified_on_host_events`,
                c.contact_service_notification_options AS `notified_on_service_events`,
                c.timeperiod_tp_id AS `notification_period_id_for_host`,
                (
                    SELECT tp_name
                    FROM `:db`.timeperiod t
                    WHERE t.tp_id = c.timeperiod_tp_id
                ) AS `notification_period_name_for_host`,
                (
                    SELECT tp_alias
                    FROM `:db`.timeperiod t
                    WHERE t.tp_id = c.timeperiod_tp_id
                ) AS `notification_period_alias_for_host`,
                c.timeperiod_tp_id2 AS `notification_period_id_for_service`,
                (
                    SELECT tp_name
                    FROM `:db`.timeperiod t
                    WHERE t.tp_id = c.timeperiod_tp_id2
                ) AS `notification_period_name_for_service`,
                (
                    SELECT tp_alias
                    FROM `:db`.timeperiod t
                    WHERE t.tp_id = c.timeperiod_tp_id2
                ) AS `notification_period_alias_for_service`,
                c.contact_activate AS `activated`,
                c.contact_admin AS `is_admin`
            FROM `:db`.contact c'
        );

        foreach ($contactIds as $index => $contactId) {
            $key = ":contactId_{$index}";

            $contactIdList[] = $key;
            $collector->addValue($key, $contactId, \PDO::PARAM_INT);
        }

        $request .= ' WHERE contact_id IN (' . implode(', ', $contactIdList) . ')';

        $statement = $this->db->prepare($request);

        $collector->bind($statement);
        $statement->execute();

        $contacts = [];
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contacts[] = DbContactFactory::createFromRecord($row);
        }

        return $contacts;
    }
}