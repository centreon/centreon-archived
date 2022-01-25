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
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Infrastructure\RealTime\Repository\Downtime\DbDowntimeFactory;
use Core\Application\RealTime\Repository\ReadDowntimeRepositoryInterface;

class DbReadDowntimeRepository extends AbstractRepositoryDRB implements ReadDowntimeRepositoryInterface
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
    public function findOnGoingDowntimesByHostId(int $hostId): array
    {
        return $this->findOnGoingDowntimes($hostId, 0);
    }

    /**
     * @inheritDoc
     */
    public function findOnGoingDowntimesByHostIdAndServiceId(int $hostId, int $serviceId): array
    {
        return $this->findOnGoingDowntimes($hostId, $serviceId);
    }

    /**
     * Find downtimes
     *
     * @param int $hostId
     * @param int $serviceId
     * @return Downtime[]
     */
    private function findOnGoingDowntimes(int $hostId, int $serviceId): array
    {
        $downtimes = [];

        $sql = 'SELECT d.*, c.contact_id AS `author_id` FROM `:dbstg`.`downtimes`  AS `d` '
            . 'LEFT JOIN `:db`.contact AS `c` ON c.contact_alias = d.author '
            . 'WHERE d.host_id = :hostId AND d.service_id = :serviceId '
            . 'AND d.deletion_time IS NULL AND ((NOW() BETWEEN FROM_UNIXTIME(d.actual_start_time) '
            . 'AND FROM_UNIXTIME(d.actual_end_time)) OR ((NOW() > FROM_UNIXTIME(d.actual_start_time) '
            . 'AND d.actual_end_time IS NULL))) '
            . 'ORDER BY d.entry_time DESC';

        $statement = $this->db->prepare($this->translateDbName($sql));
        $statement->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $statement->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $statement->execute();

        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $downtimes[] = DbDowntimeFactory::createFromRecord($row);
        }

        return $downtimes;
    }
}
