<?php
/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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
require_once _CENTREON_PATH_ . "/www/class/centreonGraphService.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphPoller.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonGraphStatus.class.php";
require_once dirname(__FILE__) . "/webService.class.php";

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
     */
    public function getList()
    {
        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }
        $query = 'SELECT DISTINCT(`metric_name`) COLLATE utf8_bin as "metric_name" ' .
            'FROM `metrics` ' .
            'WHERE metric_name LIKE ? ' .
            'ORDER BY `metric_name` COLLATE utf8_general_ci ';
        $stmt = $this->pearDBMonitoring->prepare($query);
        $DBRESULT = $this->pearDBMonitoring->execute($stmt, array('%' . (string)$q . '%'));

        $metrics = array();
        while ($row = $DBRESULT->fetchRow()) {
            $metrics[] = array(
                'id' => $row['metric_name'],
                'text' => $row['metric_name']
            );
        }

        return $metrics;
    }


    /**
     * @return array
     */
    protected function getListByService()
    {
        $queryValues = array();

        if (false === isset($this->arguments['q'])) {
            $q = '';
        } else {
            $q = $this->arguments['q'];
        }
        $queryValues[] = '%' . (string)$q . '%';
        if (isset($this->arguments['page_limit']) && isset($this->arguments['page'])) {
            $offset = ($this->arguments['page'] - 1) * $this->arguments['page_limit'];
            $range = 'LIMIT ?,?';
            $queryValues[] = (int)$offset;
            $queryValues[] = (int)$this->arguments['page_limit'];
        } else {
            $range = '';
        }

        $query = 'SELECT SQL_CALC_FOUND_ROWS m.metric_id, ' .
            'CONCAT(h.name," - ", s.description, " - ",  m.metric_name) AS fullname ' .
            'FROM metrics m, hosts h, services s, index_data i ' .
            'WHERE m.index_id = i.id ' .
            'AND h.host_id = i.host_id ' .
            'AND s.service_id = i.service_id ' .
            'AND h.enabled = 1 ' .
            'AND s.enabled = 1 ' .
            'AND CONCAT(h.name," - ", s.description, " - ",  m.metric_name) LIKE ? ' .
            'ORDER BY CONCAT(h.name," - ", s.description, " - ",  m.metric_name) COLLATE utf8_general_ci ' .
            $range;

        $stmt = $this->pearDBMonitoring->prepare($query);
        $dbResult = $this->pearDBMonitoring->execute($stmt, $queryValues);
        $total = $this->pearDB->numberRows();

        $metrics = array();
        while ($row = $dbResult->fetchRow()) {
            $metrics[] = array(
                'id' => $row['metric_id'],
                'text' => $row['fullname']
            );
        }
        return array(
            'items' => $metrics,
            'total' => $total
        );
    }


    /**
     * Get metrics datas for a service
     *
     * @return array
     */
    public function getMetricsDataByService()
    {
        if (false === isset($this->arguments['ids'])) {
            self::sendJson(array());
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = array();

        foreach ($ids as $id) {
            $result[] = $this->serviceDatas($id);
        }

        return $result;
    }

    /**
     * Get metrics datas for a metric
     *
     * @return array
     */
    public function getMetricsDataByMetric()
    {
        if (false === isset($this->arguments['ids'])) {
            self::sendJson(array());
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = array();

        foreach ($ids as $id) {
            list($hostId, $serviceId, $metricId) = explode('_', $id);
            $result[] = $this->serviceDatas($hostId . '_' . $serviceId, $metricId);
        }

        return $result;
    }

    /**
     * Get the status for a service
     *
     * @return array
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
        if (false === isset($this->arguments['start']) ||
            false === is_numeric($this->arguments['start']) ||
            false === isset($this->arguments['end']) ||
            false === is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        /* Get the numbers of points */
        $rows = 200;
        if (isset($this->arguments['rows'])) {
            if (false === is_numeric($this->arguments['rows'])) {
                throw new RestBadRequestException("Bad parameters");
            }
            $rows = $this->arguments['rows'];
        }
        if ($rows < 10) {
            throw new RestBadRequestException("The rows must be greater as 10");
        }

        if (false === isset($this->arguments['ids'])) {
            self::sendJson(array());
        }

        /* Get the list of service ID */
        $ids = explode(',', $this->arguments['ids']);
        $result = array();

        foreach ($ids as $id) {
            list($hostId, $serviceId) = explode('_', $id);
            if (false === is_numeric($hostId) ||
                false === is_numeric($serviceId)
            ) {
                throw new RestBadRequestException("Bad parameters");
            }

            /* Check ACL is not admin */
            if (!$isAdmin) {
                $query = 'SELECT service_id ' .
                    'FROM centreon_acl ' .
                    'WHERE host_id = ? ' .
                    'AND service_id = ? ' .
                    'AND group_id IN (' . $aclGroups . ')';

                $stmt = $this->pearDBMonitoring->prepare($query);
                $res = $this->pearDBMonitoring->execute($stmt, array((int)$hostId, (int)$serviceId));

                if (0 == $res->numRows()) {
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

            if (false === PEAR::isError($res)) {
                $row = $res->fetchRow();
                if (false === is_null($row) && $row['value'] === '1') {
                    $queryComment = 'SELECT `entry_time`, `author`, `data` ' .
                        'FROM comments ' .
                        'WHERE host_id = ? ' .
                        'AND service_id = ? ' .
                        'AND entry_type = 1 ' .
                        'AND deletion_time IS NULL ' .
                        'AND ? < entry_time ' .
                        'AND ? > entry_time';
                    $stmt = $this->pearDBMonitoring->prepare($queryComment);
                    $res = $this->pearDBMonitoring->execute($stmt, array(
                        (int)$hostId,
                        (int)$serviceId,
                        (int)$start,
                        (int)$end
                    ));

                    if (false === PEAR::isError($res)) {
                        while ($row = $res->fetchRow()) {
                            $comments[] = array(
                                'author' => $row['author'],
                                'comment' => $row['data'],
                                'time' => $row['entry_time']
                            );
                        }
                    }
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
     * Get data for a service can be filtered by metric
     *
     * @param string $id The service id like hostId_serviceId
     * @param int $metric The metric id
     * @return array
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

        if (false === isset($this->arguments['start']) ||
            false === is_numeric($this->arguments['start']) ||
            false === isset($this->arguments['end']) ||
            false === is_numeric($this->arguments['end'])
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        $start = $this->arguments['start'];
        $end = $this->arguments['end'];

        /* Get the numbers of points */
        $rows = 200;
        if (isset($this->arguments['rows'])) {
            if (false === is_numeric($this->arguments['rows'])) {
                throw new RestBadRequestException("Bad parameters");
            }
            $rows = $this->arguments['rows'];
        }
        if ($rows < 10) {
            throw new RestBadRequestException("The rows must be greater as 10");
        }

        list($hostId, $serviceId) = explode('_', $id);
        if (false === is_numeric($hostId) ||
            false === is_numeric($serviceId)
        ) {
            throw new RestBadRequestException("Bad parameters");
        }

        /* Check ACL is not admin */
        if (!$isAdmin) {
            $query = 'SELECT service_id ' .
                'FROM centreon_acl ' .
                'WHERE host_id = ? ' .
                'AND service_id = ? ' .
                'AND group_id IN (' . $aclGroups . ')';

            $stmt = $this->pearDBMonitoring->prepare($query);
            $res = $this->pearDBMonitoring->execute($stmt, array((int)$hostId, (int)$serviceId));

            if (0 == $res->numRows()) {
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
        if (false === is_null($metric)) {
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

        if (false === PEAR::isError($res)) {
            $row = $res->fetchRow();
            if (false === is_null($row) && $row['value'] === '1') {
                $acks = $this->getAcknowlegePeriods($hostId, $serviceId, $start, $end);
                $downtimes = $this->getDowntimePeriods($hostId, $serviceId, $start, $end);
            }
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
            'legends' => $legends
        );

        return $result;
    }

    /**
     * Get metrics Data by poller
     *
     * @return array
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
        if (false === isset($this->arguments['ids']) ||
            false === isset($this->arguments['start']) ||
            false === is_numeric($this->arguments['start']) ||
            false === isset($this->arguments['end']) ||
            false === is_numeric($this->arguments['end'])
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
            if (false === is_numeric($this->arguments['rows'])) {
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
                $this->pearDBMonitoring,
                $id,
                $userId,
                $start,
                $end
            );
            $graphPollerObject->setGraphName($graphName);
        } catch (\Exception $e) {
            throw new RestNotFoundException("Graph not found");
        }
        $pollerDatas = $graphPollerObject->getData($rows);

        for ($i = 0; $i < count($pollerDatas); $i++) {
            if (isset($pollerDatas[$i]['data'])) {
                $times = array_keys($pollerDatas[$i]['data']);
                $values = array_map(
                    array($this, "convertNaN"),
                    array_values($pollerDatas[$i]['data'])
                );
            }
            $pollerDatas[$i]['data'] = $values;
            $pollerDatas[$i]['label'] = $pollerDatas[$i]['legend'];
            unset($pollerDatas[$i]['legend']);
            $pollerDatas[$i]['type'] = $pollerDatas[$i]['graph_type'];
            unset($pollerDatas[$i]['graph_type']);
        }

        return array(
            array(
                'data' => $pollerDatas,
                'times' => $times,
                'size' => $rows,
                'acknowledge' => array(),
                'downtime' => array(),
                'limits' => array(
                    'min' => null,
                    'max' => null
                ),
                'legends' => $graphPollerObject->getLegends()
            )
        );
    }


    /**
     * Function for test is a value is NaN
     *
     * @param mixed $element The element to test
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
     * Get the list of a acknowlegment for a service during a period
     *
     * @return array The list of ack
     */
    protected function getAcknowlegePeriods($hostId, $serviceId, $start, $end)
    {
        $queryValues = array();

        $query = 'SELECT entry_time as start, deletion_time as end ' .
            'FROM acknowledgements ' .
            'WHERE host_id = ? ' .
            'AND service_id = ? ' .
            'AND ( ' .
            '(entry_time <= ? AND ? <= deletion_time) ' .
            'OR (entry_time <= ? AND ? <= deletion_time) ' .
            'OR (entry_time >= ? AND ? >= deletion_time) ' .
            'OR(deletion_time IS NULL) ' .
            ')';

        $queryValues[] = (int)$hostId;
        $queryValues[] = (int)$serviceId;
        $queryValues[] = (int)$end;
        $queryValues[] = (int)$end;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$end;
        return $this->executeQueryPeriods($query, $start, $end, $queryValues);
    }

    /**
     * @param $hostId
     * @param $serviceId
     * @param $start
     * @param $end
     * @return array
     */
    protected function getDowntimePeriods($hostId, $serviceId, $start, $end)
    {
        $query = 'SELECT actual_start_time as start, actual_end_time as end ' .
            'FROM downtimes ' .
            'WHERE host_id = ? AND service_id = ? ' .
            'AND (' .
            '(actual_start_time <= ? AND ? <= actual_end_time) ' .
            'OR (actual_start_time <= ? AND ? <= actual_end_time) ' .
            'OR (actual_start_time >= ? AND ? >= actual_end_time) ' .
            'OR (actual_start_time IS NOT NULL AND actual_end_time IS NULL) ' .
            ')';

        $queryValues[] = (int)$hostId;
        $queryValues[] = (int)$serviceId;
        $queryValues[] = (int)$end;
        $queryValues[] = (int)$end;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$start;
        $queryValues[] = (int)$end;
        return $this->executeQueryPeriods($query, $start, $end, $queryValues);
    }

    /**
     * @param $query
     * @param $start
     * @param $end
     * @param $queryValues
     * @return array
     */
    protected function executeQueryPeriods($query, $start, $end, $queryValues)
    {
        $periods = array();
        $stmt = $this->pearDBMonitoring->prepare($query);
        $res = $this->pearDBMonitoring->execute($stmt, $queryValues);

        while ($row = $res->fetchRow()) {
            $period = array(
                'start' => $row['start'],
                'end' => $row['end']
            );
            if ($start > $row['start']
                || is_null($row['start'])
                || $row['start'] === ''
            ) {
                $period['start'] = $start;
            }
            if ($end < $row['end']
                || is_null($row['end'])
                || $row['end'] === ''
            ) {
                $period['end'] = $end;
            }
            $periods[] = $period;
        }
        return $periods;
    }
}
