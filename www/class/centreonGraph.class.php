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

/*
 * this class need also others classes
 */
require_once _CENTREON_PATH_ . "www/class/centreonDuration.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonGMT.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonHost.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonService.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/include/common/common-Func.php";

/*
 * Class for XML/Ajax request
 *
 */
class CentreonGraph
{
    /**
     * Percentage over Max limit
     *
     */
    const OVER_MAX_LIMIT_PCT = 3;

    /*
     * Engine infinite values
     */
    const ENGINE_HIGH_INFINITE = 340282346638528860000000000000000000000;
    const ENGINE_LOW_INFINITE = -340282346638528860000000000000000000000;

    /*
     * Objects
     */
    protected $DB;
    protected $DBC;

    public $XML;
    public $GMT;

    protected $hostObj;
    protected $serviceObj;

    /*
     * private vars
     */
    protected $RRDoptions;
    protected $arguments;
    protected $argcount;
    protected $options;
    protected $colors;
    protected $fonts;
    protected $flag;
    protected $maxLimit;

    /*
     * Variables
     */
    protected $debug;
    protected $compress;
    public $user_id;
    protected $generalOpt;
    protected $filename;
    protected $commandLine;
    protected $dbPath;
    protected $dbStatusPath;
    protected $index;
    protected $indexData;
    protected $templateId;
    protected $templateInformations;
    protected $gprintScaleOption;
    protected $graphID;
    protected $metricsEnabled;
    protected $rmetrics;
    protected $vmetrics;
    protected $mpointer;
    protected $mlist;
    protected $vname;
    protected $metrics;
    protected $longer;
    public $onecurve;
    public $checkcurve;

    /*
     * Class constructor
     *
     * <code>
     * $obj = new CentreonBGRequest($_GET["session_id"], 1, 1, 0, 1);
     * </code>
     *
     * $user_id     char    The user id
     * $dbneeds     bool    flag for enable ndo connexion
     * $headType    bool    send XML header
     * $debug       bool    debug flag.
     */
    public function __construct($user_id, $index = null, $debug = 0, $compress = null)
    {
        if (!isset($debug)) {
            $this->debug = 0;
        }

        (!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;
        
        /*
         * User ID / Contact ID
         */
        $this->user_id = $user_id;

        $this->index = htmlentities($index, ENT_QUOTES, "UTF-8");

        /*
         * Enable Database Connexions
         */
        $this->DB       = new CentreonDB();
        $this->DBC      = new CentreonDB("centstorage");

        /*
         * Init Objects
         */
        $this->hostObj      = new CentreonHost($this->DB);
        $this->serviceObj   = new CentreonService($this->DB);

        /*
         * Timezone management
         */
        $this->GMT = new CentreonGMT($this->DB);
        $this->GMT->getMyGTMFromUser($this->user_id, $this->DB);

        $this->RRDoptions = array();
        $this->arguments = array();
        $this->options = array();
        $this->colors = array();
        $this->fonts = array();
        $this->argcount = 0;
        $this->flag = 0;

        /*
         * Set default parameters
         */
        $this->setRRDOption("width", 500);
        $this->setRRDOption("height", 120);

        $this->getIndexData();

        $this->filename = $this->indexData["host_name"]. "-".$this->indexData["service_description"];
        $this->filename = str_replace(array("/", "\\"), array("-", "-"), $this->filename);

        $this->templateInformations = array();
        $this->metricsEnabled = array();
        $this->rmetrics = array();
        $this->vmetrics = array();
        $this->mpointer = array(0,0);
        $this->mlist = array();
        $this->vname = array();
        $this->metrics = array();
        $this->onecurve = false;
        $this->checkcurve = false;

        $DBRESULT = $this->DBC->query("SELECT RRDdatabase_path, RRDdatabase_status_path FROM config LIMIT 1");
        $config = $DBRESULT->fetchRow();
        $this->dbPath = $config["RRDdatabase_path"];
        $this->dbStatusPath = $config['RRDdatabase_status_path'];
        unset($config);
        $DBRESULT->free();

        $DBRESULT = $this->DB->query("SELECT * FROM options");
        while ($opt = $DBRESULT->fetchRow()) {
            $this->generalOpt[$opt['key']] = $opt['value'];
        }
        $DBRESULT->free();
        unset($opt);

        if (isset($index)) {
            $DBRESULT = $this->DB->query("SELECT `metric_id`
                                          FROM `ods_view_details`
                                          WHERE `index_id` = '" . $this->index . "'
                                          AND `contact_id` = '" . $this->user_id . "'");
            $metrics_cache = array();
            if ($DBRESULT->numRows()) {
                while ($tmp_metrics = $DBRESULT->fetchRow()) {
                    $metrics_cache[$tmp_metrics['metric_id']] = 1;
                }
            }
            $DBRESULT->free();
            $DBRESULT = $this->DBC->query("SELECT metric_id
                                           FROM metrics
                                           WHERE index_id = '" . $this->index . "'
                                           AND `hidden` = '0'
                                           ORDER BY `metric_name`");
            $count = 0;
            $odsm = array();
            while ($milist = $DBRESULT->fetchRow()) {
                $odsm[$milist["metric_id"]] = 1;
                $count++;
            }
            // only one metric => warning/critical threshold curves can be displayed
            if ($count === 1) {
                $this->onecurve = true;
            }
            $DBRESULT->free();
            $DBRESULT = $this->DB->query("SELECT vmetric_id metric_id
                                          FROM virtual_metrics
                                          WHERE index_id = '" . $this->index . "'
                                          AND ( `hidden` = '0' OR `hidden` IS NULL )
                                          AND vmetric_activate = '1'
                                          ORDER BY 'metric_name'");
            while ($milist = $DBRESULT->fetchRow()) {
                $vmilist = "v" . $milist["metric_id"];
                $odsm[$vmilist] = 1;
            }
            $DBRESULT->free();

            foreach ($odsm as $mid => $val) {
                if (!isset($metrics_cache[$mid])) {
                    $DBRESULT = $this->DB->query(
                        "INSERT INTO `ods_view_details`
                            (`metric_id`, `contact_id`, `all_user`, `index_id`)
                            VALUES ('" . $mid . "', '" . $this->user_id . "', '0', '" . $this->index . "');"
                    );
                }
            }
        }
    }

    /**
     * Clean up ds name
     *
     * @param string $dsname
     * @param bool $reverse set to true if we want to retrieve the original string to display
     * @return string
     */
    protected function cleanupDsName($dsname, $reverse = false)
    {
        if ($reverse === true) {
            $newDsName = str_replace(array("slash_", "bslash_", "pct_", "\\#"), array("/","\\", "%", "#"), $dsname);
        } else {
            $newDsName = str_replace(array("/","\\", "%", "#"), array("slash_", "bslash_", "pct_", "\\#"), $dsname);
        }
        $newDsName = preg_replace("/[^\w\-_]/", "-", $newDsName);
        return $newDsName;
    }

    /**
    * Clean up ds name in Legend
    *
    * @param string $dsname
    * @param bool $reverse set to true if we want to retrieve the original string to display
    * @return string
    */
    protected function cleanupDsNameForLegend($dsname, $reverse = false)
    {
        $newDsName = str_replace(
            array("slash_", "bslash_", "pct_",  "'", "\\"),
            array("/", "\\", "%", " ", "\\\\"),
            $dsname
        );
        $newDsName = mb_convert_encoding($newDsName, "UTF-8");
        return $newDsName;
    }

    /**
     * Get Maximum Size of metric from index_id
     *
     * @param int $metricId
     * @return float
     */
    protected function getMaxLimit($metricId = null)
    {
        $query = "SELECT MAX(`max`) as maxlimit
                  FROM metrics
                  WHERE index_id = " . $this->DB->escape($this->index);
        if (isset($metricId)) {
            $query .= " AND metric_id = " . $this->DB->escape($metricId);
        }
        $res = $this->DBC->query($query);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            $maxlimit = $row['maxlimit'];
            if ($maxlimit != 0) {
                $maxlimit = $maxlimit + ((self::OVER_MAX_LIMIT_PCT / $maxlimit) * 100);
            }
            return $maxlimit;
        }
        return 0;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $metrics
     */
    public function setMetricList($metrics)
    {
        if (is_array($metrics) && count($metrics)) {
            $this->metricsEnabled = array_keys($metrics);
        } elseif ($metrics != "") {
            $this->metricsEnabled = array($metrics);
        }
    }

    /**
     *
     * Initiate the Graph objects
     */
    public function init()
    {
        $this->setRRDOption("interlaced");
        $this->setRRDOption("imgformat", "PNG");
        if (isset($this->templateInformations["vertical_label"])) {
            $this->setRRDOption("vertical-label", $this->templateInformations["vertical_label"]);
        }

        if ($this->generalOpt["rrdtool_version"] != "1.0") {
            $this->setRRDOption("slope-mode");
        }

        if (isset($this->templateInformations["base"]) && $this->templateInformations["base"]) {
            $this->setRRDOption("base", $this->templateInformations["base"]);
        }
        if (isset($this->templateInformations["width"]) && $this->templateInformations["width"]) {
            $this->setRRDOption("width", $this->templateInformations["width"]);
        }
        if (isset($this->templateInformations["height"]) && $this->templateInformations["height"]) {
            $this->setRRDOption("height", $this->templateInformations["height"]);
        }

        /*
         * Init Graph Template Value
         */
        if (isset($this->templateInformations["lower_limit"]) && $this->templateInformations["lower_limit"] != null) {
            $this->setRRDOption("lower-limit", $this->templateInformations["lower_limit"]);
        }
        if (isset($this->templateInformations["upper_limit"]) && $this->templateInformations["upper_limit"] != "") {
            $this->setRRDOption("upper-limit", $this->templateInformations["upper_limit"]);
        } elseif (isset($this->templateInformations["size_to_max"]) && $this->templateInformations["size_to_max"]) {
            if ($this->onecurve === true) {
                $upperLimit = $this->getMaxLimit($this->metricsEnabled[0]);
            } else {
                $upperLimit = $this->getMaxLimit();
            }
            if ($upperLimit != 0) {
                $this->setRRDOption("upper-limit", $upperLimit);
            }
        }
        if ((isset($this->templateInformations["lower_limit"]) &&
             $this->templateInformations["lower_limit"] != null) ||
            (isset(
                $this->templateInformations["upper_limit"]
            ) && $this->templateInformations["upper_limit"] != null)
            ) {
            $this->setRRDOption("rigid");
            $this->setRRDOption("alt-autoscale-max");
        }

        $this->gprintScaleOption = "%s";
        if (isset($this->templateInformations["scaled"]) && $this->templateInformations["scaled"] == "0") {
            # Disable y-axis scaling
            $this->setRRDOption("units-exponent", 0);
            # Suppress Scaling in Text Output
            $this->gprintScaleOption = "";
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $elem
     */
    private static function quote($elem)
    {
        return "'".$elem."'";
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $elem
     */
    private static function vquote($elem)
    {
        return "'".substr($elem, 1, strlen($elem)-1)."'";
    }


    /**
     * Return the appropriate comparison operator (GT or LT).
     * @param $tm a reference to a curve definition
     */
    private static function getCmpOperator(&$tm)
    {
        return ($tm["warn"] > $tm["crit"]) ? "LT" : "GT";
    }

    /**
     *
     * Enter description here ...
     */
    public function initCurveList()
    {

        if (isset($this->metricsEnabled) && count($this->metricsEnabled) > 0) {
            /* Zoom or Metric Image */
            $l_rmEnabled = array();
            $l_vmEnabled = array();
            foreach ($this->metricsEnabled as $l_id) {
                if (preg_match("/^v/", $l_id)) {
                    $l_vmEnabled[] = $l_id;

                } else {
                    $l_rmEnabled[] = $l_id;
                }
            }
            /* Create selector for reals metrics */
            if (count($l_rmEnabled)) {
                $l_rselector = "metric_id IN (" . implode(
                    ",",
                    array_map(array("CentreonGraph", "quote"), $l_rmEnabled)
                ) . ")";
                $this->log("initCurveList with selector [real]= " . $l_rselector);
            }
            if (count($l_vmEnabled)) {
                $l_vselector = "vmetric_id IN (" . implode(
                    ",",
                    array_map(array("CentreonGraph", "vquote"), $l_vmEnabled)
                ) . ")";
                $this->log("initCurveList with selector [virtual]= ". $l_vselector);
            }
        } else {
            /* Full Image */
            $l_rselector = "index_id = '" . $this->index . "'";
            $l_vselector = $l_rselector;
            $this->log("initCurveList with selector= " . $l_rselector);
        }

        /* Manage reals metrics */
        if (isset($l_rselector)) {
            $DBRESULT = $this->DBC->query(
                "SELECT host_id, service_id, metric_id, metric_name, unit_name, replace(format(warn,9),',','') warn,
                    replace(format(crit,9),',','') crit
                    FROM metrics AS m, index_data AS i
                    WHERE index_id = id
                    AND " . $l_rselector . "
                    AND m.hidden = '0'
                    ORDER BY m.metric_name"
            );
            while ($rmetric = $DBRESULT->fetchRow()) {
                $this->mlist[$rmetric["metric_id"]] = $this->mpointer[0]++;
                $this->rmetrics[] = $rmetric;
            }
            $DBRESULT->free();
        }

        /* Manage virtuals metrics */
        if (isset($l_vselector)) {
            $DBRESULT = $this->DB->query("SELECT vmetric_id
                                          FROM virtual_metrics
                                          WHERE " . $l_vselector . "
                                          ORDER BY vmetric_name");
            while ($vmetric = $DBRESULT->fetchRow()) {
                $this->manageVMetric($vmetric["vmetric_id"], null, null);
            }
            $DBRESULT->free();
        }

        /* Merge all metrics */
        $mmetrics = array_merge($this->rmetrics, $this->vmetrics);
        $DBRESULT->free();
        $this->listMetricsId = array();
        $components_ds_cache = null;

        foreach ($mmetrics as $key => $metric) {
            /*
             * Check if RRD database is available.
             */
            if ($this->CheckDBAvailability($metric["metric_id"])) {

                $this->log("found metric ".$metric["metric_id"]);

                /*
                 * List of id metrics for rrdcached
                 */
                $this->listMetricsId[] = $metric["metric_id"];

                if (isset($this->metricsEnabled) &&
                    count($this->metricsEnabled) &&
                    !in_array($metric["metric_id"], $this->metricsEnabled)) {
                    if (isset($metric["need"])) {
                        $metric["need"] = 1; /* Hidden Metric */
                    } else {
                        $this->log("metric disabled ".$metric["metric_id"]);
                        continue;
                    }
                }

                if (isset($metric["virtual"])) {
                    $this->metrics[$metric["metric_id"]]["virtual"] = $metric["virtual"];
                }
                $this->metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
                $this->metrics[$metric["metric_id"]]["metric"] = $this->cleanupDsName($metric["metric_name"]);
                $this->metrics[$metric["metric_id"]]["metric_legend"] = $this->cleanupDsNameForLegend(
                    $metric["metric_name"]
                );
                $this->metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];

                if (!isset($metric["need"]) || $metric["need"] != 1) {
                    if (is_null($components_ds_cache)) {
                        $components_ds_cache = $this->DB->getAll(
                            "SELECT * FROM giv_components_template ORDER BY host_id DESC"
                        );
                    }
                    $ds_data_associated = null;
                    $ds_data_regular = null;
                    foreach ($components_ds_cache as $ds_val) {
                        /* Prepare pattern for metrics */
                        $metricPattern = '/^' .  preg_quote($ds_val['ds_name'], '/') . '$/i';
                        $metricPattern = str_replace('\\*', '.*', $metricPattern);

                        # Check associated
                        if (($ds_val['host_id'] == $metric['host_id'] || $ds_val['host_id'] == '') &&
                            ($ds_val['service_id'] == $metric['service_id'] || $ds_val['service_id'] == '') &&
                            preg_match($metricPattern, $metric['metric_name'])) {
                            $ds_data_associated = $ds_val;
                            break;
                        }

                        /* Check regular */
                        if (is_null($ds_data_regular) &&
                            preg_match('/^' . preg_quote($ds_val['ds_name'], '/') . '$/i', $metric["metric_name"])) {
                            $ds_data_regular = $ds_val;
                        }
                    }

                    $ds_data = null;
                    if (!is_null($ds_data_associated)) {
                        $ds_data = $ds_data_associated;
                    } elseif (!is_null($ds_data_regular)) {
                        $ds_data = $ds_data_regular;
                    }


                    if (!isset($ds_data) && !$ds_data) {
                        /** *******************************************
                        * Get default info in default template
                        */
                        $DBRESULT3 = $this->DB->query(
                            "SELECT ds_min, ds_max, ds_minmax_int, ds_last, ds_average, ds_total, ds_tickness,
                                ds_color_line_mode, ds_color_line
                                FROM giv_components_template
                                WHERE default_tpl1 = '1' LIMIT 1"
                        );
                        if ($DBRESULT3->numRows()) {
                            foreach ($DBRESULT3->fetchRow() as $key => $ds_val) {
                                $ds[$key] = $ds_val;
                            }
                        }
                        $DBRESULT3->free();
                        $ds_data = $ds;
                    }

                    if ($ds_data["ds_color_line_mode"] == '1') {
                        // Get random color. Only line will be set
                        $ds_data["ds_color_line"] = $this->getOVDColor($metric["metric_id"]);
                    }

                    /** **********************************
                     * Fetch Datas
                     */
                    foreach ($ds_data as $key => $ds_d) {
                        if ($key == "ds_transparency") {
                            $transparency = dechex(255-($ds_d*255)/100);
                            if (strlen($transparency) == 1) {
                                $transparency = "0" . $transparency;
                            }
                            $this->metrics[$metric["metric_id"]][$key] = $transparency;
                            unset($transparency);
                        } else {
                            $this->metrics[$metric["metric_id"]][$key] = $ds_d;
                        }
                    }

                    $escaped_chars_nb = 0;
                    if (isset($ds_data["ds_legend"]) && strlen($ds_data["ds_legend"]) > 0) {
                        $counter = 0;
                        $this->metrics[$metric["metric_id"]]["legend"] = str_replace(
                            '"',
                            '\"',
                            html_entity_decode($ds_data["ds_legend"], ENT_COMPAT, 'UTF-8'),
                            $counter
                        );
                        $escaped_chars_nb += $counter;
                    } else {
                        if (!isset($ds_data["ds_name"]) || !preg_match('/DS/', $ds_data["ds_name"], $matches)) {
                            $this->metrics[$metric["metric_id"]]["legend"] = $this->cleanupDsNameForLegend(
                                $metric["metric_name"],
                                true
                            );
                        } else {
                            $this->metrics[$metric["metric_id"]]["legend"] = (
                                isset($ds_data["ds_name"]) ? $ds_data["ds_name"] : ""
                            );
                        }
                        $this->metrics[$metric["metric_id"]]["legend"] = str_replace(
                            ":",
                            "\:",
                            $this->metrics[$metric["metric_id"]]["legend"],
                            $counter
                        );
                        $escaped_chars_nb += $counter;
                    }

                    if ($metric["unit_name"] != "") {
                        $this->metrics[$metric["metric_id"]]["legend"] .= " (".$metric["unit_name"].")";
                    }

                    /* Checks whether or not string must be decoded */
                    $lgd = $this->metrics[$metric["metric_id"]]["legend"];
                    if (preg_match('!!u', utf8_decode($lgd))) {
                        $this->metrics[$metric["metric_id"]]["legend"] = utf8_decode($lgd);
                    }

                    $this->metrics[$metric["metric_id"]]["legend_len"] = mb_strlen(
                        $this->metrics[$metric["metric_id"]]["legend"],
                        'UTF-8'
                    ) - $escaped_chars_nb;
                    $this->metrics[$metric["metric_id"]]["stack"] = (
                        isset($ds_data["ds_stack"]) && $ds_data["ds_stack"] ? $ds_data["ds_stack"] : 0
                    );
                    if ($this->onecurve) {
                        if (isset($metric["warn"]) && $metric["warn"] != 0 &&
                            $metric["warn"] != self::ENGINE_LOW_INFINITE && $metric["warn"] != self::ENGINE_HIGH_INFINITE) {
                            $this->metrics[$metric["metric_id"]]["warn"] = $metric["warn"];
                            if (!isset($ds_data["ds_color_area_warn"]) || empty($ds_data["ds_color_area_warn"])) {
                                $this->metrics[$metric["metric_id"]]["ds_color_area_warn"] =
                                    $this->generalOpt["color_warning"];
                            }
                        }
                        if (isset($metric["crit"]) && $metric["crit"] != 0 &&
                            $metric["crit"] != self::ENGINE_LOW_INFINITE && $metric["crit"] != self::ENGINE_HIGH_INFINITE) {
                            $this->metrics[$metric["metric_id"]]["crit"] = $metric["crit"];
                            if (!isset($ds_data["ds_color_area_crit"]) || empty($ds_data["ds_color_area_crit"])) {
                                $this->metrics[$metric["metric_id"]]["ds_color_area_crit"] =
                                    $this->generalOpt["color_critical"];
                            }
                        }
                    }

                    if (isset($metric["need"])) {
                        $this->metrics[$metric["metric_id"]]["need"] = $metric["need"];
                    } else {
                        $this->metrics[$metric["metric_id"]]["ds_order"] = (
                            isset($ds_data["ds_order"]) && $ds_data["ds_order"] ? $ds_data["ds_order"] : 0
                        );
                    }
                } else {
                    /* the metric is need for a CDEF metric, but not display */
                    $this->metrics[$metric["metric_id"]]["need"] = $metric["need"];
                    $this->metrics[$metric["metric_id"]]["ds_order"] = "0";
                }
                if (isset($metric["def_type"])) {
                    $this->metrics[$metric["metric_id"]]["def_type"] = $metric["def_type"];
                }
                if (isset($metric["cdef_order"])) {
                    $this->metrics[$metric["metric_id"]]["cdef_order"] = $metric["cdef_order"];
                }
                if (isset($metric["rpn_function"])) {
                    $this->metrics[$metric["metric_id"]]["rpn_function"] = $metric["rpn_function"];
                }
                if (isset($metric["ds_hidecurve"])) {
                    $this->metrics[$metric["metric_id"]]["ds_hidecurve"] = $metric["ds_hidecurve"];
                }
            }
        }
        $DBRESULT->free();

        /*
         * Sort by ds_order,then legend
         */
        uasort($this->metrics, array("CentreonGraph", "cmpmultiple"));

        /*
         * add data definitions for each metric
         */
        $cpt = 0;
        $lcdef = array();
        $this->longer = 0;
        if (isset($this->metrics)) {
            foreach ($this->metrics as $key => &$tm) {
                if (!isset($tm["virtual"]) && isset($tm["need"]) && $tm["need"] == 1) {
                    $this->addArgument("DEF:v" . $cpt . "=" . $this->dbPath . $key . ".rrd:value:AVERAGE");
                    $this->vname[$tm["metric"]] = "v" . $cpt;
                    $cpt++;
                    continue;
                }
                if (isset($tm["virtual"])) {
                    $lcdef[$key] = $tm;
                    $this->vname[$tm["metric"]] = "vv" . $cpt;
                    $cpt++;
                } else {
                    if (isset($tm["ds_invert"]) && $tm["ds_invert"]) {
                        /* Switching RRD options lower-limit & upper-limit */
                        if ($this->onecurve &&
                            isset($this->RRDoptions["lower-limit"]) &&
                            $this->RRDoptions["lower-limit"] &&
                            isset($this->RRDoptions["upper-limit"]) &&
                            $this->RRDoptions["upper-limit"]) {
                            $this->switchRRDLimitOption(
                                $this->RRDoptions["lower-limit"],
                                $this->RRDoptions["upper-limit"]
                            );
                        }
                        $this->addArgument(
                            "DEF:vi" . $cpt . "=" . $this->dbPath . $key . ".rrd:value:AVERAGE CDEF:v" . $cpt .
                            "=vi" . $cpt . ",-1,*"
                        );
                        if (isset($tm["warn"]) && $tm["warn"] != 0) {
                            $tm["warn"] *= -1;
                        }
                        if (isset($tm["crit"]) && $tm["crit"] != 0) {
                            $tm["crit"] *= -1;
                        }
                    } else {
                        $this->addArgument("DEF:v" . $cpt . "=" . $this->dbPath . $key . ".rrd:value:AVERAGE");
                    }
                    if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 &&
                        isset($tm["crit"]) && $tm["crit"] != 0) {
                        $l_CMP = "," . $this->getCmpOperator($tm) . ",";
                        $this->addArgument(
                            "CDEF:ok" . $cpt . "=v" . $cpt . "," . $tm["warn"] . $l_CMP . $tm["warn"] . ",v" .
                            $cpt . ",IF"
                        );
                        $this->addArgument(
                            "CDEF:oc" . $cpt . "=v" . $cpt . "," . $tm["crit"] . $l_CMP . "v" . $cpt . "," .
                            $tm["crit"] . ",-,0,IF"
                        );
                        $this->addArgument(
                            "CDEF:ow" . $cpt . "=v" . $cpt . "," . $tm["warn"] . $l_CMP . "v" . $cpt . "," .
                            $tm["warn"] . ",-,oc" . $cpt . ",-,0,IF"
                        );
                        $this->areaNb = $cpt;
                    }
                    $this->vname[$tm["metric"]] = "v".$cpt;
                    $cpt++;
                }
                if (!isset($tm["virtual"])) {
                    if ($tm["legend_len"] > $this->longer) {
                        $this->longer = $tm["legend_len"];
                    }
                }
            }
        }
        $deftype = array(0 => "CDEF", 1 => "VDEF");
        uasort($lcdef, array("CentreonGraph", "cmpcdeforder"));
        foreach ($lcdef as $key => &$tm) {
            $rpn = $this->subsRPN($tm["rpn_function"], $this->vname);
            $arg = $deftype[$tm["def_type"]] . ":" . $this->vname[$tm["metric"]] . "=" . $rpn;
            if (isset($tm["ds_invert"]) && $tm["ds_invert"]) {
                $this->addArgument($arg.",-1,*");
                /* Switching RRD options lower-limit & upper-limit */
                if ($this->onecurve) {
                    $this->switchRRDLimitOption($this->RRDoptions["lower-limit"], $this->RRDoptions["upper-limit"]);
                }
                if (isset($tm["warn"]) && $tm["warn"] != 0) {
                    $tm["warn"] *= -1;
                }
                if (isset($tm["crit"]) && $tm["crit"] != 0) {
                    $tm["crit"] *= -1;
                }
            } else {
                $this->addArgument($arg);
            }
            if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
                $l_CMP = "," . $this->getCmpOperator($tm) . ",";
                $nb=substr($this->vname[$tm["metric"]], 2, strlen($this->vname[$tm["metric"]])-2);
                $this->addArgument(
                    "CDEF:ok" . $nb . "=" . $this->vname[$tm["metric"]] . "," . $tm["warn"] .
                    $l_CMP . $tm["warn"] . "," . $this->vname[$tm["metric"]] . ",IF"
                );
                $this->addArgument(
                    "CDEF:oc" . $nb . "=" . $this->vname[$tm["metric"]] . "," . $tm["crit"] .
                    $l_CMP . $this->vname[$tm["metric"]] . "," . $tm["crit"] . ",-,0,IF"
                );
                $this->addArgument(
                    "CDEF:ow" . $nb . "=" . $this->vname[$tm["metric"]] . "," . $tm["warn"] .
                    $l_CMP . $this->vname[$tm["metric"]] . "," . $tm["warn"] . ",-,oc" . $nb . ",-,0,IF"
                );
            }
        }
    }

    /**
     *
     * Create Legend on the graph
     */
    public function createLegend()
    {
        $currentColors = array("Min"=>"#19EE11", "Max"=>"#F91E05", "Average"=>"#2AD1D4",
                    "Last_Min"=>"#2AD1D4", "Last_5_Min"=>"#13EB3A", "Last_15_Min"=>"#F8C706",
                    "Last_Hour"=>"#F91D05", "Up"=>"#19EE11", "Down"=>"#F91E05",
                    "Unreach"=>"#2AD1D4", "Ok"=>"#13EB3A", "Warn"=>"#F8C706",
                    "Crit"=>"#F91D05", "Unk"=>"#2AD1D4", "In_Use"=>"#13EB3A",
                    "Max_Used"=>"#F91D05", "Total_Available"=>"#2AD1D4"
                );
        
        $cpt = 0;
        $rpn_values = "";
        $rpn_expr = "";
        foreach ($this->metrics as $key => $tm) {
            if (in_array($tm['metric'], $currentColors)) {
                $tm["ds_color_line"] = $currentColors[$tm['metric']];
            }
            
            if (isset($tm["need"]) && $tm["need"] == 1) {
                continue;
            }
            if (!$this->onecurve && isset($tm["ds_hidecurve"]) && $tm["ds_hidecurve"] == 1) {
                $arg = "COMMENT:'";
            } else {
                if ((isset($tm["ds_filled"]) && $tm["ds_filled"]) || (isset($tm["ds_stack"]) && $tm["ds_stack"])) {
                    if ($this->onecurve && isset($tm["warn"]) &&
                        $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
                        $nb=$cpt;
                        if (isset($tm["virtual"])) {
                            $nb=substr($this->vname[$tm["metric"]], 2, strlen($this->vname[$tm["metric"]]) - 2);
                        }
                        if (isset($this->areaNb)) {
                            $nb = $this->areaNb;
                        }
                        $arg = "AREA:ok" . $nb . $tm["ds_color_area"];
                    } else {
                        $arg = "AREA:" . $this->vname[$tm["metric"]] . $tm["ds_color_area"];
                    }
                    if ($tm["ds_filled"]) {
                        $arg .= $tm["ds_transparency"];
                    } else {
                        $arg .= "00";
                    }
                    if ($cpt != 0 && $tm["ds_stack"]) {
                        $arg .= "::STACK CDEF:vc" . $cpt . "=" . $rpn_values.$this->vname[$tm["metric"]] . $rpn_expr;
                    }
                    $rpn_values .= $this->vname[$tm["metric"]] . ",UN,0," . $this->vname[$tm["metric"]] . ",IF,";
                    $rpn_expr .= ",+";
                    $this->addArgument($arg);
                    if ($this->onecurve && isset($tm["warn"]) &&
                        $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
                        $this->addArgument("AREA:ow" . $nb . $tm["ds_color_area_warn"] . "CF::STACK");
                        $this->addArgument("AREA:oc" . $nb . $tm["ds_color_area_crit"] . "CF::STACK");
                    }
                }

                
                if (!isset($tm["ds_stack"]) || !$tm["ds_stack"] || $cpt == 0) {
                    $arg = "LINE" . $tm["ds_tickness"] . ":" . $this->vname[$tm["metric"]];
                } else {
                    $arg = "LINE" . $tm["ds_tickness"] . ":vc" . $cpt;
                }
                $arg .= $tm["ds_color_line"] . ":'";
            }

            if (!$this->checkcurve) {
                $arg .= $tm["legend"];
                for ($i = $tm["legend_len"]; $i <= $this->longer; $i++) {
                    $arg .= " ";
                }
                // Add 2 more spaces if display only legend is set
                if (!$this->onecurve && isset($tm["ds_hidecurve"]) && $tm["ds_hidecurve"] == 1) {
                    $arg .= "  ";
                }
                $arg .= "'";
                $this->addArgument($arg);

                $vdefs = "";
                $prints = "";
                
                
                foreach (array("last" => "LAST", "min" => "MINIMUM", "max" => "MAXIMUM",
                               "average" => "AVERAGE", "total" => "TOTAL") as $name => $cf) {
                    if (!$tm["ds_" . $name]) {
                        continue;
                    }
                    $dispname = ucfirst($name);
                    $vdefs .= "VDEF:" . $this->vname[$tm["metric"]] . $dispname . "=" .
                        $this->vname[$tm["metric"]] . "," . $cf . " ";
                    if (($name == "min" || $name == "max") &&
                        (isset($tm['ds_minmax_int']) && $tm['ds_minmax_int'])) {
                        $displayformat = "%7.0lf";
                    } else {
                        $displayformat = "%7.2lf";
                    }
                    $prints .= "GPRINT:" . $this->vname[$tm["metric"]] . $dispname.":\"" .
                        $dispname . "\:" . $displayformat . ($this->gprintScaleOption) . "\" ";
                }
                $this->addArgument($vdefs);
                $this->addArgument($prints . "COMMENT:\"\\l\"");

                if ($this->onecurve) {
                    if (isset($tm["warn"]) && !empty($tm["warn"]) && $tm["warn"] != 0) {
                        $this->addArgument(
                            "HRULE:" . $tm["warn"] . $tm["ds_color_area_warn"] . ":\"Warning  \: " .
                            $this->humanReadable($tm["warn"], $tm["unit"]) . "\\l\" "
                        );
                    }
                    if (isset($tm["crit"]) && !empty($tm["crit"]) && $tm["crit"] != 0) {
                        $this->addArgument(
                            "HRULE:" . $tm["crit"] . $tm["ds_color_area_crit"] . ":\"Critical \: " .
                            $this->humanReadable($tm["crit"], $tm["unit"]) . "\""
                        );
                    }
                }
                if (!$this->onecurve) {
                    $cline = 0;
                    if (!isset($tm["ds_jumpline"])) {
                        $tm["ds_jumpline"] = 0;
                    }
                    while ($cline < $tm["ds_jumpline"]) {
                        $this->addArgument("COMMENT:\"\\c\"");
                        $cline++;
                    }
                }
            }
            if (isset($tm["ds_stack"]) && $tm["ds_stack"]) {
                $cpt++;
            }
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $l_value
     * @param unknown_type $l_unit
     */
    private function humanReadable($l_value = null, $l_unit = null)
    {
        if (empty($l_value)) {
            return;
        }

        if ($l_unit == 'B' || $l_unit == 'o' || $l_unit == 'b/s') {
            if (isset($this->RRDoptions["base"])) {
                $l_base = $this->RRDoptions["base"];
            } else {
                $l_base = 1000;
            }
            
            $l_px = array(
                "8" => array(
                    "1000" => "Y",
                    "1024" =>"Yi"
                ),
                "7" => array(
                    "1000" => "Z",
                    "1024" =>"Zi"
                ),
                "6" => array(
                    "1000" => "E",
                    "1024" =>"Ei"
                ),
                "5" => array(
                    "1000" => "P",
                    "1024" =>"Pi"
                ),
                "4" => array(
                    "1000" => "T",
                    "1024" =>"Ti"
                ),
                "3" => array(
                    "1000" => "G",
                    "1024" =>"Gi"
                ),
                "2" => array(
                    "1000" => "M",
                    "1024" =>"Mi"
                ),
                "1" => array(
                    "1000" => "K",
                    "1024" =>"Ki"
                )
            );
            $l_sign = "";
            if ($l_value < 0) {
                $l_sign = "-";
                $l_value *= -1;
            }
            $l_cpx = 0;
            while ($l_value > $l_base) {
                $l_value /= $l_base;
                $l_cpx++;
            }
            $l_upx = $l_px[$l_cpx][$l_base];
            return $l_sign.sprintf("%.2f", $l_value).$l_upx.$l_unit;
        }

        return sprintf("%.2f", $l_value) . $l_unit;
    }

    /**
     *
     * Enter description here ...
     */
    private function getDefaultGraphTemplate()
    {
        $template_id = $this->getServiceGraphID();
        if ($template_id != "") {
            $this->templateId = $template_id;
            return;
        } else {
            $command_id = getMyServiceField($this->indexData["service_id"], "command_command_id");
            $DBRESULT = $this->DB->query("SELECT graph_id FROM command WHERE `command_id` = '".$command_id."'");
            if ($DBRESULT->numRows()) {
                $data = $DBRESULT->fetchRow();
                if ($data["graph_id"] != 0) {
                    $this->templateId = $data["graph_id"];
                    unset($data);
                    return;
                }
            }
            $DBRESULT->free();
            unset($command_id);
        }
        $DBRESULT = $this->DB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
        if ($DBRESULT->numRows()) {
            $data = $DBRESULT->fetchRow();
            $this->templateId = $data["graph_id"];
            unset($data);
            $DBRESULT->free();
            return;
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $template_id
     */
    public function setTemplate($template_id = null)
    {
        if (isset($template_id)) {
            $template_id = htmlentities($template_id, ENT_QUOTES, "UTF-8");
        }

        if (!isset($template_id)|| !$template_id) {
            if ($this->indexData["host_name"] != "_Module_Meta") {
                /*
                 * graph is based on real host/service
                 */
                $this->getDefaultGraphTemplate();
            } else {
                /*
                 * Graph is based on a module check point
                 */
                $DBRESULT_meta = $this->DB->query(
                    "SELECT graph_id
                        FROM meta_service
                        WHERE `meta_name` = '" . $this->indexData["service_description"] . "'"
                );
                $meta = $DBRESULT_meta->fetchRow();
                $this->templateId = $meta["graph_id"];
                unset($meta);
            }
        } else {
            $this->templateId = htmlentities($_GET["template_id"], ENT_QUOTES, "UTF-8");
        }
        $DBRESULT = $this->DB->query(
            "SELECT *
                FROM giv_graphs_template
                WHERE graph_id = '" . $this->templateId . "' LIMIT 1"
        );
        $this->templateInformations = $DBRESULT->fetchRow();
        $DBRESULT->free();

    }

    /**
     *
     * Enter description here ...
     */
    private function getServiceGraphID()
    {
        $service_id = $this->indexData["service_id"];

        $tab = array();
        while (1) {
            $DBRESULT = $this->DB->query(
                "SELECT esi.graph_id, service_template_model_stm_id
                    FROM service
                    LEFT JOIN extended_service_information esi ON esi.service_service_id = service_id
                    WHERE service_id = '" . $service_id . "' LIMIT 1"
            );
            $row = $DBRESULT->fetchRow();
            if ($row["graph_id"]) {
                $this->graphID = $row["graph_id"];
                return $this->graphID;
            } elseif ($row["service_template_model_stm_id"]) {
                if (isset($tab[$row['service_template_model_stm_id']])) {
                    break;
                }
                $service_id = $row["service_template_model_stm_id"];
                $tab[$service_id] = 1;
            } else {
                break;
            }
        }
        return $this->graphID;
    }

    /**
     *
     * Get index Data
     */
    private function getIndexData()
    {
        if (isset($this->metricsEnabled)) {
            $svc_instance = $this->metrics[$this->metricsEnabled[0]]["index_id"];
        } else {
            $svc_instance = $this->index;
        }

        $this->log("index_data for ".$svc_instance);
        $DBRESULT = $this->DBC->query("SELECT * FROM index_data WHERE id = '" . $svc_instance . "' LIMIT 1");
        if (!$DBRESULT->numRows()) {
            $this->indexData = 0;
        } else {
            $this->indexData = $DBRESULT->fetchRow();
            /*
             * Check Meta Service description
             */
            if (preg_match("/meta_([0-9]*)/", $this->indexData["service_description"], $matches)) {
                $DBRESULT_meta = $this->DB->query(
                    "SELECT meta_name FROM meta_service WHERE `meta_id` = '" . $matches[1] . "'"
                );
                $meta = $DBRESULT_meta->fetchRow();
                $this->indexData["service_description"] = $meta["meta_name"];
                unset($meta);
                $DBRESULT_meta->free();
            }
        }
        $DBRESULT->free();

        if (isset($this->metricsEnabled)) {
            $metrictitle = " metric ".$this->metrics[$this->metricsEnabled]["metric_name"];
        } else {
            $metrictitle = "";
        }

        if ($this->indexData["host_name"] != "_Module_Meta") {
            $sdesc = $this->indexData['service_description'];
            $hname = $this->indexData['host_name'];
            if (!mb_detect_encoding($sdesc, 'UTF-8', true)) {
                $sdesc = utf8_encode($sdesc);
            }
            if (!mb_detect_encoding($hname, 'UTF-8', true)) {
                $hname = utf8_encode($hname);
            }
            $this->setRRDOption("title", $sdesc . " " . _("graph on") . " " . $hname.$metrictitle);
        } else {
            $this->setRRDOption("title", _("Graph")." ".$this->indexData["service_description"].$metrictitle);
        }
    }

    /**
     *
     * Display Start and end time on graph
     * @param $arg
     */
    public function addArgument($arg)
    {
        $this->arguments[$this->argcount++] = $arg;
    }

    /**
     * Geneate image...
     */
    public static function displayError()
    {
        $image  = imagecreate(250, 100);
        $fond   = imagecolorallocate($image, 0xEF, 0xF2, 0xFB);
        $textcolor = imagecolorallocate($image, 0, 0, 255);
        // imagestring($image, 5, 0, 0, "Session: ".$_GET['session_id']."svc_id: ".$_GET["index"], $textcolor);

        /*
         * Send Header
         */
        header("Content-Type: image/gif");

        imagegif($image);
        exit;
    }

    /**
     *
     * Enter description here ...
     * @param $name
     * @param $value
     */
    public function setFont($name, $value)
    {
        $this->fonts[$name] = $value;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function setColor($name, $value)
    {
        $this->colors[$name] = $value;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $value
     */
    public function setRRDOption($name, $value = null)
    {
        if (strpos($value, " ")!==false) {
            $value = "'".$value."'";
        }
        $this->RRDoptions[$name] = $value;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $lower
     * @param unknown_type $upper
     */
    private function switchRRDLimitOption($lower = null, $upper = null)
    {
        if (is_null($lower)) {
            unset($this->RRDoptions["upper-limit"]);
        } else {
            $this->RRDoptions["upper-limit"] = $lower;
        }
        if (is_null($upper)) {
            unset($this->RRDoptions["lower-limit"]);
        } else {
            $this->RRDoptions["lower-limit"] = $upper;
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $flag
     */
    public function setCommandLineTimeLimit($flag)
    {
        if (isset($flag)) {
            $this->flag = $flag;
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $bool
     */
    public function setOption($name, $bool = true)
    {
        $this->options[$name] = $bool;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
        return false;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $encoding
     */
    public function setHeaders($encoding, $content_length = false)
    {
        header("Content-Type: image/png");
        header("Content-Transfer-Encoding: binary");
        header("Content-Disposition: attachment; filename=\"".$this->filename.".png\";");

        if ($this->compress && $encoding) {
            header('Content-Encoding: '.$encoding);
        }
        if ($content_length != false) {
            header("Content-Length: " . $content_length);
        }
    }

    /**
     *
     * Enter description here ...
     */
    public function displayImageFlow()
    {
        $commandLine = "";

        /*
         * Send header
         */

        $this->flushRrdcached($this->listMetricsId);

        $commandLine = " graph - ";

        if (isset($this->RRDoptions["end"]) && isset($this->RRDoptions["start"])) {
            if ($this->RRDoptions["end"] - $this->RRDoptions["start"] > 2160000
                && $this->RRDoptions["end"] - $this->RRDoptions["start"] < 12960000) {
                if ($this->RRDoptions["end"] - $this->RRDoptions["start"] < 10368000 - (86400*7)) {
                    $this->setRRDOption("x-grid", "DAY:1:DAY:7:DAY:7:0:%d/%m");
                } else {
                    $this->setRRDOption("x-grid", "DAY:7:DAY:7:DAY:14:0:%d/%m");
                }
            }
        }

        foreach ($this->RRDoptions as $key => $value) {
            $commandLine .= "--".$key;
            if (isset($value)) {
                $commandLine .= "=".$value;
            }
            $commandLine .= " ";
        }
        foreach ($this->colors as $key => $value) {
            $commandLine .= "--color ".$key.$value." ";
        }
        foreach ($this->fonts as $key => $value) {
            $commandLine .= "--font ".$key.$value." ";
        }

        /*
         * ... order does matter!
         */
        if (isset($this->options["comment_time"]) && $this->options["comment_time"] == true) {
            $rrd_time  = addslashes($this->GMT->getDate("Y\/m\/d G:i", $this->RRDoptions["start"]));
            $rrd_time = str_replace(":", "\:", $rrd_time);
            $rrd_time2 = addslashes($this->GMT->getDate("Y\/m\/d G:i", $this->RRDoptions["end"])) ;
            $rrd_time2 = str_replace(":", "\:", $rrd_time2);
            $commandLine .= " COMMENT:\" From $rrd_time to $rrd_time2 \\c\" ";
        }
        foreach ($this->arguments as $arg) {
            $commandLine .= " ".$arg." ";
        }
        $gmt_export = "";
        $commandLine = preg_replace("/(\\\$|`)/", "", $commandLine);
        $timezone = $this->GMT->getMyTimezone();
        if (!empty($timezone)) {
            $gmt_export = "export TZ='".$timezone."'; " ;
        }
        $this->log($commandLine);
        /*
         * Send Binary Data
         */
        if (!$this->checkcurve) {
            if (is_writable($this->generalOpt['debug_path'])) {
                $stderr = array('file', $this->generalOpt['debug_path'].'/rrdtool.log', 'a');
            } else {
                $stderr = array('pipe', 'a');
            }
            $descriptorspec = array(
                                0 => array("pipe", "r"),  // stdin est un pipe processus va lire
                                1 => array("pipe", "w"),  // stdout est un pipe processus va ecrire
                                2 => $stderr // stderr est un fichier
                            );

            $process = proc_open(
                $gmt_export . $this->generalOpt["rrdtool_path_bin"] . " - ",
                $descriptorspec,
                $pipes,
                null,
                null
            );
            if (is_resource($process)) {
                fwrite($pipes[0], $commandLine);
                fclose($pipes[0]);

                $str = stream_get_contents($pipes[1]);
                $return_value = proc_close($process);

                /* Force no compress for image */
                $this->setHeaders(false, mb_strlen($str, '8bit'));
                print $str;
            }
        } else {
            return $commandLine;
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $name
     * @param unknown_type $tab
     * @param unknown_type $defaultValue
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
     *
     * Enter description here ...
     * @param unknown_type $l_mid
     */
    public function getOVDColor($l_mid)
    {
        $DBRESULT = $this->DB->query(
            "SELECT `rnd_color`
                FROM `ods_view_details`
                WHERE `index_id` = '" . $this->index . "'
                    AND `metric_id` = '" . $l_mid . "'
                    AND `contact_id` = '" . $this->user_id . "'"
        );
        if ($DBRESULT->numRows()) {
            $l_ovd = $DBRESULT->fetchRow();
            $DBRESULT->free();
            if (isset($l_ovd["rnd_color"]) &&
                !empty($l_ovd["rnd_color"]) &&
                preg_match("/^\#[a-f0-9]{6,6}/i", $l_ovd["rnd_color"])) {
                return $l_ovd["rnd_color"];
            }
            $l_rndcolor = $this->getRandomWebColor();
            // Update ods_view_details
            $DBRESULT = $this->DB->query(
                "UPDATE `ods_view_details` SET `rnd_color` = '".$l_rndcolor."'
                WHERE `index_id` = '" . $this->index . "'
                    AND `metric_id` = '" . $l_mid . "'
                    AND `contact_id` = '" . $this->user_id . "';"
            );
        } else {
            $l_rndcolor = $this->getRandomWebColor();
        }
        return $l_rndcolor;
    }

    /**
     *
     * Enter description here ...
     */
    public function getRandomWebColor()
    {
        $web_safe_colors = array('#000033', '#000066', '#000099', '#0000cc',
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
            '#ffccff');
            return $web_safe_colors[rand(0, sizeof($web_safe_colors)-1)];
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $a
     * @param unknown_type $b
     */
    private function cmpmultiple($a, $b)
    {
        if (isset($a["ds_order"]) && isset($b["ds_order"])) {
            if ($a["ds_order"]<$b["ds_order"]) {
                return -1;
            } elseif ($a["ds_order"]>$b["ds_order"]) {
                return 1;
            }
        }
        return strnatcasecmp(
            (isset($a["legend"]) && $a["legend"]) ? $a["legend"] : null,
            (isset($b["legend"]) && $b["legend"]) ? $b["legend"] : null
        );
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $a
     * @param unknown_type $b
     */
    private function cmpcdeforder($a, $b)
    {
        if ($a["cdef_order"] == $b["cdef_order"]) {
            return 0;
        }
        return ( $a["cdef_order"] < $b["cdef_order"] ) ? -1 : 1;
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $rpn
     * @param unknown_type $vname
     * @param unknown_type $suffix
     */
    protected function subsRPN($rpn, $vname, $suffix = null)
    {
        $l_list = preg_split("/\,/", $rpn);
        $l_rpn = "";
        foreach ($l_list as $l_m) {
            if (isset($vname[$l_m])) {
                if ($suffix == null) {
                    $l_rpn .= $vname[$l_m];
                } elseif (isset($vname[$l_m.$suffix])) {
                    $l_rpn .= $vname[$l_m.$suffix];
                } else {
                    return "No_RPN_Found";
                }
                $l_rpn .= ",";
            } else {
                $l_rpn .= $l_m.",";
            }
        }
        return substr($l_rpn, 0, strlen($l_rpn) - 1);
    }

    /**
     *
     * need : [0]->need/visible [1]->need/hidden
     * @param unknown_type $vId
     * @param unknown_type $vName
     * @param unknown_type $indexId
     */
    private function manageVMetric($vId, $vName, $indexId)
    {
        /* Recursif function */

        /*
         * Manage Virtual Metrics
         */
        $lWhidden = "";
        if (!$this->checkcurve) {
            $lWhidden = " AND (hidden = '0' OR hidden IS NULL) AND vmetric_activate = '1'";
        }

        if (is_null($vId)) {
            $lWhere = "vmetric_name = '" . $vName . "' AND index_id ='" . $indexId . "'";
        } else {
            $lWhere = "vmetric_id = '" . $vId . "'" . $lWhidden;
        }


        $lPqy = $this->DB->query(
            "SELECT vmetric_id metric_id, index_id, vmetric_name metric_name, unit_name,
                replace(format(warn,9),',','') warn, replace(format(crit,9),',','') crit, def_type, rpn_function
                FROM virtual_metrics WHERE " . $lWhere . " ORDER BY metric_name"
        );
        /*
         * There is only one metric_id
         */
        if ($lPqy->numRows() == 1) {
            $lVmetric = $lPqy->fetchRow();
            $lPqy->free();
            if (!isset($this->mlist["v".$lVmetric["metric_id"]])) {
                if (is_null($vId)) {
                    $lVmetric["need"] = 1; /* 1 : Need this virtual metric : Hidden */
                }
                /*
                 * Find Host/Service For this metric_id
                 */
                $l_poqy = $this->DBC->query(
                    "SELECT host_id, service_id FROM index_data WHERE id = '" . $lVmetric["index_id"] . "'"
                );
                $l_indd = $l_poqy->fetchRow();
                $l_poqy->free();
                /* Check for real or virtual metric(s) in the RPN function */
                $l_mlist = preg_split("/\,/", $lVmetric["rpn_function"]);
                foreach ($l_mlist as $l_mnane) {
                    /*
                     * Check for a real metric 
                     */
                    $l_poqy = $this->DBC->query(
                        "SELECT host_id, service_id, metric_id, metric_name, unit_name,
                            replace(format(warn,9),',','') warn, replace(format(crit,9),',','') crit
                            FROM metrics AS m, index_data as i
                            WHERE index_id = id AND index_id = '" . $lVmetric["index_id"] . "'
                                AND metric_name = '" . $l_mnane . "'"
                    );
                    if ($l_poqy->numRows() == 1) {
                        /* Find a real metric in the RPN function */
                        $l_rmetric = $l_poqy->fetchrow();
                        $l_poqy->free();
                        $l_rmetric["need"] = 1; /* 1 : Need this real metric - hidden */
                        if (!isset($this->mlist[$l_rmetric["metric_id"]])) {
                            $this->mlist[$l_rmetric["metric_id"]] = $this->mpointer[0]++;
                            $this->rmetrics[] = $l_rmetric;
                        } else {
                            /* We Already Find the real metrics in the array */
                            /* Make sure, it's add */
                            $l_pointer = $this->mlist[$l_rmetric["metric_id"]];
                            if (!isset($this->rmetrics[$l_pointer]["need"])) {
                                $this->rmetrics[$l_pointer]["need"] = 0;
                            }
                        }
                    } elseif ($l_poqy->numRows() == 0) {
                            /* key : id or vname and iid */
                            $l_poqy->free();
                            $this->manageVMetric(null, $l_mnane, $lVmetric["index_id"]);
                    } else {
                        $l_poqy->free();
                    }
                }
                $lVmetric["metric_id"] = "v".$lVmetric["metric_id"];
                $lVmetric["host_id"] = $l_indd["host_id"];
                $lVmetric["service_id"] = $l_indd["service_id"];
                $lVmetric["virtual"] = 1;
                $lVmetric["cdef_order"] = $this->mpointer[1];
                $this->mlist[$lVmetric["metric_id"]] = $this->mpointer[1]++;
                $this->vmetrics[] = $lVmetric;
            } else {
                /* We Already Find the virtual metrics in the array */
                /* Make sure, it's add */
                $l_pointer = $this->mlist["v" . $lVmetric["metric_id"]];
                if (is_null($vId)) {
                    if (!isset($this->vmetrics[$l_pointer]["need"]) || $this->vmetrics[$l_pointer]["need"] != 1) {
                        $this->vmetrics[$l_pointer]["need"] = 0;
                    } else {
                        if (!isset($this->vmetrics[$l_pointer]["need"]) || $this->vmetrics[$l_pointer]["need"] == 1) {
                            $this->vmetrics[$l_pointer]["need"] = 0;
                        }
                    }
                }
            }
        } else {
            $lPqy->free();
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $message
     */
    private function log($message)
    {
        if ($this->generalOpt['debug_rrdtool'] && is_writable($this->generalOpt['debug_path'])) {
            error_log(
                "[" . date("d/m/Y H:i") ."] RDDTOOL : ".$message." \n",
                3,
                $this->generalOpt["debug_path"]."rrdtool.log"
            );
        }
    }

    /**
     *
     * Enter description here ...
     * @param unknown_type $metric_id
     */
    private function checkDBAvailability($metric_id)
    {
        if (!file_exists($this->dbPath.$metric_id.".rrd") && !preg_match("/^v/", $metric_id)) {
            return 0;
        }
        return 1;
    }

    /**
     * Flush metrics in rrdcached
     *
     * @param array $metricsId The list of metrics
     * @return bool
     */
    protected function flushRrdcached($metricsId)
    {
        if (!isset($this->generalOpt['rrdcached_enable'])
            || $this->generalOpt['rrdcached_enable'] == 0) {
            return true;
        }

        /*
         * Connect to rrdcached
         */
        $errno = 0;
        $errstr = '';
        if (isset($this->generalOpt['rrdcached_port'])
            && trim($this->generalOpt['rrdcached_port']) != '') {
            $sock = @fsockopen('127.0.0.1', trim($this->generalOpt['rrdcached_port']), $errno, $errstr);
            if ($sock === false) {
                return false;
            }
        } elseif (isset($this->generalOpt['rrdcached_unix_path'])
            && trim($this->generalOpt['rrdcached_unix_path']) != '') {
            $sock = @fsockopen('unix://' . trim($this->generalOpt['rrdcached_unix_path']), $errno, $errstr);
        } else {
            return false;
        }
        if (false === $sock) {
            // @todo log the error
            return false;
        }
        /*
         * Run batch mode
         */
        if (false === fputs($sock, "BATCH\n")) {
            @fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            @fclose($sock);
            return false;
        }
        /*
         * Run flushs
         */
        foreach ($metricsId as $metricId) {
            $fullpath = realpath($this->dbPath . $metricId . '.rrd');
            $cmd = 'FLUSH ' . $fullpath;
            if (false === fputs($sock, $cmd . "\n")) {
                @fclose($sock);
                return false;
            }
        }
        /*
         * Execute commands
         */
        if (false === fputs($sock, ".\n")) {
            @fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            @fclose($sock);
            return false;
        }
        /*
         * Send close
         */
        fputs($sock, "QUIT\n");
        @fclose($sock);
        return true;
    }
    
    /**
     * Returns index data id
     *
     * @param int $hostId
     * @param int $serviceId
     * @return int
     */
    public function getIndexDataId($hostId, $serviceId)
    {
        $sql = "SELECT id FROM index_data 
            WHERE host_id = " . $this->DBC->escape($hostId) . "
                AND service_id = " . $this->DBC->escape($serviceId);
        $res = $this->DBC->query($sql);
        if ($res->numRows()) {
            $row = $res->fetchRow();
            return $row['id'];
        }
        return 0;
    }
    
    /**
     * Returns true if status graph exists
     *
     * @param int $hostId
     * @param int $serviceId
     * @return bool
     */
    public function statusGraphExists($hostId, $serviceId)
    {
        $id = $this->getIndexDataId($hostId, $serviceId);
        if (is_file($this->dbStatusPath."/".$id.".rrd")) {
            return true;
        }
        return false;
    }
}
