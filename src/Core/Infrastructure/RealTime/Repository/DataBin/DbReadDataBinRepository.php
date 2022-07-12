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

namespace Core\Infrastructure\RealTime\Repository\DataBin;

use \PDO;
use \DateTimeInterface;
use Core\Application\RealTime\Repository\ReadDataBinRepositoryInterface;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

class DbReadDataBinRepository extends AbstractRepositoryDRB implements ReadDataBinRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Retrieves raw data_bin with filters
     *
     * @param  array<int, string> $metrics
     * @return iterable<array<string,string>>
     */
    public function findDataByMetricsAndDates(array $metrics, DateTimeInterface $startDate, DateTimeInterface $endDate): iterable
    {
        $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        $this->db->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

        $columns = ['ctime AS time'];
        foreach ($metrics as $metricId => $metricName) {
            $columns[] = sprintf('AVG(CASE WHEN id_metric = %d THEN `value` end) AS %s', $metricId, $metricName);
        }

        $query = sprintf(
            'SELECT %s FROM `:dbstg`.data_bin WHERE ctime >= :start AND ctime < :end GROUP BY time',
            join(',', $columns)
        );

        $statement = $this->db->prepare($this->translateDbName($query));

        $statement->bindValue(':start', $startDate->getTimestamp(), \PDO::PARAM_INT);
        $statement->bindValue(':end', $endDate->getTimestamp(), \PDO::PARAM_INT);
        $statement->execute();

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $dataBin) {
            yield $this->formatDataBin($dataBin);
        }

        $statement->closeCursor();
    }

    private function formatDataBin(array $dataBin): array
    {
        $formattedData = [
            'time' => $dataBin['time'],
            'humantime' => date('Y-m-d H:i:s', (int) $dataBin['time']),

        ];

        foreach ($dataBin as $columnName => $columnValue) {
            if ($columnName !== 'time') {
                $formattedData[$columnName] = sprintf('%f', $columnValue);
            }
        }

        return $formattedData;
    }
}