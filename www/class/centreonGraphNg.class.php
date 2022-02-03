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

require_once __DIR__ . '/centreonDuration.class.php';
require_once __DIR__ . '/centreonGMT.class.php';
require_once __DIR__ . '/centreonACL.class.php';
require_once __DIR__ . '/centreonHost.class.php';
require_once __DIR__ . '/centreonService.class.php';
require_once __DIR__ . '/centreonSession.class.php';
require_once __DIR__ . '/../include/common/common-Func.php';

/**
 * Singleton Class for topological sorting
 *
 **/
class MetricUtils
{
    private static $instance = null;

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Singleton create method
     *
     * @return MetricUtils
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new MetricUtils();
        }

        return self::$instance;
    }

    /**
     * Process Topological Sort
     *
     * @param int   $pointer
     * @param mixed $dependency
     * @param mixed $order
     * @param mixed $preProcessing
     *
     * @return bool
     */
    private function processTopoSort($pointer, &$dependency, &$order, &$preProcessing)
    {
        if (isset($preProcessing[$pointer])) {
            return false;
        } else {
            $preProcessing[$pointer] = $pointer;
        }

        foreach ($dependency[$pointer] as $i => $v) {
            if (isset($dependency[$v])) {
                if (!$this->processTopoSort($v, $dependency, $order, $preProcessing)) {
                    return false;
                }
            }
            $order[$v] = $v;
            unset($preProcessing[$v]);
        }
        $order[$pointer] = $pointer;
        unset($preProcessing[$pointer]);
        return true;
    }

    /**
     * Function to do a topological sort on a directed acyclic graph
     *
     * @param mixed $data       nodes listing
     * @param mixed $dependency nodes link between them
     *
     * @return mixed nodes listing sorted
     */
    public function topologicalSort($data, $dependency)
    {
        $order = array();
        $preProcessing = array();
        $order = array_diff_key($data, $dependency);
        $data = array_diff_key($data, $order);
        foreach ($data as $i => $v) {
            if (!$this->processTopoSort($i, $dependency, $order, $preProcessing)) {
                return false;
            }
        }
        return $order;
    }
}

class CentreonGraphNg
{
    protected $db;
    protected $dbCs;
    protected $metricUtils;

    protected $rrdOptions;
    protected $arguments;

    protected $debug;
    protected $userId;
    protected $generalOpt;
    protected $dbPath;
    protected $dbStatusPath;
    protected $indexData = [
        'host_id' => null,
        'host_name' => null,
        'service_id' => null,
        'service_description' => null,
    ];
    protected $templateId;
    protected $templateInformations;
    protected $metrics;
    protected $indexIds;

    protected $dsDefault;
    protected $colorCache;
    protected $componentsDsCache;
    protected $extraDatas;
    protected $cacheAllMetrics;
    protected $vnodes;
    protected $vnodesDependencies;
    protected $vmetricsOrder;
    protected $graphData;
    protected $rrdCachedOptions;

    /**
     * Connect to databases
     *
     * @return void
     */
    private function initDatabase()
    {
        global $conf_centreon;

        $mysqlHost = $conf_centreon["hostCentreon"];
        $mysqlDatabase = $conf_centreon["db"];
        $mysqlUser = $conf_centreon["user"];
        $mysqlPassword = $conf_centreon["password"];
        $mysqlPort = $conf_centreon["port"] ? $conf_centreon["port"] : '3306';
        $this->db = new PDO(
            "mysql:dbname=pdo;host=" . $mysqlHost . ";port=" . $mysqlPort . ";dbname=" . $mysqlDatabase,
            $mysqlUser,
            $mysqlPassword,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $mysqlHostCs = $conf_centreon["hostCentstorage"];
        $mysqlDatabaseCs = $conf_centreon["dbcstg"];
        $this->dbCs = new PDO(
            "mysql:dbname=pdo;host=" . $mysqlHostCs . ";port=" . $mysqlPort . ";dbname=" . $mysqlDatabaseCs,
            $mysqlUser,
            $mysqlPassword,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
        $this->dbCs->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Constructor
     *
     * @param int $userId
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->initDatabase();
        $this->metricUtils = MetricUtils::getInstance();

        $this->cacheAllMetrics = array();
        $this->vnodes = array();
        $this->vnodesDependencies = array();
        $this->vmetricsOrder = array();

        $this->arguments = array();
        $this->indexIds = array();
        $this->dsDefault = null;
        $this->colorCache = null;
        $this->userId = $userId;
        $this->componentsDsCache = null;
        $this->listMetricsId = array();
        $this->metrics = array();
        $this->vmetrics = array();
        $this->templateInformations = array();
        $this->extraDatas = array();
        $this->multipleServices = false;

        $stmt = $this->dbCs->prepare("SELECT RRDdatabase_path, RRDdatabase_status_path FROM config");
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->dbPath = $config['RRDdatabase_path'];
        $this->dbStatusPath = $config['RRDdatabase_status_path'];

        $stmt = $this->db->prepare("SELECT `key`, `value` FROM options");
        $stmt->execute();
        $this->generalOpt = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        /* Get RRDCacheD options */
        $stmt = $this->db->query(
            "SELECT config_key, config_value
            FROM cfg_centreonbroker_info AS cbi
            INNER JOIN cfg_centreonbroker AS cb ON (cb.config_id = cbi.config_id)
            INNER JOIN nagios_server AS ns ON (ns.id = cb.ns_nagios_server)
            WHERE ns.localhost = '1'
            AND cbi.config_key IN ('rrd_cached_option', 'rrd_cached')"
        );

        while ($row = $stmt->fetch()) {
            $this->rrdCachedOptions[$row['config_key']] = $row['config_value'];
        }
    }

    /**
     * Set if a graph has multiple services
     *
     * @param int $multiple set multiple value
     *
     * @return void
     */
    public function setMultipleServices($multiple)
    {
        $this->multipleServices = $multiple;
    }

    /**
     * Get graph result
     *
     * @param int $start unix timestamp start date
     * @param int $end   unix timestamp end date
     *
     * @return array graph result
     */
    public function getGraph($start, $end)
    {
        /**
         * For the title and also get the graph template
         * With multiple index_id, we get the last
         * Need to think about it
         */
        $this->getIndexData();
        $this->extraDatas['start'] = $start;
        $this->extraDatas['end'] = $end;
        $this->setRRDOption("start", $start);
        $this->setRRDOption("end", $end);
        $this->setTemplate();
        $this->init();
        $this->initCurveList();
        $this->createLegend();
        return $this->getJsonStream();
    }

    /**
     * Initiate the Graph objects
     *
     * @return void
     */
    public function init()
    {
        $this->setRRDOption("imgformat", "JSONTIME");
        if (isset($this->templateInformations["vertical_label"])) {
            $this->extraDatas['vertical-label'] = $this->templateInformations["vertical_label"];
        }

        $this->setRRDOption("slope-mode");

        if (isset($this->templateInformations["base"]) && $this->templateInformations["base"]) {
            $this->extraDatas['base'] = $this->templateInformations["base"];
        }
        if (isset($this->templateInformations["width"]) && $this->templateInformations["width"]) {
            $this->extraDatas['width'] = $this->templateInformations["width"];
            $this->setRRDOption("width", $this->templateInformations["width"]);
        }
        if (isset($this->templateInformations["height"]) && $this->templateInformations["height"]) {
            $this->extraDatas['height'] = $this->templateInformations["height"];
            $this->setRRDOption("height", $this->templateInformations["height"]);
        }

        if (isset($this->templateInformations["lower_limit"]) && $this->templateInformations["lower_limit"] != null) {
            $this->extraDatas['lower-limit'] = $this->templateInformations["lower_limit"];
            $this->setRRDOption("lower-limit", $this->templateInformations["lower_limit"]);
        }
        if (isset($this->templateInformations["upper_limit"]) && $this->templateInformations["upper_limit"] != "") {
            $this->extraDatas['upper-limit'] = $this->templateInformations["upper_limit"];
            $this->setRRDOption("upper-limit", $this->templateInformations["upper_limit"]);
        } elseif (isset($this->templateInformations[""]) && $this->templateInformations["size_to_max"]) {
            $this->extraDatas['size-to-max'] = $this->templateInformations["size_to_max"];
        }

        $this->extraDatas['scaled'] = 1;
        if (isset($this->templateInformations["scaled"]) && $this->templateInformations["scaled"] == "0") {
            $this->extraDatas['scaled'] = 0;
        }
    }

    /**
     * Get Curve Config
     *
     * @param mixed $metric
     *
     * @return mixed curve config
     */
    protected function getCurveDsConfig($metric)
    {
        $dsData = null;

        if (is_null($this->componentsDsCache)) {
            $stmt = $this->db->prepare("SELECT * FROM giv_components_template ORDER BY host_id DESC");
            $stmt->execute();
            $this->componentsDsCache = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $dsDataAssociated = null;
        $dsDataRegular = null;
        foreach ($this->componentsDsCache as $dsVal) {
            $metricPattern = '/^' . str_replace('/', '\/', $dsVal['ds_name']) . '$/i';
            $metricPattern = str_replace('*', '.*', $metricPattern);

            if (
                isset($metric['host_id']) && isset($metric['service_id']) &&
                ($dsVal['host_id'] == $metric['host_id'] || $dsVal['host_id'] == '') &&
                ($dsVal['service_id'] == $metric['service_id'] || $dsVal['service_id'] == '') &&
                preg_match($metricPattern, $metric['metric_name'])
            ) {
                $dsDataAssociated = $dsVal;
                break;
            }

            if (
                is_null($dsDataRegular)
                && preg_match('/^' . preg_quote($dsVal['ds_name'], '/') . '$/i', $metric['metric_name'])
            ) {
                $dsDataRegular = $dsVal;
            }
        }

        if (!is_null($dsDataAssociated)) {
            $dsData = $dsDataAssociated;
        } elseif (!is_null($dsDataRegular)) {
            $dsData = $dsDataRegular;
        }

        if (is_null($dsData)) {
            if (is_null($this->dsDefault)) {
                $stmt = $this->db->prepare(
                    "SELECT ds_min, ds_max, ds_minmax_int, ds_last, ds_average, ds_total,
                        ds_tickness, ds_color_line_mode, ds_color_line
                     FROM giv_components_template WHERE default_tpl1 = '1'"
                );
                $stmt->execute();
                $this->dsDefault = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            $dsData = $this->dsDefault;
        }

        if ($dsData['ds_color_line_mode'] == '1') {
            $dsData['ds_color_line'] = $this->getOVDColor($metric['index_id'], $metric['metric_id']);
        }

        return $dsData;
    }

    /**
     * Get Legend
     *
     * @param mixed $metric
     *
     * @return string
     */
    private function getLegend($metric)
    {
        $legend = '';
        if (isset($metric['ds_data']['ds_legend']) && strlen($metric['ds_data']['ds_legend']) > 0) {
            $legend = str_replace('"', '\"', $metric['ds_data']['ds_legend']);
        } else {
            if (!isset($metric['ds_data']['ds_name']) || !preg_match('/DS/', $metric['ds_data']['ds_name'], $matches)) {
                $legend = $this->cleanupDsNameForLegend($metric['metric']);
            } else {
                $legend = (isset($metric['ds_data']['ds_name']) ? $metric['ds_data']['ds_name'] : "");
            }
            $legend = str_replace(":", "\:", $legend);
        }

        return $legend;
    }

    /**
     * Manage Virtual Metrics
     *
     * @return void
     */
    private function manageMetrics()
    {
        $this->vmetricsOrder = array();

        if (count($this->vmetrics) == 0) {
            return 0;
        }
        foreach ($this->vmetrics as $vmetricId => &$tm) {
            $this->vnodes[$vmetricId] = $vmetricId;

            $rpns = explode(',', $tm['rpn_function']);
            foreach ($rpns as &$rpn) {
                if (isset($this->cacheAllMetrics['r:' . $rpn])) {
                    $rpn = 'v' . $this->cacheAllMetrics['r:' . $rpn];
                } elseif (isset($this->cacheAllMetrics['v:' . $rpn])) {
                    $vmetricIdChild = $this->cacheAllMetrics['v:' . $rpn];
                    $this->vnodesDependencies[$vmetricId][] = $vmetricIdChild;
                    $rpn = 'vv' . $vmetricIdChild;
                }
            }

            $tm['rpn_function'] = implode(',', $rpns);
        }

        $this->vmetricsOrder = $this->metricUtils->topologicalSort($this->vnodes, $this->vnodesDependencies);
    }

    /**
     * Add a regular metric (not virtual)
     *
     * @param mixed $metric
     * @param int   $hidden
     *
     * @return void
     */
    private function addRealMetric($metric, $hidden = null)
    {
        if (!$this->CheckDBAvailability($metric["metric_id"])) {
            return ;
        }
        if (isset($this->metrics[$metric['metric_id']])) {
            return ;
        }

        $this->log("found metric " . $metric["metric_id"]);

        /**
         * List of id metrics for rrdcached
         */
        $this->listMetricsId[] = $metric['metric_id'];

        $this->metrics[$metric['metric_id']] = array(
            'index_id' => $metric['index_id'],
            'metric_id' => $metric['metric_id'],
            'metric' => $metric['metric_name'],
            'metric_legend' => $this->cleanupDsNameForLegend($metric['metric_name']),
            'unit' => $metric['unit_name'],
            'hidden' => 0,
            'min' => $metric['min'],
            'max' => $metric['max'],
            'virtual' => 0,
        );

        $this->cacheAllMetrics['r:' . $metric["metric_name"]] = $metric["metric_id"];

        $dsData = $this->getCurveDsConfig($metric);
        $this->metrics[$metric['metric_id']]['ds_data'] = $dsData;

        $this->metrics[$metric['metric_id']]['legend'] = $this->getLegend($this->metrics[$metric["metric_id"]]);

        $this->metrics[$metric['metric_id']]["stack"] =
            (isset($dsData["ds_stack"]) && $dsData["ds_stack"] ? $dsData["ds_stack"] : 0);

        $this->metrics[$metric["metric_id"]]["warn"] = $metric["warn"];
        $this->metrics[$metric["metric_id"]]["warn_low"] = $metric["warn_low"];
        $this->metrics[$metric["metric_id"]]["crit"] = $metric["crit"];
        $this->metrics[$metric["metric_id"]]["crit_low"] = $metric["crit_low"];
        if (!isset($dsData["ds_color_area_warn"]) || empty($dsData["ds_color_area_warn"])) {
            $this->metrics[$metric["metric_id"]]["ds_color_area_warn"] = $this->generalOpt["color_warning"]['value'];
        }
        if (!isset($dsData["ds_color_area_crit"]) || empty($dsData["ds_color_area_crit"])) {
            $this->metrics[$metric["metric_id"]]["ds_color_area_crit"] = $this->generalOpt["color_critical"]['value'];
        }

        $this->metrics[$metric["metric_id"]]["ds_order"] =
            (isset($dsData["ds_order"]) && $dsData["ds_order"] ? $dsData["ds_order"] : 0);

        $this->metrics[$metric['metric_id']]['hidden'] = is_null($hidden) ? 0 : $hidden;

        if (isset($dsData['ds_invert']) && $dsData['ds_invert']) {
            if (
                !is_null($this->metrics[$metric['metric_id']]['min'])
                && is_numeric($this->metrics[$metric['metric_id']]['min'])
            ) {
                $this->metrics[$metric['metric_id']]['min'] = $metric['min'] * -1;
            }
            if (
                !is_null($this->metrics[$metric['metric_id']]['max'])
                && is_numeric($this->metrics[$metric['metric_id']]['max'])
            ) {
                $this->metrics[$metric['metric_id']]['max'] = $metric['max'] * -1;
            }
        }
    }

    /**
     * Add a virtual metric
     *
     * @param mixed $vmetric
     * @param int   $hidden
     *
     * @return void
     */
    private function addVirtualMetric($vmetric, $hidden = null)
    {
        if (isset($this->vmetrics[$vmetric['vmetric_id']])) {
            return ;
        }

        $this->log("found vmetric " . $vmetric["vmetric_id"]);
        $this->vmetrics[$vmetric['vmetric_id']] = array(
            'index_id' => $vmetric['index_id'],
            'vmetric_id' => $vmetric['vmetric_id'],
            'metric' => $vmetric['vmetric_name'],
            'metric_legend' => $vmetric['vmetric_name'],
            'unit' => $vmetric['unit_name'],
            'hidden' => isset($vmetric['hidden']) && $vmetric['hidden'] == 1 ? 1 : 0,
            'warn' => $vmetric['warn'],
            'crit' => $vmetric['crit'],
            'def_type' => $vmetric['def_type'] == 1 ? 'VDEF' : 'CDEF',
            'rpn_function' => $vmetric['rpn_function'],
            'virtual' => 1,
        );

        if (!is_null($hidden)) {
            $this->vmetrics[$vmetric['vmetric_id']]['hidden'] = $hidden;
        }

        $this->cacheAllMetrics['v:' . $vmetric['vmetric_name']] = $vmetric['vmetric_id'];

        if ($this->vmetrics[$vmetric['vmetric_id']]['hidden'] == 0) {
            # Not cleaning. Should have its own metric_id for ods_view_details
            $vmetric['metric_name'] = $vmetric['vmetric_name'];
            $vmetric['metric_id'] = $vmetric['vmetric_id'];
            $dsData = $this->getCurveDsConfig($vmetric);
            $this->vmetrics[$vmetric['vmetric_id']]['ds_data'] = $dsData;

            $this->vmetrics[$vmetric['vmetric_id']]['legend'] =
                $this->getLegend($this->vmetrics[$vmetric["vmetric_id"]]);
            $this->vmetrics[$vmetric['vmetric_id']]['ds_order'] =
                (isset($dsData["ds_order"]) && $dsData["ds_order"] ? $dsData["ds_order"] : 0);
        }
    }

    /**
     * Add metrics for a service
     *
     * @param int $hostId
     * @param int $serviceId
     *
     * @return void
     */
    public function addServiceMetrics($hostId, $serviceId)
    {
        $indexId = null;
        $stmt = $this->dbCs->prepare(
            "SELECT
                m.index_id, host_id, service_id, metric_id, metric_name,
                unit_name, min, max, warn, warn_low, crit, crit_low
            FROM metrics AS m, index_data AS i
            WHERE i.host_id = :host_id
                AND i.service_id = :service_id
                AND i.id = m.index_id
                AND m.hidden = '0'"
        );
        $stmt->bindParam(':host_id', $hostId, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($metrics as $metric) {
            $indexId = $metric['index_id'];
            $this->addIndexId($metric['index_id']);
            $this->addRealMetric($metric);
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM virtual_metrics
             WHERE index_id = :index_id
             AND vmetric_activate = '1'"
        );
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->execute();
        $vmetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vmetrics as $vmetric) {
            $this->addVirtualMetric($vmetric);
        }
    }

    /**
     * Add metrics for a service
     *
     * @param int   $hostId
     * @param int   $serviceId
     * @param mixed $metricsSelected
     *
     * @return void
     */
    public function addServiceCustomMetrics($hostId, $serviceId, $metricsSelected): void
    {
        $indexId = null;
        $stmt = $this->dbCs->prepare(
            "SELECT
                m.index_id, host_id, service_id, metric_id, metric_name,
                unit_name, min, max, warn, warn_low, crit, crit_low
            FROM metrics AS m, index_data AS i
            WHERE i.host_id = :host_id
                AND i.service_id = :service_id
                AND i.id = m.index_id
                AND m.hidden = '0'"
        );
        $stmt->bindParam(':host_id', $hostId, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($metrics as $metric) {
            $indexId = $metric['index_id'];
            $this->addIndexId($metric['index_id']);
            if (isset($metricsSelected[$metric['metric_id']])) {
                $this->addRealMetric($metric);
            } else {
                // this metric will be hidden
                $this->addRealMetric($metric, 1);
            }
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM virtual_metrics
             WHERE index_id = :index_id
             AND vmetric_activate = '1'"
        );
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->execute();
        $vmetrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vmetrics as $vmetric) {
            $this->addVirtualMetric($vmetric);
        }
    }

    /**
     * Add a metric
     *
     * @param int $metricId
     * @param int $isVirtual
     *
     * @return void
     */
    public function addMetric($metricId, $isVirtual = 0)
    {
        if ($isVirtual == 0) {
            $stmt = $this->dbCs->prepare(
                "SELECT m.index_id, host_id, service_id, metric_id, metric_name,
                unit_name, min, max, warn, warn_low, crit, crit_low
                FROM metrics AS m, index_data AS i
                WHERE m.metric_id = :metric_id
                AND m.hidden = '0'
                AND m.index_id = i.id"
            );
            $stmt->bindParam(':metric_id', $metricId, PDO::PARAM_INT);
            $stmt->execute();
            $metric = $stmt->fetch(PDO::FETCH_ASSOC);
            if (is_null($metric)) {
                return;
            }

            $this->addIndexId($metric['index_id']);
            $this->addRealMetric($metric);

            return ;
        }

        $stmt = $this->db->prepare(
            "SELECT *
             FROM virtual_metrics
             WHERE vmetric_id = :vmetric_id
                AND vmetric_activate = '1'"
        );
        $stmt->bindParam(':vmetric_id', $metricId, PDO::PARAM_INT);
        $stmt->execute();
        $vmetric = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_null($vmetric)) {
            return;
        }

        $this->addIndexId($vmetric['index_id']);
        $this->addVirtualMetric($vmetric);

        /**
         * Brutal: we get all vmetrics and metrics, with hidden
         */
        $metrics = $this->getRealMetricsByIndexId($vmetric['index_id']);
        foreach ($metrics as $metric) {
            $this->addIndexId($metric['index_id']);
            $this->addRealMetric($metric, 1);
        }

        $vmetrics = $this->getVirtualMetricsByIndexId($vmetric['index_id']);
        foreach ($vmetrics as $vmetric) {
            $this->addVirtualMetric($vmetric);
        }
    }

    /**
     * Initiate rrdtool curve arguments
     *
     * @return void
     */
    private function initCurveList()
    {
        uasort($this->metrics, array("CentreonGraphNg", "cmpmultiple"));

        foreach ($this->metrics as $metricId => &$tm) {
            if (isset($tm['ds_data']['ds_invert']) && $tm['ds_data']['ds_invert']) {
                $this->addArgument("DEF:vi" . $metricId . "=" . $this->dbPath . $metricId . ".rrd:value:AVERAGE CDEF:v"
                    . $metricId . "=vi" . $metricId . ",-1,*");
            } else {
                $this->addArgument("DEF:v" . $metricId . "=" . $this->dbPath . $metricId . ".rrd:value:AVERAGE");
            }
        }

        $this->manageMetrics();

        foreach ($this->vmetricsOrder as $vmetricId) {
            $this->addArgument($this->vmetrics[$vmetricId]['def_type'] . ":vv" . $vmetricId . "="
                . $this->vmetrics[$vmetricId]['rpn_function']);
        }
    }

    /**
     * Switch graph limits
     *
     * @param int $lower
     * @param int $upper
     *
     * @return void
     */
    private function switchRRDLimitOption($lower = null, $upper = null)
    {
        if (is_null($lower)) {
            unset($this->rrdOptions["upper-limit"]);
            unset($this->extraDatas['upper-limit']);
        } else {
            $this->rrdOptions["upper-limit"] = $lower;
            $this->extraDatas['upper-limit'] = $lower;
        }
        if (is_null($upper)) {
            unset($this->rrdOptions["lower-limit"]);
            unset($this->extraDatas['lower-limit']);
        } else {
            $this->rrdOptions["lower-limit"] = $upper;
            $this->extraDatas['lower-limit'] = $upper;
        }
    }

    /**
     * Clean up ds name in Legend
     *
     * @param string $dsname
     *
     * @return string
     */
    protected function cleanupDsNameForLegend($dsname)
    {
        $newDsName = str_replace(array("'", "\\"), array(" ", "\\\\"), $dsname);
        return $newDsName;
    }

    /**
     * Add rrdtool legends
     *
     * @param mixed $metric
     * @param int   $metricId
     * @param int   $isVirtual
     *
     * @return void
     */
    private function legendAddPrint($metric, $metricId, $isVirtual = 0)
    {
        $vdefs = "";
        $prints = "";
        $prefix = 'v';
        if ($isVirtual == 1) {
            $prefix = 'vv';
        }

        foreach (
            array("last" => "LAST", "min" => "MINIMUM", "max" => "MAXIMUM",
                       "average" => "AVERAGE", "total" => "TOTAL") as $name => $cf
        ) {
            if (!$metric['ds_data']['ds_' . $name]) {
                continue;
            }

            $dispname = ucfirst($name);
            if (isset($metric['ds_data']['ds_invert']) && $metric['ds_data']['ds_invert']) {
                $vdefs .= "VDEF:" . $prefix . "i" . $metricId . $dispname . "="
                . $prefix . 'i' . $metricId . "," . $cf . " ";
            } else {
                $vdefs .= "VDEF:" . $prefix . $metricId . $dispname . "="
                    . $prefix . $metricId . "," . $cf . " ";
            }
            if (
                ($name == "min" || $name == "max") &&
                (isset($metric['ds_data']['ds_minmax_int']) && $metric['ds_data']['ds_minmax_int'])
            ) {
                $displayformat = "%.0lf";
            } else {
                $displayformat = "%.2lf";
            }
            if (isset($metric['ds_data']['ds_invert']) && $metric['ds_data']['ds_invert']) {
                $prints .= "GPRINT:" . $prefix . "i" . $metricId . $dispname . ":\""
                    . $dispname . "\:" . $displayformat . "\" ";
            } else {
                $prints .= "GPRINT:" . $prefix . $metricId  . $dispname . ":\""
                    . $dispname . "\:" . $displayformat . "\" ";
            }
        }

        $this->addArgument($vdefs);
        $this->addArgument($prints);
    }

    /**
     * Create Legend on the graph
     *
     * @return void
     */
    public function createLegend()
    {
        foreach ($this->metrics as $metricId => $tm) {
            if ($tm['hidden'] == 1) {
                continue;
            }
            $arg = "LINE1:v" . $metricId . "#0000ff:v" . $metricId;
            $this->addArgument($arg);
            $this->legendAddPrint($tm, $metricId);
        }

        foreach ($this->vmetricsOrder as $vmetricId) {
            if ($this->vmetrics[$vmetricId]['hidden'] == 1) {
                continue;
            }

            $arg = "LINE1:vv" . $vmetricId . "#0000ff:vv" . $vmetricId;
            $this->addArgument($arg);
            $this->legendAddPrint($this->vmetrics[$vmetricId], $vmetricId, 1);
        }
    }

    /**
     * Get template configuration
     *
     * @return void
     */
    private function getDefaultGraphTemplate()
    {
        $templateId = $this->getServiceGraphID();
        if (!is_null($templateId) && $templateId != "") {
            $this->templateId = $templateId;
            return ;
        } else {
            $commandId = getMyServiceField($this->indexData["service_id"], "command_command_id");
            $stmt = $this->db->prepare("SELECT graph_id FROM command WHERE `command_id` = :command_id");
            $stmt->bindParam(':command_id', $commandId, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row !== false && $row["graph_id"] != 0) {
                $this->templateId = $row["graph_id"];
                return ;
            }
        }

        $stmt = $this->db->prepare("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1'");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->templateId = $row["graph_id"];
    }

    /**
     * Get graph ID for the service
     *
     * @return void
     */
    private function getServiceGraphID()
    {
        $serviceId = $this->indexData["service_id"];

        $stmt = $this->db->prepare("SELECT
                esi.graph_id, service_template_model_stm_id FROM service
            LEFT JOIN extended_service_information esi
                ON esi.service_service_id = service_id
                WHERE service_id = :service_id");
        $tab = array();
        while (1) {
            $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row["graph_id"]) {
                    return $row["graph_id"];
                } elseif ($row["service_template_model_stm_id"]) {
                    if (isset($tab[$row['service_template_model_stm_id']])) {
                        break;
                    }
                    $serviceId = $row["service_template_model_stm_id"];
                    $tab[$serviceId] = 1;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return null;
    }

    /**
     * Get index data
     *
     * @return void
     */
    private function getIndexData()
    {
        if ($this->multipleServices) {
            return ;
        }
        /**
         * We take the first
         */
        $keys = array_keys($this->indexIds);
        $indexId = array_shift($keys);

        $this->log("index_data for " . $indexId);
        $stmt = $this->dbCs->prepare(
            "SELECT host_id, host_name, service_id, service_description FROM index_data WHERE id = :index_id"
        );
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->execute();
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->indexData = $row;
        }

        if (preg_match("/meta_([0-9]*)/", $this->indexData["service_description"], $matches)) {
            $stmt = $this->db->prepare("SELECT meta_name FROM meta_service WHERE `meta_id` = :meta_id");
            $stmt->bindParam(':meta_id', $matches[1], PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->indexData["service_description"] = $row["meta_name"];
        }

        if ($this->indexData["host_name"] != "_Module_Meta") {
            $this->extraDatas['title'] = $this->indexData['service_description'] . " " . _("graph on") . " "
                . $this->indexData['host_name'];
        } else {
            $this->extraDatas['title'] = _("Graph") . " " . $this->indexData["service_description"];
        }
    }

    /**
     * Assign graph template
     *
     * @param int $templateId
     *
     * @return void
     */
    public function setTemplate($templateId = null)
    {
        if ($this->multipleServices) {
            return ;
        }

        if (!isset($templateId) || !$templateId) {
            if ($this->indexData["host_name"] != "_Module_Meta") {
                $this->getDefaultGraphTemplate();
            } else {
                $stmt = $this->db->prepare("SELECT graph_id FROM meta_service WHERE `meta_name` = :meta_name");
                $stmt->bindParam(':meta_name', $this->indexData["service_description"], PDO::PARAM_STR);
                $stmt->execute();
                $meta = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->templateId = $meta["graph_id"];
            }
        } else {
            $this->templateId = htmlentities($_GET["template_id"], ENT_QUOTES, "UTF-8");
        }

        $stmt = $this->db->prepare("SELECT * FROM giv_graphs_template WHERE graph_id = :graph_id");
        $stmt->bindParam(':graph_id', $this->templateId, PDO::PARAM_INT);
        $stmt->execute();
        $this->templateInformations = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Add argument rrdtool
     *
     * @param string $arg
     *
     * @return void
     */
    public function addArgument($arg)
    {
        $this->arguments[] = $arg;
    }

    /**
     * Add argument rrdtool
     *
     * @param string $name the key
     * @param string $value
     *
     * @return void
     */
    public function setRRDOption($name, $value = null)
    {
        if (strpos($value, " ") !== false) {
            $value = "'" . $value . "'";
        }
        $this->rrdOptions[$name] = $value;
    }

    /**
     * Parse rrdtool result
     *
     * @param array $rrdData
     *
     * @return void
     */
    private function formatByMetrics($rrdData)
    {
        $this->graphData['times'] = [];

        $size = isset($rrdData['data']) && (is_array($rrdData['data']) || $rrdData['data'] instanceof \Countable)
            ? count($rrdData['data'])
            : 0;

        $gprintsSize = isset($rrdData['meta']['gprints'])
            && (is_array($rrdData['meta']['gprints']) || $rrdData['meta']['gprints'] instanceof \Countable)
            ? count($rrdData['meta']['gprints'])
            : 0;

        for ($i = 0; $i < $size; $i++) {
            $this->graphData['times'][] = $rrdData['data'][$i][0];
        }

        $metricIndex = 1;
        $gprintsPos = 0;
        foreach ($this->graphData['metrics'] as &$metric) {
            $metric['data'] = array();
            $metric['prints'] = array();

            $insert = 0;
            if ($metric['virtual'] == 0) {
                $metricFullname = 'v' . $metric['metric_id'];
            } else {
                $metricFullname = 'vv' . $metric['vmetric_id'];
            }

            $metric['last_value'] = null;
            $metric['minimum_value'] = null;
            $metric['maximum_value'] = null;
            $metric['average_value'] = null;
            for (; $gprintsPos < $gprintsSize; $gprintsPos++) {
                if (isset($rrdData['meta']['gprints'][$gprintsPos]['line'])) {
                    if ($rrdData['meta']['gprints'][$gprintsPos]['line'] == $metricFullname) {
                        $insert = 1;
                    } else {
                        break;
                    }
                } elseif ($insert == 1) {
                    $metric['prints'][] = array_values($rrdData['meta']['gprints'][$gprintsPos]);
                    foreach (array_values($rrdData['meta']['gprints'][$gprintsPos]) as $gprintValue) {
                        if (preg_match('/^(.+):((?:\d|\.)+)$/', $gprintValue, $matches)) {
                            switch ($matches[1]) {
                                case 'Last':
                                    $metric['last_value'] = (float) $matches[2];
                                    break;
                                case 'Min':
                                    $metric['minimum_value'] = (float) $matches[2];
                                    break;
                                case 'Max':
                                    $metric['maximum_value'] = (float) $matches[2];
                                    break;
                                case 'Average':
                                    $metric['average_value'] = (float) $matches[2];
                                    break;
                            }
                        }
                    }
                }
            }

            for ($dataIndex = 0; $dataIndex < $size; $dataIndex++) {
                $metric['data'][] = $rrdData['data'][$dataIndex][$metricIndex];
            }

            $metricIndex++;
        }
    }

    /**
     * Get rrdtool result
     *
     * @return mixed
     */
    public function getJsonStream()
    {
        $commandLine = "";

        $this->flushRrdcached($this->listMetricsId);

        $commandLine = " graph - ";

        foreach ($this->rrdOptions as $key => $value) {
            $commandLine .= "--" . $key;
            if (isset($value)) {
                if (preg_match('/\'/', $value)) {
                    $value = "'" . preg_replace('/\'/', ' ', $value) . "'";
                }
                $commandLine .= "=" . $value;
            }
            $commandLine .= " ";
        }

        foreach ($this->arguments as $arg) {
            $commandLine .= " " . $arg . " ";
        }
        $commandLine = preg_replace("/(\\\$|`)/", "", $commandLine);
        $this->log($commandLine);

        if (is_writable($this->generalOpt['debug_path']['value'])) {
            $stderr = array('file', $this->generalOpt['debug_path']['value'] . '/rrdtool.log', 'a');
        } else {
            $stderr = array('pipe', 'a');
        }
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"),
            2 => $stderr
        );

        $process = proc_open(
            $this->generalOpt['rrdtool_path_bin']['value'] . " - ",
            $descriptorspec,
            $pipes,
            null,
            null
        );
        $this->extraDatas['multiple_services'] = $this->multipleServices;
        $this->graphData = array(
            'global' => $this->extraDatas,
            'metrics' => []
        );
        foreach ($this->metrics as $metric) {
            if ($metric['hidden'] == 1) {
                continue;
            }
            $this->graphData['metrics'][] = $metric;
        }
        foreach ($this->vmetricsOrder as $vmetricId) {
            if ($this->vmetrics[$vmetricId]['hidden'] == 1) {
                continue;
            }

            $this->graphData['metrics'][] = $this->vmetrics[$vmetricId];
        }

        if (is_resource($process)) {
            fwrite($pipes[0], $commandLine);
            fclose($pipes[0]);

            $str = stream_get_contents($pipes[1]);
            $returnValue = proc_close($process);

            $str = preg_replace("/OK u:.*$/", "", $str);
            $rrdData = json_decode($str, true);
        }

        $this->formatByMetrics($rrdData);
        return $this->graphData;
    }

    /**
     * Check argument
     *
     * @param string $name
     * @param mixed  $tab
     * @param string $defaultValue
     *
     * @return string
     */
    public function checkArgument($name, $tab, $defaultValue)
    {
        if (isset($name) && isset($tab)) {
            if (isset($tab[$name])) {
                return htmlentities($tab[$name], ENT_QUOTES, "UTF-8");
            } else {
                return htmlentities($defaultValue, ENT_QUOTES, "UTF-8");
            }
        }
    }

    /**
     * Get curve color
     *
     * @param int $indexId
     * @param int $metricId
     *
     * @return string
     */
    public function getOVDColor($indexId, $metricId)
    {
        if (is_null($this->colorCache)) {
            $this->colorCache = [];
        }
        if (!isset($this->colorCache[$indexId])) {
            $stmt = $this->db->prepare(
                "SELECT metric_id, rnd_color FROM `ods_view_details` WHERE `index_id` = :index_id"
            );
            $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
            $stmt->execute();
            $this->colorCache[$indexId] = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        }

        if (
            isset($this->colorCache[$indexId][$metricId])
            && preg_match("/^\#[a-f0-9]{6,6}/i", $this->colorCache[$indexId][$metricId]['rnd_color'])
        ) {
            return $this->colorCache[$indexId][$metricId]['rnd_color'];
        }
        $lRndcolor = $this->getRandomWebColor();
        $stmt = $this->db->prepare(
            "INSERT INTO `ods_view_details` (rnd_color, index_id, metric_id) VALUES (:rnd_color, :index_id, :metric_id)"
        );
        $stmt->bindParam(':rnd_color', $lRndcolor, PDO::PARAM_STR);
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->bindParam(':metric_id', $metricId, PDO::PARAM_INT);
        $stmt->execute();
        return $lRndcolor;
    }

    /**
     * Get random color in a predefined list
     *
     * @return string
     */
    public function getRandomWebColor()
    {
        $webSafeColors = array('#000033', '#000066', '#000099', '#0000cc',
            '#0000ff', '#003300', '#003333', '#003366', '#003399', '#0033cc',
            '#0033ff', '#006600', '#006633', '#006666', '#006699', '#0066cc',
            '#0066ff', '#009900', '#009933', '#009966', '#009999', '#0099cc',
            '#0099ff', '#00cc00', '#00cc33', '#00cc66', '#00cc99', '#00cccc',
            '#00ccff', '#00ff00', '#00ff33', '#00ff66', '#00ff99', '#00ffcc',
            '#00ffff', '#330000', '#330033', '#330066', '#330099', '#3300cc',
            '#3300ff', '#333300', '#333333', '#333366', '#333399', '#3333cc',
            '#3333ff', '#336600', '#336633', '#336666', '#336699', '#3366cc',
            '#3366ff', '#339900', '#339933', '#339966', '#339999', '#3399cc',
            '#3399ff', '#33cc00', '#33cc33', '#33cc66', '#33cc99', '#33cccc',
            '#33ccff', '#33ff00', '#33ff33', '#33ff66', '#33ff99', '#33ffcc',
            '#33ffff', '#660000', '#660033', '#660066', '#660099', '#6600cc',
            '#6600ff', '#663300', '#663333', '#663366', '#663399', '#6633cc',
            '#6633ff', '#666600', '#666633', '#666666', '#666699', '#6666cc',
            '#6666ff', '#669900', '#669933', '#669966', '#669999', '#6699cc',
            '#6699ff', '#66cc00', '#66cc33', '#66cc66', '#66cc99', '#66cccc',
            '#66ccff', '#66ff00', '#66ff33', '#66ff66', '#66ff99', '#66ffcc',
            '#66ffff', '#990000', '#990033', '#990066', '#990099', '#9900cc',
            '#9900ff', '#993300', '#993333', '#993366', '#993399', '#9933cc',
            '#9933ff', '#996600', '#996633', '#996666', '#996699', '#9966cc',
            '#9966ff', '#999900', '#999933', '#999966', '#999999', '#9999cc',
            '#9999ff', '#99cc00', '#99cc33', '#99cc66', '#99cc99', '#99cccc',
            '#99ccff', '#99ff00', '#99ff33', '#99ff66', '#99ff99', '#99ffcc',
            '#99ffff', '#cc0000', '#cc0033', '#cc0066', '#cc0099', '#cc00cc',
            '#cc00ff', '#cc3300', '#cc3333', '#cc3366', '#cc3399', '#cc33cc',
            '#cc33ff', '#cc6600', '#cc6633', '#cc6666', '#cc6699', '#cc66cc',
            '#cc66ff', '#cc9900', '#cc9933', '#cc9966', '#cc9999', '#cc99cc',
            '#cc99ff', '#cccc00', '#cccc33', '#cccc66', '#cccc99', '#cccccc',
            '#ccccff', '#ccff00', '#ccff33', '#ccff66', '#ccff99', '#ccffcc',
            '#ccffff', '#ff0000', '#ff0033', '#ff0066', '#ff0099', '#ff00cc',
            '#ff00ff', '#ff3300', '#ff3333', '#ff3366', '#ff3399', '#ff33cc',
            '#ff33ff', '#ff6600', '#ff6633', '#ff6666', '#ff6699', '#ff66cc',
            '#ff66ff', '#ff9900', '#ff9933', '#ff9966', '#ff9999', '#ff99cc',
            '#ff99ff', '#ffcc00', '#ffcc33', '#ffcc66', '#ffcc99', '#ffcccc',
            '#ffccff'
        );
        return $webSafeColors[rand(0, sizeof($webSafeColors) - 1)];
    }

    /**
     * Order method
     *
     * @param mixed $a
     * @param mixed $b
     *
     * @return bool
     */
    private function cmpmultiple($a, $b)
    {
        if (isset($a["ds_order"]) && isset($b["ds_order"])) {
            if ($a["ds_order"] < $b["ds_order"]) {
                return -1;
            } elseif ($a["ds_order"] > $b["ds_order"]) {
                return 1;
            }
        }
        return strnatcasecmp(
            (isset($a["legend"]) && $a["legend"]) ? $a["legend"] : null,
            (isset($b["legend"]) && $b["legend"]) ? $b["legend"] : null
        );
    }

    /**
     * Log message
     *
     * @param string $message
     *
     * @return void
     */
    private function log($message)
    {
        if (
            $this->generalOpt['debug_rrdtool']['value'] &&
            is_writable($this->generalOpt['debug_path']['value'])
        ) {
            error_log(
                "[" . date("d/m/Y H:i") . "] RDDTOOL : " . $message . " \n",
                3,
                $this->generalOpt['debug_path']['value'] . "rrdtool.log"
            );
        }
    }

    /**
     * Check rrd file is present
     *
     * @param int $metricId
     *
     * @return bool
     */
    private function checkDBAvailability($metricId)
    {
        if (!file_exists($this->dbPath . $metricId . ".rrd") && !preg_match("/^v/", $metricId)) {
            return 0;
        }
        return 1;
    }

    /**
     * Flush metrics in rrdcached
     *
     * @param mixed $metricsId The list of metrics
     *
     * @return bool
     */
    protected function flushRrdcached($metricsId)
    {
        if (
            !isset($this->rrdCachedOptions['rrd_cached_option'])
            || !in_array($this->rrdCachedOptions['rrd_cached_option'], ['unix', 'tcp'])
        ) {
            return true;
        }

        $errno = 0;
        $errstr = '';
        if ($this->rrdCachedOptions['rrd_cached_option'] === 'tcp') {
            $sock = fsockopen('127.0.0.1', trim($this->rrdCachedOptions['rrd_cached']), $errno, $errstr);
        } elseif ($this->rrdCachedOptions['rrd_cached_option'] === 'unix') {
            $sock = fsockopen('unix://' . trim($this->rrdCachedOptions['rrd_cached']), $errno, $errstr);
        } else {
            return false;
        }

        if (false === $sock) {
            $this->log("socket connection: " . $errstr);
            return false;
        }

        if (false === fputs($sock, "BATCH\n")) {
            fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            fclose($sock);
            return false;
        }

        foreach ($metricsId as $metricId) {
            $fullpath = realpath($this->dbPath . $metricId . '.rrd');
            $cmd = 'FLUSH ' . $fullpath;
            if (false === fputs($sock, $cmd . "\n")) {
                fclose($sock);
                return false;
            }
        }

        if (false === fputs($sock, ".\n")) {
            fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            fclose($sock);
            return false;
        }

        fputs($sock, "QUIT\n");
        fclose($sock);
        return true;
    }

    /**
     * Returns index data id
     *
     * @param int $hostId
     * @param int $serviceId
     *
     * @return int
     */
    public function getIndexDataId($hostId, $serviceId)
    {
        $stmt = $this->dbCs->prepare(
            "SELECT id FROM index_data WHERE host_id = :host_id AND service_id = :service_id"
        );
        $stmt->bindParam(':host_id', $hostId, PDO::PARAM_INT);
        $stmt->bindParam(':service_id', $serviceId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_null($row) ? 0 : $row['id'];
    }

    /**
     * Returns true if status graph exists
     *
     * @param int $hostId
     * @param int $serviceId
     *
     * @return bool
     */
    public function statusGraphExists($hostId, $serviceId)
    {
        $id = $this->getIndexDataId($hostId, $serviceId);
        if (is_file($this->dbStatusPath . "/" . $id . ".rrd")) {
            return true;
        }
        return false;
    }

    /**
     * Add Index ID
     *
     * @param int $indexId
     *
     * @return void
     */
    private function addIndexId($indexId)
    {
        if (!isset($this->indexIds[$indexId])) {
            $this->indexIds[$indexId] = 1;
        }
    }

    /**
     * Get regular metrics
     *
     * @param int $indexId
     *
     * @return mixed
     */
    private function getRealMetricsByIndexId($indexId)
    {
        $stmt = $this->dbCs->prepare(
            "SELECT m.index_id, host_id, service_id, metric_id, metric_name,
                unit_name, min, max, warn, warn_low, crit, crit_low
             FROM metrics AS m, index_data AS i
             WHERE i.id = :index_id
                AND i.id = m.index_id
                AND m.hidden = '0'"
        );
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get virtual metrics
     *
     * @param int $indexId
     *
     * @return mixed
     */
    private function getVirtualMetricsByIndexId($indexId)
    {
        $stmt = $this->db->prepare(
            "SELECT *
             FROM virtual_metrics
             WHERE index_id = :index_id
                AND vmetric_activate = '1'"
        );
        $stmt->bindParam(':index_id', $indexId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
