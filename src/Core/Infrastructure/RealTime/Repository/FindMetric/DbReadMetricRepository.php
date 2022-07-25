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

namespace Core\Infrastructure\RealTime\Repository\FindMetric;

use PDO;
use Centreon\Infrastructure\DatabaseConnection;
use Core\Application\RealTime\Repository\ReadMetricRepositoryInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Domain\RealTime\Model\Metric;

class DbReadMetricRepository extends AbstractRepositoryDRB implements ReadMetricRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @return array<Metric>
     */
    public function findMetricsByIndexId(int $indexId): array
    {
        $query = 'SELECT DISTINCT metric_id as id, metric_name as name FROM `:dbstg`.metrics, `:dbstg`.index_data ';
        $query .= ' WHERE metrics.index_id = index_data.id AND id = :index_id ORDER BY metric_id';
        $statement = $this->db->prepare($this->translateDbName($query));
        $statement->bindValue(':index_id', $indexId, \PDO::PARAM_INT);
        $statement->execute();

        $records = $statement->fetchAll();
        if (!is_array($records) || count($records) === 0) {
            return [];
        }

        $metrics = [];
        foreach ($records as $record) {
            $metrics[] = new Metric((int) $record['id'], $record['name']);
        }

        return $metrics;
    }
}
