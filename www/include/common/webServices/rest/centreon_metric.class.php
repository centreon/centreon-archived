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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

global $centreon_path;
require_once $centreon_path . "/www/class/centreonBroker.class.php";
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/centreonGraphService.class.php";

class CentreonMetric {
    protected $pearDB;
    protected $pearDBMonitoring;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @return void
     */
    public function __construct()
    {
        $this->pearDB = new CentreonDB();
        $brk = new CentreonBroker($this->pearDB);
        if ($brk->getBroker() == 'broker') {
            $this->pearDBMonitoring = new CentreonDB('centstorage');
        } else {
            $this->pearDBMonitoring = new CentreonDB('ndo');
        }
    }

    /**
     * Get metric list
     *
     * @return array
     */
    public function getList($q = "")
    {
        $query = "SELECT DISTINCT(`metric_name`) COLLATE utf8_bin as \"metric_name\" FROM `metrics` WHERE metric_name LIKE '%$q%' ORDER BY `metric_name` COLLATE utf8_general_ci ";
        $DBRESULT = $this->pearDBMonitoring->query($query);
        $metrics = array();
        while ($row = $DBRESULT->fetchRow()) {
            $metrics[] = array(
                'id' => $row['metric_name'],
                'text' => $row['metric_name']
            );
        }

        echo json_encode($metrics);
    }
    
    /**
     * Get metrics datas for a service
     *
     * @param string $q The host id and service id in format hostId_serviceId
     */
    public function getMetricsDataByService($q = array())
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
        if (false === isset($q['start']) ||
            false === is_numeric($q['start']) ||
            false === isset($q['end']) ||
            false === is_numeric($q['end'])) {
            $this->sendJson("Bad Request", 400);
        }

        $start = $q['start'];
        $end = $q['end'];
        
        /* Get the numbers of points */
        $rows = 200;
        if (isset($q['rows'])) {
            if (false === is_numeric($q['rows'])) {
                $this->sendJson("Bad Request", 400);
            }
            $rows = $q['rows'];
        }
        if ($rows < 10) {
            $this->sendJson("The rows must be greater as 10", 400);
        }
        
        if (false === isset($q['ids'])) {
            $this->sendJson(array());
        }
        
        /* Get the list of service ID */
        $ids = explode(',', $q['ids']);
        $result = array();
        
        foreach ($ids as $id) {
            list($hostId, $serviceId) = explode('_', $id);
            if (false === is_numeric($hostId) ||
                false === is_numeric($serviceId)) {
                $this->sendJson("Bad Request", 400);
            }

            /* Check ACL is not admin */
            if (!$isAdmin) {
                $query = "SELECT service_id
                    FROM centreon_acl
                    WHERE host_id = " . $hostId . "
                        AND service_id = " . $serviceId . "
                        AND group_id IN (" . $aclGroups . ")";
                $res = $pearDBD->query($query);
                if (0 == $res->numRows()) {
                    $this->sendJson("Access denied", 403);
                }
            }

            $data = array();

            /* Prepare graph */
            try {
                /* Get index data */
                $indexData = CentreonGraphService::getIndexId($hostId, $serviceId, $this->pearDBMonitoring);
                $graph = new CentreonGraphService($indexData, session_id());
            } catch (Exception $e) {
                $this->sendJson("Graph not found", 404);
            }
            $graph->setRRDOption("start", $start);
            $graph->setRRDOption("end", $end);
            $graph->initCurveList();
            $graph->createLegend();

            $serviceData = $graph->getData($rows);
            
            
            /* Replace NaN */
            for ($i = 0; $i < count($serviceData); $i++) {
                if (isset($serviceData[$i]['data'])) {
                    $times = array_keys($serviceData[$i]['data']);
                    $values = array_map(array($this, "convertNaN"),
                        array_values($serviceData[$i]['data'])
                    );
                }
                $serviceData[$i]['data'] = $values;
                $serviceData[$i]['label'] = $serviceData[$i]['legend'];
                unset($serviceData[$i]['legend']);
                $serviceData[$i]['type'] = $serviceData[$i]['graph_type'];
                unset($serviceData[$i]['graph_type']);
            }
            $result[] = array(
                'service_id' => $id,
                'data' => $serviceData,
                'times' => $times,
                'size' => $rows
            );
        }
        
        $this->sendJson($result);
    }
    
    /**
     * Send json return
     *
     * @param mixed $values The values
     * @param integer $code The HTTP code
     */
    protected function sendJson($values, $code = 200)
    {
        switch ($code) {
            case 500:
                header("HTTP/1.1 500 Internal Server Error");
                break;
            case 403:
                header("HTTP/1.1 403 Forbidden");
                break;
            case 404:
                header("HTTP/1.1 404 Object not found");
                break;
            case 400:
                header("HTTP/1.1 400 Bad Request");
                break;
        }
        header('Content-type: application/json');
        print json_encode($values);
        exit();
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
}
?>
