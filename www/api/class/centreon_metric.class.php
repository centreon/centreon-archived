<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphNg.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphService.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphPoller.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphStatus.class.php";
require_once __DIR__ . "/webService.class.php";

class CentreonMetric extends CentreonWebService
{
    protected $pearDBMonitoring;

    protected $statusColors = array(
        'ok' => '#88b917',
        'warning' => '#ff9a13',
        'critical' => '#e00b3d',
        'unknown' => 'gray'
    );

    /**
     * Constructor
     *
     * @param CentreonDB $db
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
    }

    /**
     * Get metric list
     *
     * @return array
     *
     * @throws Exception
     */
    public function getList()
    {
        global $centreon;

        $queryValues = array();
        if (isset($this->arguments['q'])) {
            $queryValues['name'] = '%' . (string)$this->arguments['q'] . '%';
        } else {
            $queryValues['name'] = '%%';
        }

        $query = 'SELECT DISTINCT(`metric_name`)
            COLLATE utf8_bin as "metric_name", index_id FROM `metrics` as m, index_data i
            WHERE metric_name LIKE :name ';

        /**
         * If ACLs on, then only return metrics linked to services that the user can see.
         */
        if (!$centreon->user->admin) {
            $acl = new CentreonACL($centreon->user->user_id, $centreon->user->admin);
            $query .= ' AND m.index_id = i.id AND i.service_id IN (' .
                $acl->getServicesString('ID', $this->pearDBMonitoring) . ') ';
        }

        $query .= ' ORDER BY `metric_name` COLLATE utf8_general_ci ';
        $stmt = $this->pearDBMonitoring->prepare($query);
        $stmt->bindParam(':name', $queryValues['name'], \PDO::PARAM_STR);
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = array(
                'id' => $row['metric_name'],
                'text' => $row['metric_name']
            );
        }

        return $metrics;
    }

    /**
     * @return array
     *
     * @throws RestBadRequestException
     */
    protected function getListByService()
    {
        global $centreon;

        $queryValues = array();
        if (isset($this->arguments['q'])) {
            $queryValues['name'] = '%' . (string)$this->arguments['q'] . '%';
        } else {
            $queryValues['name'] = '%%';
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS m.metric_id,
            CONCAT(h.name," - ", s.description, " - ",  m.metric_name) AS fullname
            FROM metrics m, hosts h, services s, index_data i
            WHERE m.index_id = i.id
            AND h.host_id = i.host_id
            AND s.service_id = i.service_id
            AND h.enabled = 1
            AND s.enabled = 1
            AND CONCAT(h.name," - ", s.description, " - ",  m.metric_name) LIKE :name ';

        if (!$centreon->user->admin) {
            $acl = new CentreonACL($centreon->user->user_id, $centreon->user->admin);
            $query .= 'AND s.service_id IN (' . $acl->getServicesString('ID', $this->pearDBMonitoring) . ') ';
        }

        $query .= ' ORDER BY fullname COLLATE utf8_general_ci ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('400 Bad Request, limit error');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $query .= 'LIMIT :offset,:limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDBMonitoring->prepare($query);
        $stmt->bindParam(':name', $queryValues['name'], \PDO::PARAM_STR);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], \PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], \PDO::PARAM_INT);
        }
        $stmt->execute();
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = array(
                'id' => $row['metric_id'],
                'text' => $row['fullname']
            );
        }
        return array(
            'items' => $metrics,
            'total' => (int) $this->pearDBMonitoring->numberRows()
        );
    }

    /**
     * @return array
     *
     * @throws RestBadRequestException
     */
    protected function getListOfMetricsByService()
    {
        $queryValues = array();
        if (isset($this->arguments['id'])) {
            $tmp = explode('-', $this->arguments['id']);
            $queryValues['host_id'] = (int)$tmp[0];
            $queryValues['service_id'] = (int)$tmp[1];
        } else {
            throw new \RestBadRequestException('400 Bad Request, invalid service id');
        }
        $nameArg = filter_var($this->arguments['q'] ?? false, FILTER_SANITIZE_STRING);
        if ($nameArg !== false) {
            $queryValues['name'] = '%' . $nameArg . '%';
        } else {
            $queryValues['name'] = '%%';
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS m.metric_id, ' .
            'm.metric_name AS name ' .
            'FROM metrics m, hosts h, services s, index_data i ' .
            'WHERE m.index_id = i.id ' .
            'AND h.host_id = i.host_id ' .
            'AND s.service_id = i.service_id ' .
            'AND h.enabled = 1 ' .
            'AND s.enabled = 1 ' .
            'AND m.metric_name LIKE :name ' .
            'AND h.host_id = :host_id AND s.service_id = :service_id ' .
            'ORDER BY m.metric_name ';

        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            if (
                !is_numeric($this->arguments['page'])
                || !is_numeric($this->arguments['page_limit'])
                || $this->arguments['page_limit'] < 1
            ) {
                throw new \RestBadRequestException('400 Bad Request, limit error');
            }
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $query .= 'LIMIT :offset,:limit';
            $queryValues['offset'] = (int)$offset;
            $queryValues['limit'] = (int)$this->arguments['page_limit'];
        }
        $stmt = $this->pearDBMonitoring->prepare($query);
        $stmt->bindParam(':name', $queryValues['name'], \PDO::PARAM_STR);
        $stmt->bindParam(':host_id', $queryValues['host_id'], \PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $queryValues['service_id'], \PDO::PARAM_INT);
        if (isset($queryValues['offset'])) {
            $stmt->bindParam(':offset', $queryValues["offset"], \PDO::PARAM_INT);
            $stmt->bindParam(':limit', $queryValues["limit"], \PDO::PARAM_INT);
        }
        $stmt->execute();
        $metrics = array();
        while ($row = $stmt->fetch()) {
            $metrics[] = array(
                'id' => $row['metric_id'],
                'text' => $row['name']
            );
        }
        return array(
            'items' => $metrics,
            'total' => (int) $this->pearDBMonitoring->numberRows()
        );
    }

    /**
     * Get last metrics value by services or/and metrics
     *
     * @return array | null if arguments are not set
     */
    public function getLastMetricsData()
    {
        if (!isset($this->arguments['services']) && !isset($this->arguments['metrics'])) {
            self::sendResult([]);
        }

        return $this->lastMetricsData(
            $this->arguments['services'] ?? '',
            $this->arguments['metrics'] ?? ''
        );
    }


    /**
     * Get metrics data by service or/and metrics
     *
     * @return array | null if arguments are not set
     */
    public function getMetricsData()
    {
        if (!isset($this->arguments['services']) && !isset($this->arguments['metrics'])) {
            self::sendResult([]);
        }

        return $this->metricsData(
            $this->arguments['services'] ?? '',
            $this->arguments['metrics'] ?? ''
        );
    }

    /**
     * Get metrics datas for a service
     *
     * @return array
     */
    public function getMetricsDataByService()
    {
        if (!isset($this->arguments['ids'])) {
            self::sendResult([]);
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = [];

        if (isset($this->arguments['type']) && $this->arguments['type'] === 'ng') {
            foreach ($ids as $id) {
                $result[] = $this->serviceDatasNg($id);
            }
        } else {
            foreach ($ids as $id) {
                $result[] = $this->serviceDatas($id);
            }
        }

        return $result;
    }

    /**
     * Get metrics datas for a metric
     *
     * @return mixed
     */
    public function getMetricsDataByMetric()
    {
        if (!isset($this->arguments['ids'])) {
            self::sendResult(array());
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = array();

        if (isset($this->arguments['type']) && $this->arguments['type'] === 'ng') {
            foreach ($ids as $id) {
                list($hostId, $serviceId, $metricId) = explode('_', $id);
                $hostId = (int)$hostId;
                $serviceId = (int)$serviceId;
                $metricId = (int)$metricId;

                $result[] = $this->serviceDatasNg($hostId . '_' . $serviceId, $metricId);
            }
        } else {
            foreach ($ids as $id) {
                list($hostId, $serviceId, $metricId) = explode('_', $id);
                $hostId = (int)$hostId;
                $serviceId = (int)$serviceId;
                $metricId = (int)$metricId;

                $result[] = $this->serviceDatas($hostId . '_' . $serviceId, $metricId);
            }
        }

        return $result;
    }

    /**
     * Check acl for user
     *
     * @param int    $hostId     Host id to check
     * @param int    $serviceId  Service id to check
     * @param string $aclGroups  String with user acl groups
     * @param int    $isAdmin    User is admin or not
     *
     * @return bool if the user is allowed to get service information
     */
    private function checkAcl($hostId, $serviceId, ?array $aclGroups, $isAdmin = true): bool
    {
        if (!$isAdmin) {
            $query = 'SELECT service_id ' .
                'FROM centreon_acl ' .
                'WHERE host_id = :hostId ' .
                'AND service_id = :serviceId ' .
                'AND group_id IN (' . $aclGroups . ')';

            $stmt = $this->pearDBMonitoring->prepare($query);
            $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
            $stmt->bindParam(':serviceId', $serviceId, \PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult || $stmt->rowCount() === 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get array builded from arguments
     *
     * @param string $services List of services (like hostId_serviceId,hostId2_serviceId2,...)
     * @param string $metrics  List of metrics (like metricId,metricId2,...)
     *
     * @return mixed
     */
    private function manageMetricsDataArguments($services, $metrics)
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclGroups = null;

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
        }

        if (
            !isset($this->arguments['start']) || !is_numeric($this->arguments['start'])
            || !isset($this->arguments['end']) || !is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        /*
         * Format:
         *    {
         *      "hostId_serviceId": {}, # no metricsId means: all metrics
         *      "hostId2_serviceId2: { "metricId": 1, "metricId2": 1 }
         *    }
         */
        $selectedMetrics = [];
        if (is_string($services) && strlen($services) > 0) {
            foreach (explode(',', $services) as $service) {
                list($hostId, $serviceId) = explode('_', $service);
                if (!is_numeric($hostId) || !is_numeric($serviceId)) {
                    continue;
                }
                if (isset($selectedMetrics[$service])) {
                    continue;
                }

                if (!$this->checkAcl($hostId, $serviceId, $aclGroups, $isAdmin)) {
                    continue;
                }
                $selectedMetrics[$service] = [];
            }
        }

        if (is_string($metrics) && strlen($metrics) > 0) {
            $filter = '';
            $filterAppend = '';
            $queryValues = [];
            foreach (explode(',', $metrics) as $metricId) {
                if (!is_numeric($metricId)) {
                    continue;
                }
                $filter .= $filterAppend . ' :metric' . $metricId;
                $queryValues[':metric' . $metricId] = $metricId;
                $filterAppend = ',';
            }

            if ($filter === '') {
                return $selectedMetrics;
            }

            $stmt = $this->pearDBMonitoring->prepare(
                'SELECT metric_id, host_id, service_id
                 FROM metrics, index_data
                 WHERE metrics.metric_id IN (' . $filter . ')
                 AND metrics.index_id = index_data.id'
            );
            foreach ($queryValues as $param => $value) {
                $stmt->bindValue($param, $value, \PDO::PARAM_INT);
            }
            $stmt->execute();

            while ($row = $stmt->fetch()) {
                if (
                    isset($selectedMetrics[$row['host_id'] . '_' . $row['service_id']])
                    && count($selectedMetrics[$row['host_id'] . '_' . $row['service_id']]) <= 0
                ) {
                    continue;
                }
                if (!isset($selectedMetrics[$row['host_id'] . '_' . $row['service_id']])) {
                    if (!$this->checkAcl($row['host_id'], $row['service_id'], $aclGroups, $isAdmin)) {
                        continue;
                    }
                    $selectedMetrics[$row['host_id'] . '_' . $row['service_id']] = [];
                }
                $selectedMetrics[$row['host_id'] . '_' . $row['service_id']][$row['metric_id']] = 1;
            }
        }

        return $selectedMetrics;
    }

    /**
     * Get last data for metrics (by services and/or metrics)
     *
     * @param string $services List of services (like hostId_serviceId,hostId2_serviceId2,...)
     * @param string $metrics  List of metrics (like metricId,metricId2,...)
     *
     * @return array
     *
     * @throws Exception
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    protected function lastMetricsData($services, $metrics)
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;
        $aclGroups = null;

        $query = '';
        $filterHostIds = [];
        $filterServiceIds = [];
        $filterMetricIds = [];

        if (is_string($services) && strlen($services) > 0) {
            foreach (explode(',', $services) as $service) {
                list($hostId, $serviceId) = explode('_', $service);
                if (!is_numeric($hostId) || !is_numeric($serviceId)) {
                    continue;
                }
                $filterHostIds[':host' . $hostId] = $hostId;
                $filterServiceIds[':service' . $serviceId] = $serviceId;
            }

            if (!empty($filterHostIds) && !empty($filterServiceIds)) {
                $query = '
                    SELECT i.host_id, i.service_id, m.*
                    FROM index_data i, metrics m
                    WHERE i.host_id IN (' . implode(',', array_keys($filterHostIds)) . ')
                    AND i.service_id IN (' . implode(',', array_keys($filterServiceIds)) . ')
                    AND i.id = m.index_id';
            }
        }

        if (is_string($metrics) && strlen($metrics) > 0) {
            foreach (explode(',', $metrics) as $metricId) {
                if (!is_numeric($metricId)) {
                    continue;
                }
                $filterMetricIds[':metric' . $metricId] = $metricId;
            }

            if (!empty($filterMetricIds)) {
                if ($query !== '') {
                    $query .= ' UNION ';
                }
                $query .= '
                    SELECT i.host_id, i.service_id, m.*
                    FROM metrics m, index_data i
                    WHERE m.metric_id IN (' . implode(',', array_keys($filterMetricIds)) . ')
                    AND m.index_id = i.id';
            }
        }

        if ($query === '') {
            throw new \Exception("No metrics found");
        }

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
            $query = '
                SELECT ms.* FROM (' . $query . ') as ms, centreon_acl ca
                WHERE EXISTS (
                    SELECT 1
                    FROM centreon_acl ca
                    WHERE ca.host_id = ms.host_id
                    AND ca.service_id = ms.service_id
                    AND ca.group_id IN (' . $aclGroups . '))';
        }

        $stmt = $this->pearDBMonitoring->prepare($query);
        foreach ([$filterHostIds, $filterServiceIds, $filterMetricIds] as $filterParams) {
            foreach ($filterParams as $param => $value) {
                $stmt->bindValue($param, $value, \PDO::PARAM_INT);
            }
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get data for metrics (by services and/or metrics)
     *
     * @param string $services List of services (like hostId_serviceId,hostId2_serviceId2,...)
     * @param string $metrics  List of metrics (like metricId,metricId2,...)
     *
     * @return array
     *
     * @throws Exception
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    protected function metricsData($services, $metrics)
    {
        global $centreon;

        $selectedMetrics = $this->manageMetricsDataArguments($services, $metrics);
        $multipleServices = count(array_keys($selectedMetrics)) > 1 ? true : false;

        /* Prepare graph */
        try {
            $graph = new CentreonGraphNg($centreon->user->user_id);
            $graph->setMultipleServices($multipleServices);
            foreach ($selectedMetrics as $service => $metrics) {
                list($hostId, $serviceId) = explode('_', $service);
                if (count(array_keys($metrics)) <= 0) {
                    $graph->addServiceMetrics($hostId, $serviceId);
                } else {
                    $graph->addServiceCustomMetrics($hostId, $serviceId, $metrics);
                }
            }
        } catch (Exception $e) {
            throw new RestNotFoundException("Graph not found");
        }

        $result = $graph->getGraph($this->arguments['start'], $this->arguments['end']);

        if (!$multipleServices && count($selectedMetrics) > 0) {
            /* Get extra information (downtime/acknowledgment) */
            $result['acknowledge'] = array();
            $result['downtime'] = array();
            list($hostId, $serviceId) = explode('_', array_key_first($selectedMetrics));
            $result['acknowledge'] = $this->getAcknowledgements(
                (int)$hostId,
                (int)$serviceId,
                (int)$this->arguments['start'],
                (int)$this->arguments['end']
            );
            $result['downtime'] = $this->getDowntimePeriods(
                (int)$hostId,
                (int)$serviceId,
                (int)$this->arguments['start'],
                (int)$this->arguments['end']
            );
        }

        return $result;
    }

    /**
     * Get the status for a service
     *
     * @return mixed
     *
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    public function getStatusByService()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
        }

        /* Validate options */
        if (
            !isset($this->arguments['start']) || !is_numeric($this->arguments['start'])
            || !isset($this->arguments['end']) || !is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        /* Get the numbers of points */
        $rows = 200;
        if (isset($this->arguments['rows'])) {
            if (!is_numeric($this->arguments['rows'])) {
                throw new RestBadRequestException("Bad parameters");
            }
            $rows = $this->arguments['rows'];
        }
        if ($rows < 10) {
            throw new RestBadRequestException("The rows must be greater as 10");
        }

        if (!isset($this->arguments['ids'])) {
            self::sendResult(array());
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = array();

        foreach ($ids as $id) {
            list($hostId, $serviceId) = explode('_', $id);
            if (
                !is_numeric($hostId) ||
                !is_numeric($serviceId)
            ) {
                throw new RestBadRequestException("Bad parameters");
            }

            /* Check ACL is not admin */
            if (!$isAdmin) {
                $query = 'SELECT service_id ' .
                    'FROM centreon_acl ' .
                    'WHERE host_id = :hostId ' .
                    'AND service_id = :serviceId ' .
                    'AND group_id IN (' . $aclGroups . ')';

                $stmt = $this->pearDBMonitoring->prepare($query);
                $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
                $stmt->bindParam(':serviceId', $serviceId, \PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }

                if ($stmt->rowCount() === 0) {
                    throw new RestForbiddenException("Access denied");
                }
            }

            /* Prepare graph */
            try {
                /* Get index data */
                $indexData = CentreonGraphStatus::getIndexId($hostId, $serviceId, $this->pearDBMonitoring);
                $graph = new CentreonGraphStatus($indexData, $start, $end);
            } catch (Exception $e) {
                throw new RestNotFoundException("Graph not found");
            }

            $statusData = $graph->getData($rows);

            /* Get comments for this services */
            $comments = array();
            $query = 'SELECT `value` FROM `options` WHERE `key` = "display_comment_chart"';
            $res = $this->pearDB->query($query);
            $row = $res->fetch();
            if ($row && $row['value'] === '1') {
                $queryComment = 'SELECT `entry_time`, `author`, `data` ' .
                    'FROM comments ' .
                    'WHERE host_id = :hostId ' .
                    'AND service_id = :serviceId ' .
                    'AND type = 2 ' .
                    'AND entry_type = 1 ' .
                    'AND deletion_time IS NULL ' .
                    'AND :start < entry_time ' .
                    'AND :end > entry_time';
                $stmt = $this->pearDBMonitoring->prepare($queryComment);
                $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
                $stmt->bindParam(':serviceId', $serviceId, \PDO::PARAM_INT);
                $stmt->bindParam(':start', $start, \PDO::PARAM_INT);
                $stmt->bindParam(':end', $end, \PDO::PARAM_INT);
                $dbResult = $stmt->execute();
                if (!$dbResult) {
                    throw new \Exception("An error occured");
                }

                while ($row = $stmt->fetch()) {
                    $comments[] = array(
                        'author' => $row['author'],
                        'comment' => $row['data'],
                        'time' => $row['entry_time']
                    );
                }
            }
            $result[] = array(
                'service_id' => $id,
                'data' => array(
                    'status' => $statusData,
                    'comments' => $comments
                ),
                'size' => $rows
            );
        }
        return $result;
    }

    /**
     * Get data for a service can be filtered by metric (new backend)
     *
     * @param string $id     The service id like hostId_serviceId
     * @param int    $metric The metric id
     *
     * @return array
     *
     * @throws Exception
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    protected function serviceDatasNg($id, $metric = null)
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
        }

        if (
            !isset($this->arguments['start']) || !is_numeric($this->arguments['start'])
            || !isset($this->arguments['end']) || !is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        list($hostId, $serviceId) = explode('_', $id);
        if (
            !is_numeric($hostId) ||
            !is_numeric($serviceId)
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        /* Check ACL is not admin */
        if (!$isAdmin) {
            $query = 'SELECT service_id ' .
                'FROM centreon_acl ' .
                'WHERE host_id = :hostId ' .
                'AND service_id = :serviceId ' .
                'AND group_id IN (' . $aclGroups . ')';

            $stmt = $this->pearDBMonitoring->prepare($query);
            $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
            $stmt->bindParam(':serviceId', $serviceId, \PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            if ($stmt->rowCount() === 0) {
                throw new RestForbiddenException("Access denied");
            }
        }

        /* Prepare graph */
        try {
            $graph = new CentreonGraphNg($userId);
            if (is_null($metric)) {
                $graph->addServiceMetrics($hostId, $serviceId);
            } else {
                $graph->addMetric($metric);
            }
        } catch (Exception $e) {
            throw new RestNotFoundException("Graph not found");
        }

        $result = $graph->getGraph($this->arguments['start'], $this->arguments['end']);

        /* Get extra information (downtime/acknowledgment) */
        $result['acknowledge'] = array();
        $result['downtime'] = array();
        $query = 'SELECT `value` FROM `options` WHERE `key` = "display_downtime_chart"';

        $res = $this->pearDB->query($query);

        $row = $res->fetch();
        if ($row && $row['value'] === '1') {
            $result['acknowledge'] = $this->getAcknowlegePeriods($hostId, $serviceId, $start, $end);
            $result['downtime'] = $this->getDowntimePeriods($hostId, $serviceId, $start, $end);
        }

        return $result;
    }

    /**
     * Get data for a service can be filtered by metric
     *
     * @param string $id     The service id like hostId_serviceId
     * @param int    $metric The metric id
     *
     * @return array
     *
     * @throws Exception
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    protected function serviceDatas($id, $metric = null)
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
            $aclGroups = $acl->getAccessGroupsString();
        }

        if (
            !isset($this->arguments['start']) ||
            !is_numeric($this->arguments['start']) ||
            !isset($this->arguments['end']) ||
            !is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        /* Get the numbers of points */
        $rows = 200;
        if (isset($this->arguments['rows'])) {
            if (!is_numeric($this->arguments['rows'])) {
                throw new RestBadRequestException("Bad parameters");
            }
            $rows = $this->arguments['rows'];
        }
        if ($rows < 10) {
            throw new RestBadRequestException("The rows must be greater as 10");
        }

        list($hostId, $serviceId) = explode('_', $id);
        if (
            !is_numeric($hostId) ||
            !is_numeric($serviceId)
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        /* Check ACL is not admin */
        if (!$isAdmin) {
            $query = 'SELECT service_id ' .
                'FROM centreon_acl ' .
                'WHERE host_id = :hostId ' .
                'AND service_id = :serviceId ' .
                'AND group_id IN (' . $aclGroups . ')';

            $stmt = $this->pearDBMonitoring->prepare($query);
            $stmt->bindParam(':hostId', $hostId, \PDO::PARAM_INT);
            $stmt->bindParam(':serviceId', $serviceId, \PDO::PARAM_INT);
            $dbResult = $stmt->execute();
            if (!$dbResult) {
                throw new \Exception("An error occured");
            }
            if ($stmt->rowCount() === 0) {
                throw new RestForbiddenException("Access denied");
            }
        }

        /* Prepare graph */
        try {
            /* Get index data */
            $indexData = CentreonGraphService::getIndexId($hostId, $serviceId, $this->pearDBMonitoring);
            $graph = new CentreonGraphService($indexData, $userId);
        } catch (Exception $e) {
            throw new RestNotFoundException("Graph not found");
        }
        if (!is_null($metric)) {
            $graph->setMetricList($metric);
        }
        $graph->setRRDOption("start", $start);
        $graph->setRRDOption("end", $end);
        $graph->setTemplate();
        $graph->initCurveList();
        $graph->createLegend();
        $serviceData = $graph->getData($rows);

        /* Replace NaN */
        for ($i = 0; $i < count($serviceData); $i++) {
            if (isset($serviceData[$i]['data'])) {
                $times = array_keys($serviceData[$i]['data']);
                $values = array_map(
                    array($this, "convertNaN"),
                    array_values($serviceData[$i]['data'])
                );
            }
            $serviceData[$i]['data'] = $values;
            $serviceData[$i]['label'] = $serviceData[$i]['legend'];
            unset($serviceData[$i]['legend']);
            $serviceData[$i]['type'] = $serviceData[$i]['graph_type'];
            unset($serviceData[$i]['graph_type']);
        }

        /* Get extra information (downtime/acknowledgment) */
        $acks = array();
        $downtimes = array();
        $query = 'SELECT `value` FROM `options` WHERE `key` = "display_downtime_chart"';
        $res = $this->pearDB->query($query);
        $row = $res->fetch();
        if (!is_null($row) && $row['value'] === '1') {
            $acks = $this->getAcknowlegePeriods($hostId, $serviceId, $start, $end);
            $downtimes = $this->getDowntimePeriods($hostId, $serviceId, $start, $end);
        }

        /* Prepare limits */
        $limits = $graph->getLimits();

        /* Prepare legends */
        $legends = $graph->getLegends();

        $result = array(
            'service_id' => $id,
            'data' => $serviceData,
            'times' => $times,
            'size' => $rows,
            'acknowledge' => $acks,
            'downtime' => $downtimes,
            'limits' => $limits,
            'legends' => $legends,
            'base' => $graph->getBase()
        );

        return $result;
    }

    /**
     * Get metrics Data by poller
     *
     * @return array
     *
     * @throws Exception
     * @throws RestBadRequestException
     * @throws RestForbiddenException
     * @throws RestNotFoundException
     */
    public function getMetricsDataByPoller()
    {
        global $centreon;

        $userId = $centreon->user->user_id;
        $isAdmin = $centreon->user->admin;

        /* Get ACL if user is not admin */
        if (!$isAdmin) {
            $acl = new CentreonACL($userId, $isAdmin);
        }

        /* Validate options */
        if (
            !isset($this->arguments['ids']) ||
            !isset($this->arguments['start']) ||
            !is_numeric($this->arguments['start']) ||
            !isset($this->arguments['end']) ||
            !is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $explodedId = explode('-', $this->arguments['ids']);
        $id = $explodedId[0];
        $graphName = $explodedId[1];


        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        /* Get the numbers of points */
        $rows = 200;
        if (isset($this->arguments['rows'])) {
            if (!is_numeric($this->arguments['rows'])) {
                throw new RestBadRequestException("Bad parameters");
            }
            $rows = $this->arguments['rows'];
        }
        if ($rows < 10) {
            throw new RestBadRequestException("The rows must be greater as 10");
        }

        // Init graph object
        try {
            $graphPollerObject = new CentreonGraphPoller(
                $this->pearDB,
                $this->pearDBMonitoring
            );
            $graphPollerObject->setPoller($id, $graphName);
        } catch (\Exception $e) {
            throw new RestNotFoundException("Graph not found");
        }

        $result = $graphPollerObject->getGraph($start, $end);

        return array($result);
    }


    /**
     * Function for test is a value is NaN
     *
     * @param  mixed $element The element to test
     *
     * @return mixed null if NaN else the element
     */
    protected function convertNaN($element)
    {
        if (strtoupper($element) == 'NAN') {
            return null;
        }
        return $element;
    }

    /**
     * Get the list of a acknowlegments for a service during a period
     *
     * @param int $hostId the host id
     * @param int $serviceId the service id
     * @param int $start the start timestamp
     * @param int $end the end timestamp
     *
     * @return array The list of acknowledgements
     */
    protected function getAcknowledgements(int $hostId, int $serviceId, int $start, int $end): array
    {
        $query = 'SELECT entry_time as start, deletion_time as end, author, comment_data ' .
            'FROM acknowledgements ' .
            'WHERE host_id = :hostId ' .
            'AND service_id = :serviceId ' .
            'AND (entry_time >= :start AND entry_time <= :end)';
        $stmt = $this->pearDBMonitoring->prepare($query);
        $stmt->bindValue(':hostId', $hostId, \PDO::PARAM_INT);
        $stmt->bindValue(':serviceId', $serviceId, \PDO::PARAM_INT);
        $stmt->bindValue(':start', $start, \PDO::PARAM_INT);
        $stmt->bindValue(':end', $end, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the list of a acknowlegment for a service during a period
     *
     * @return array The list of ack
     */
    protected function getAcknowlegePeriods($hostId, $serviceId, $start, $end)
    {
        $queryValues = array();

        $query = 'SELECT entry_time as start, deletion_time as end ' .
            'FROM acknowledgements ' .
            'WHERE host_id = :hostId ' .
            'AND service_id = :serviceId ' .
            'AND ( ' .
            '(entry_time <= :end AND :end <= deletion_time) ' .
            'OR (entry_time <= :start AND :start <= deletion_time) ' .
            'OR (entry_time >= :start AND :end >= deletion_time) ' .
            'OR(deletion_time IS NULL) ' .
            ')';

        $queryValues['hostId'] = (int)$hostId;
        $queryValues['serviceId'] = (int)$serviceId;
        $queryValues['end'] = (int)$end;
        $queryValues['start'] = (int)$start;
        return $this->executeQueryPeriods($query, $start, $end, $queryValues);
    }

    /**
     * Get list of downtime periods by service
     *
     * @param int $hostId the host id
     * @param int $serviceId the service id
     * @param int $start the start timestamp
     * @param int $end the end timestamp
     *
     * @return array
     */
    protected function getDowntimePeriods($hostId, $serviceId, $start, $end): array
    {
        $query = 'SELECT actual_start_time as start, actual_end_time as end ' .
            'FROM downtimes ' .
            'WHERE host_id = :hostId AND service_id = :serviceId ' .
            'AND ' .
            'actual_start_time <= :end AND (actual_end_time >= :start OR actual_end_time is NULL)';
        $queryValues['hostId'] = (int)$hostId;
        $queryValues['serviceId'] = (int)$serviceId;
        $queryValues['end'] = (int)$end;
        $queryValues['start'] = (int)$start;
        return $this->executeQueryPeriods($query, $start, $end, $queryValues);
    }

    /**
     * @param string $query
     * @param int    $start
     * @param int    $end
     * @param mixed  $queryValues
     *
     * @return array
     */
    protected function executeQueryPeriods($query, $start, $end, $queryValues)
    {
        $periods = array();
        $stmt = $this->pearDBMonitoring->prepare($query);
        foreach ($queryValues as $key => $value) {
            $stmt->bindValue(':' . $key, $value, \PDO::PARAM_INT);
        }
        $dbResult = $stmt->execute();
        if (!$dbResult) {
            throw new \Exception("An error occured");
        }

        while ($row = $stmt->fetch()) {
            $period = array(
                'start' => $row['start'],
                'end' => $row['end']
            );
            if (
                $start > $row['start']
                || is_null($row['start'])
                || $row['start'] === ''
            ) {
                $period['start'] = $start;
            }
            if (
                $end < $row['end']
                || is_null($row['end'])
                || $row['end'] === ''
            ) {
                $period['end'] = $end;
            }
            $periods[] = $period;
        }
        return $periods;
    }

    /**
     * Authorize to access to the action
     *
     * @param string  $action     The action name
     * @param array   $user       The current user
     * @param boolean $isInternal If the api is call in internal
     *
     * @return boolean If the user has access to the action
     */
    public function authorize($action, $user, $isInternal = false)
    {
        if (
            parent::authorize($action, $user, $isInternal)
            || ($user && $user->hasAccessRestApiRealtime())
        ) {
            return true;
        }

        return false;
    }
}
