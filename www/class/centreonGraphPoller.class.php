<?php
/**
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

/**
 *  Class tp get metrics for a poller and return this on JSON
 */
class centreonGraphPoller
{
    protected $generalOpt;
    protected $extraDatas;

    /**
     *
     * @var string Rrdtool command line
     */
    private $commandLine;

    /**
     *
     * @var int Poller id
     */
    private $pollerId;

    /**
     *
     * @var array Graph titles
     */
    private $title;

    /**
     *
     * @var array
     */
    private $options;

    /**
     *
     * @var array
     */
    private $differentStats;

    /**
     *
     * @var array
     */
    private $colors;

    /**
     *
     * @var \CentreonDB
     */
    private $db;

    /**
     *
     * @var \CentreonDB
     */
    private $dbMonitoring;

    /**
     *
     * @var string
     */
    private $graphName;

    /**
     *
     * @var string
     */
    private $nagiosStatsPath;

    /**
     *
     * @var array
     */
    private $metricsInfos = array();

    /**
     *
     * @param \CentreonDB $db
     * @param \CentreonDB $dbMonitoring
     */
    public function __construct($db, $dbMonitoring)
    {
        $this->db = $db;
        $this->dbMonitoring = $dbMonitoring;
                
        $this->initGraphOptions();
        $this->initRrd();
    }

    /**
     * Set poller graph
     *
     * @param int    $pollerId
     * @param string $graphName
     *
     * @return void
     */
    public function setPoller($pollerId, $graphName)
    {
        $this->graphName = $graphName;
        $this->pollerId = $pollerId;
        $this->extraDatas = array(
            'title' => $this->title[$graphName],
            'base' => 1000,
        );
        $this->rrdOptions = array();
        $this->arguments = array();
        
        $this->setRRDOption("imgformat", "JSONTIME");
    }

    /**
     * Init graph titles
     *
     * @return void
     */
    private function initGraphOptions()
    {
        $this->title = array(
            "active_host_check" => _("Host Check Execution Time"),
            "active_host_last" => _("Hosts Actively Checked"),
            "host_latency" => _("Host check latency"),
            "active_service_check" => _("Service Check Execution Time"),
            "active_service_last" => _("Services Actively Checked"),
            "service_latency" => _("Service check latency"),
            "cmd_buffer" => _("Commands in buffer"),
            "host_states" => _("Host status"),
            "service_states" => _("Service status")
        );

        $this->colors = array(
            "Min" => "#88b917",
            "Max" => "#e00b3d",
            "Average" => "#00bfb3",
            "Last_Min" => "#00bfb3",
            "Last_5_Min" => "#88b917",
            "Last_15_Min" => "#ff9a13",
            "Last_Hour" => "#F91D05",
            "Up" => "#88b917",
            "Down" => "#e00b3d",
            "Unreach" => "#818285",
            "Ok" => "#88b917",
            "Warn" => "#ff9a13",
            "Crit" => "#F91D05",
            "Unk" => "#bcbdc0",
            "In_Use" => "#88b917",
            "Max_Used" => "#F91D05",
            "Total_Available" => "#00bfb3"
        );

        $this->options = array(
            "active_host_check" => "nagios_active_host_execution.rrd",
            "active_host_last" => "nagios_active_host_last.rrd",
            "host_latency" => "nagios_active_host_latency.rrd",
            "active_service_check" => "nagios_active_service_execution.rrd",
            "active_service_last" => "nagios_active_service_last.rrd",
            "service_latency" => "nagios_active_service_latency.rrd",
            "cmd_buffer" => "nagios_cmd_buffer.rrd",
            "host_states" => "nagios_hosts_states.rrd",
            "service_states" => "nagios_services_states.rrd"
        );

        $this->differentStats = array(
            "nagios_active_host_execution.rrd" => array("Min", "Max", "Average"),
            "nagios_active_host_last.rrd" => array("Last_Min", "Last_5_Min", "Last_15_Min", "Last_Hour"),
            "nagios_active_host_latency.rrd" => array("Min", "Max", "Average"),
            "nagios_active_service_execution.rrd" => array("Min", "Max", "Average"),
            "nagios_active_service_last.rrd" => array("Last_Min", "Last_5_Min", "Last_15_Min", "Last_Hour"),
            "nagios_active_service_latency.rrd" => array("Min", "Max", "Average"),
            "nagios_cmd_buffer.rrd" => array("In_Use", "Max_Used", "Total_Available"),
            "nagios_hosts_states.rrd" => array("Up", "Down", "Unreach"),
            "nagios_services_states.rrd" => array("Ok", "Warn", "Crit", "Unk")
        );
    }

    /**
     * Get rrdtool options
     *
     * @return void
     */
    private function initRrd()
    {
        $DBRESULT = $this->db->query("SELECT * FROM `options`");

        $this->generalOpt = array();
        while ($option = $DBRESULT->fetch()) {
            $this->generalOpt[$option["key"]] = $option["value"];
        }
        $DBRESULT->closeCursor();

        $DBRESULT2 = $this->dbMonitoring->query("SELECT RRDdatabase_nagios_stats_path FROM config");
        $nagiosStats = $DBRESULT2->fetch();
        $this->nagiosStatsPath = $nagiosStats['RRDdatabase_nagios_stats_path'];
        $DBRESULT2->closeCursor();
    }

    /**
     *
     * @return string
     */
    public function getGraphName()
    {
        return $this->graphName;
    }

    /**
     *
     * @param string $graphName
     */
    public function setGraphName($graphName = '')
    {
        $this->graphName = $graphName;
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
        if (strpos($value, " ")!==false) {
            $value = "'".$value."'";
        }
        $this->rrdOptions[$name] = $value;
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
        if ($this->generalOpt['debug_rrdtool'] &&
            is_writable($this->generalOpt['debug_path'])) {
            error_log(
                "[" . date("d/m/Y H:i") ."] RDDTOOL : ".$message." \n",
                3,
                $this->generalOpt['debug_path'] . "rrdtool.log"
            );
        }
    }

    /**
     * Add arguments for rrdtool command line
     *
     * @param int $start
     * @param int $end
     *
     * @return void
     *
     * @throws RuntimeException
     */
    public function buildCommandLine($start, $end)
    {
        $this->extraDatas['start'] = $start;
        $this->extraDatas['end'] = $end;
        
        $this->setRRDOption("start", $start);
        $this->setRRDOption("end", $end);
        
        $this->metrics = array();

        $metrics = $this->differentStats[$this->options[$this->graphName]];

        $i = 0;
        foreach ($metrics as $metric) {
            $path = $this->nagiosStatsPath . '/perfmon-' . $this->pollerId . '/' . $this->options[$this->graphName];
            if (false === file_exists($path)) {
                throw new RuntimeException();
            }
            
            $displayformat = "%7.2lf";
            $this->addArgument("DEF:v" . $i . "=" . $path . ":" . $metric . ":AVERAGE");
            $this->addArgument("VDEF:v" . $i . $metric . "=v" . $i . ",AVERAGE");
            $this->addArgument("LINE1:v" . $i . "#0000ff:v" . $i);
            $this->addArgument("GPRINT:v" . $i . $metric . ":\"" . $metric . "\:" . $displayformat . "\" ");

            $this->metrics[] = array(
                "metric_id" => $i,
                "metric" => $metric,
                "metric_legend" => $metric,
                "legend" => $metric,
                "ds_data" => array(
                    "ds_filled" => 0,
                    "ds_color_line" => $this->colors[$metric],
                )
            );

            $i++;
        }
    }

    /**
     * Get graph result
     *
     * @param int $start
     * @param int $end
     *
     * @return array
     *
     * @throws RuntimeException
     */
    public function getGraph($start, $end)
    {
        $this->buildCommandLine($start, $end);
        return $this->getJsonStream();
    }

    /**
     * Get rrdtool result
     *
     * @return mixed
     */
    private function getJsonStream()
    {
        $commandLine = "";
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

        if (is_writable($this->generalOpt['debug_path'])) {
            $stderr = array('file', $this->generalOpt['debug_path'] . '/rrdtool.log', 'a');
        } else {
            $stderr = array('pipe', 'a');
        }
        $descriptorspec = array(
                            0 => array("pipe", "r"),  // stdin is pipe for reading
                            1 => array("pipe", "w"),  // stdout is pipe for writing
                            2 => $stderr // stderr is a file
                        );

        $process = proc_open($this->generalOpt['rrdtool_path_bin']. " - ", $descriptorspec, $pipes, null, null);
        $this->graphData = array(
            'global' => $this->extraDatas,
            'metrics' => array(),
        );
        foreach ($this->metrics as $metric) {
            $this->graphData['metrics'][] = $metric;
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
     * Parse rrdtool result
     *
     * @param mixed $rrdData
     *
     * @return void
     */
    private function formatByMetrics($rrdData)
    {
        $this->graphData['times'] = array();
        $size = count($rrdData['data']);
        $gprintsSize = count($rrdData['meta']['gprints']);
        
        for ($i = 0; $i < $size; $i++) {
            $this->graphData['times'][] = $rrdData['data'][$i][0];
        }
        
        $i = 1;
        $gprintsPos = 0;
        foreach ($this->graphData['metrics'] as &$metric) {
            $metric['data'] = array();
            $metric['prints'] = array();
            
            $insert = 0;
            $metricFullname = 'v' . $metric['metric_id'];
            for (; $gprintsPos < $gprintsSize; $gprintsPos++) {
                if (isset($rrdData['meta']['gprints'][$gprintsPos]['line'])) {
                    if ($rrdData['meta']['gprints'][$gprintsPos]['line'] == $metricFullname) {
                        $insert = 1;
                    } else {
                        break;
                    }
                } elseif ($insert == 1) {
                    $metric['prints'][] = array_values($rrdData['meta']['gprints'][$gprintsPos]);
                }
            }
            
            for ($j = 0; $j < $size; $j++) {
                $metric['data'][] = $rrdData['data'][$j][$i];
            }
            $i++;
        }
    }
}
