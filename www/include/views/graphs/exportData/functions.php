<?php

/**
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
 */

declare(strict_types=1);

/**
 * Sends a json response with http code 400
 *
 * @param mixed $data
 *
 * @return void
 */
function sendErrorResponse(mixed $data): void
{
    header('HTTP/1.1 400 Bad Request', true, 400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
}

/**
 * Sends download stream response to the client
 *
 * @param array<int,string>       $heads
 * @param iterable<array<string>> $rows
 */
function sendDownloadResponse(string $fileName, array $heads, iterable $rows): void
{
    header('Content-Disposition: attachment;filename="' . $fileName . '";');
    header('Content-Type: application/csv; charset=UTF-8');
    header('Pragma: no-cache');

    $f = fopen('php://output', 'w');

    if ($f === false) {
        throw new \RuntimeException('Unable to write content in output');
    }

    fputcsv($f, $heads, ';');

    foreach ($rows as $row) {
        fputcsv($f, $row, ';');
    }

    fclose($f);
}

function checkSession(mixed $sid, CentreonDB $pearDB): void
{
    if (!is_string($sid)) {
        throw new \InvalidArgumentException('Session id is required');
    }

    $pearDB->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $statement = $pearDB->prepare('SELECT COUNT(1) as session_counter FROM session WHERE session_id = :session_id');
    $statement->bindParam(':session_id', $sid, \PDO::PARAM_STR);
    $statement->execute();
    $row = $statement->fetch();

    $sessionExists = is_array($row) && array_key_exists('session_counter', $row) && (int) $row['session_counter'] === 1;

    if (!$sessionExists) {
        throw new \InvalidArgumentException('Bad session id');
    }
}

/**
 * @return array<string,int>
 */
function extractHostIdAndServiceIdFromChartId(string $chartId): array
{
    if ($chartId === '') {
        throw new \InvalidArgumentException('Chart ID is missing');
    }

    if (preg_match('/^(\d+)_(\d+)$/', $chartId, $matches) !== 1) {
        throw new \InvalidArgumentException('Unable to parse chart ID');
    }

    return ['hostId' => (int) $matches[1], 'serviceId' => (int) $matches[2]];
}

function findIndexByHostIdAndServiceId(int $hostId, int $serviceId, CentreonDB $pearDBO): int
{
    $query = 'SELECT id FROM index_data WHERE host_id = :hostId AND service_id = :serviceId';
    $stmt = $pearDBO->prepare($query);
    $stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
    $stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch();

    if (!is_array($row) || !array_key_exists('id', $row)) {
        throw new \InvalidArgumentException('Resource not found');
    }

    return (int) $row['id'];
}

function findIndexByChartId(string $chartId, CentreonDB $pearDBO): int
{
    $hostAndServiceIds = extractHostIdAndServiceIdFromChartId($chartId);

    return findIndexByHostIdAndServiceId($hostAndServiceIds['hostId'], $hostAndServiceIds['serviceId'], $pearDBO);
}

function generateFileNameByIndex(int $index, CentreonDB $pearDBO): string
{
    $stmt = $pearDBO->prepare('SELECT host_name, service_description FROM index_data WHERE id = :index');
    $stmt->bindValue(':index', $index, \PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch();

    if (!is_array($row) || !array_key_exists('host_name', $row)) {
        throw new \InvalidArgumentException('host_name could not be found');
    }

    if (!array_key_exists('service_description', $row)) {
        throw new \InvalidArgumentException('service_description could not be found');
    }

    $hostName = $row['host_name'];
    $serviceDescription = $row['service_description'];

    if ($hostName !== '' && $serviceDescription !== '') {
        return sprintf('%s_%s.csv', $hostName, $serviceDescription);
    }

    return sprintf('%s.csv', $index);
}

/**
 * @return array<string,string>
 */
function getMetricsByIndexId(int $indexId, CentreonDB $pearDBO): array
{
    $query = 'SELECT DISTINCT metric_id, metric_name  FROM metrics, index_data ';
    $query .= ' WHERE metrics.index_id = index_data.id AND id = :index_id ORDER BY metric_id';
    $stmt = $pearDBO->prepare($query);
    $stmt->bindValue(':index_id', $indexId, \PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
}

/**
 * Retrieves raw data_bin with filters
 *
 * @param  array<int, string> $metrics
 * @return iterable<array<string,string>>
 */
function getDataByMetrics(array $metrics, int $startDate, int $endDate, CentreonDB $pearDBO): iterable
{
    $pearDBO->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    $pearDBO->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, true);

    $columns = ['ctime AS time'];
    foreach ($metrics as $metricId => $metricName) {
        $columns[] = sprintf('AVG(CASE WHEN id_metric = %d THEN `value` end) AS %s', $metricId, $metricName);
    }
    $query = sprintf(
        'SELECT %s FROM data_bin WHERE ctime >= :start AND ctime < :end GROUP BY time',
        join(',', $columns)
    );

    $stmt = $pearDBO->prepare($query);
    $stmt->bindValue(':start', $startDate, \PDO::PARAM_INT);
    $stmt->bindValue(':end', $endDate, \PDO::PARAM_INT);
    $stmt->execute();

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $dataBin) {
        yield formatDataBin($dataBin);
    }

    $stmt->closeCursor();
}

/**
 * Formats raw data from data_bin table
 *
 * @param  array<string> $dataBin
 * @return array<string>
 */
function formatDataBin(array $dataBin): array
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

function extractDate(mixed $data): int
{
    $date = filter_var($data ?? 0, FILTER_VALIDATE_INT);

    if (!is_int($date) || $date <= 0) {
        throw new \InvalidArgumentException('Date is not consistent');
    }

    return $date;
}

/**
 * Finds index with chartId in DB. If not $defaultIndex is returned
 *
 * @param string     $chartId      Chart identifier with pattern {hostId}_{serviceId}
 * @param string     $defaultIndex default value for index. Would be returned if chartId handling fails
 * @param CentreonDB $pearDBO      Database connection
 *
 * @return int
 */
function findIndex(string $chartId, string $defaultIndex, CentreonDB $pearDBO): int
{
    $sanitizedChartId = filter_var($chartId, FILTER_SANITIZE_STRING);
    if (is_string($sanitizedChartId)) {
        return findIndexByChartId($sanitizedChartId, $pearDBO);
    }

    $sanitizedDefaultIndex = filter_var($defaultIndex, FILTER_VALIDATE_INT);
    if (is_int($sanitizedDefaultIndex)) {
        return $sanitizedDefaultIndex;
    }

    return 0;
}
