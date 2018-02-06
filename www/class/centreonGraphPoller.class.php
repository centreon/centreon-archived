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

require_once "centreonGMT.class.php";


/**
 *  Class tp get metrics for a poller and return this on JSON
 */
class centreonGraphPoller
{
    /**
     *
     * @var array Array of rrdtool options
     */
    private $rrdtoolOptions;

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
     * @var int User id
     */
    private $userId;

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
     * @var type
     */
    private $db;

    /**
     *
     * @var type
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
     * @param type $db
     * @param type $dbMonitoring
     * @param type $pollerId
     * @param type $userId
     * @param type $start
     * @param type $end
     */
    public function __construct($db, $dbMonitoring, $pollerId, $userId, $start, $end)
    {
        $this->db = $db;
        $this->dbMonitoring = $dbMonitoring;
        $this->pollerId = $pollerId;
        $this->userId = $userId;
        $this->start = $start;
        $this->end = $end;

        $this->initGraphOptions();
        $this->initRrd();
    }

    /**
     *
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
     * Set rrdtool options
     */
    private function initRrd()
    {
        $DBRESULT = $this->db->query("SELECT * FROM `options`");

        while ($option = $DBRESULT->fetchRow()) {
            if (strpos($option["key"], 'rrdtool', 0) !== false) {
                $this->rrdtoolOptions[$option["key"]] = $option["value"];
            }
        }
        $DBRESULT->free();

        $DBRESULT2 = $this->dbMonitoring->query("SELECT RRDdatabase_nagios_stats_path FROM config");
        $nagiosStats = $DBRESULT2->fetchRow();
        $this->nagiosStatsPath = $nagiosStats['RRDdatabase_nagios_stats_path'];
        $DBRESULT2->free();
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
     *
     * @param int $rows
     * @throws RuntimeException
     */
    public function buildCommandLine($rows)
    {
        $this->commandLine = '';

        /* Build command line */
        $this->commandLine .= " xport ";
        $this->commandLine .= " --start " . $this->start;
        $this->commandLine .= " --end " . $this->end;
        $this->commandLine .= " --maxrows " . $rows;

        $metrics = $this->differentStats[$this->options[$this->graphName]];

        $i = 0;
        foreach ($metrics as $metric) {
            $path = $this->nagiosStatsPath . '/perfmon-' . $this->pollerId . '/' . $this->options[$this->graphName];
            if (false === file_exists($path)) {
                throw new RuntimeException();
            }
            $this->commandLine .= " DEF:v" . $i . "=" . $path . ":$metric:AVERAGE";
            $this->commandLine .= " GPRINT:v". ($i) .":LAST:\"\:%7.2lf%s\l\"";
            $this->commandLine .= " XPORT:v" . $i . ":v" . $i;

            $info = array(
                "data" => array(),
                "legend" => $metric,
                "graph_type" => "line",
                "unit" => null,
                "color" => $this->colors[$metric],
                "negative" => false,
                "stack" => false,
                "crit" => null,
                "warn" => null
            );

            if (isset($metric['ds_color_area']) &&
              isset($metric['ds_filled']) &&
              $metric['ds_filled'] === '1') {
                $info['graph_type'] = "area";
            }
            if (isset($metric['ds_invert']) && $metric['ds_invert'] == 1) {
                $info['negative'] = true;
            }
            if (isset($metric['stack'])) {
                $info['stack'] = $metric['stack'] == 1 ? true : false;
            }
            if (isset($metric['crit'])) {
                $info['crit'] = $metric['crit'];
            }
            if (isset($metric['warn'])) {
                $info['warn'] = $metric['warn'];
            }
            $this->metricsInfos[] = $info;

            $i++;
        }
    }

    /**
     *
     * @param int $rows
     * @return array
     * @throws RuntimeException
     */
    public function getData($rows = 200)
    {
        //$this->initRrdtoolCommandLine();

        $this->buildCommandLine($rows);

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'a'),
        );

        $process = proc_open($this->rrdtoolOptions['rrdtool_path_bin'] . " - ", $descriptorspec, $pipes, null, null);
        if (false === is_resource($process)) {
            throw new RuntimeException();
        }
        fwrite($pipes[0], $this->commandLine);
        fclose($pipes[0]);

        $str = '';
        stream_set_blocking($pipes[1], 0);
        do {
            $status = proc_get_status($process);
            $str .= stream_get_contents($pipes[1]);
        } while ($status['running']);

        $str .= stream_get_contents($pipes[1]);

        /* Remove text of the end of the stream */
        $str = preg_replace("/<\/xport>(.*)$/s", "</xport>", $str);

        $exitCode = $status['exitcode'];

        proc_close($process);

        if ($exitCode != 0) {
            throw new RuntimeException();
        }

        /* Transform XML to values */
        $xml = simplexml_load_string($str);
        if (false === $xml) {
            throw new RuntimeException();
        }
        $xmlRows = $xml->xpath("//xport/data/row");
        foreach ($xmlRows as $xmlRow) {
            $time = null;
            $i = 0;
            foreach ($xmlRow->children() as $info) {
                if (is_null($time)) {
                    $time = (string)$info;
                } else {
                    if (strtolower($info) === "nan" || is_null($info)) {
                        $this->metricsInfos[$i++]['data'][$time] = $info;
                    } elseif ($this->metricsInfos[$i]['negative']) {
                        $this->metricsInfos[$i++]['data'][$time] = floatval((string)$info) * -1;
                    } else {
                        $this->metricsInfos[$i++]['data'][$time] = floatval((string)$info);
                    }
                }
            }
        }
        return $this->metricsInfos;
    }

    public function getLegends()
    {
        $legends = array();
        $metrics = $this->differentStats[$this->options[$this->graphName]];
        foreach ($metrics as $metric) {
            $legends[$metric] = array(
                'extras' => array()
            );
        }
        return $legends;
    }
}
