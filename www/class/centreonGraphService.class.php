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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
require_once 'centreonGraph.class.php';

/**
 * Class for get metrics for a service and return this on JSON
 *
 */
class CentreonGraphService extends CentreonGraph
{
    /**
     * Constructor
     *
     * @param int $index The index data id
     * @param string $sid The session id
     */
    public function __construct($index, $userId)
    {
        parent::__construct($userId, $index, 0, 1);
    }

    /**
     * Get the metrics
     *
     * @param integer $rows The number of points returned (Default: 200)
     * @param array
     */
    public function getData($rows = 200)
    {
        /* Flush RRDCached for have the last values */
        $this->flushRrdCached($this->listMetricsId);
        
        $commandLine = "";

        /* Build command line */
        $commandLine .= " xport ";
        $commandLine .= " --start " . $this->_RRDoptions['start'];
        $commandLine .= " --end " . $this->_RRDoptions['end'];
        $commandLine .= " --maxrows " . $rows;

        $metrics = array();
        $i = 0;
        foreach ($this->metrics as $metric) {
            $path = $this->dbPath . '/' . $metric['metric_id'] . '.rrd';
            if (false === file_exists($path)) {
                throw new RuntimeException();
            }
            $commandLine .= " DEF:v" . $i . "=" . $path . ":value:AVERAGE";
            $commandLine .= " XPORT:v" . $i . ":v" . $i;
            $i++;
            $info = array(
                "data" => array(),
                "legend" => $metric["metric_legend"],
                "graph_type" => "line",
                "unit" => $metric["unit"],
                "color" => $metric["ds_color_line"],
                "negative" => false
            );
            if (isset($metric['ds_color_area'])) {
                $info['graph_type'] = "area";
            }
            if (isset($metric['ds_invert']) && $metric['ds_invert'] == 1) {
                $info['negative'] = true;
            }
            $metrics[] = $info;
        }

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'a'),
        );

        $process = proc_open($this->general_opt["rrdtool_path_bin"] . " - ", $descriptorspec, $pipes, NULL, NULL);
        if (false === is_resource($process)) {
            throw new RuntimeException();
        }
        fwrite($pipes[0], $commandLine);
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
        $rows = $xml->xpath("//xport/data/row");
        foreach ($rows as $row) {
            $time = null;
            $i = 0;
            foreach ($row->children() as $info) {
                if (is_null($time)) {
                    $time = (string)$info;
                } else {
                    if (strtolower($info) === "nan" || is_null($info)) {
                        $metrics[$i++]['data'][$time] = $info;
                    } elseif ($metrics[$i]['negative']) {
                        $metrics[$i++]['data'][$time] = floatval((string)$info) * -1;
                    } else {
                        $metrics[$i++]['data'][$time] = floatval((string)$info);
                    }
                }
            }
        }

        return $metrics;
    }

    /**
     * Get the index data id for a service
     *
     * @param int $hostId The host id
     * @param int $serviceId The service id
     * @param CentreonDB $dbc The database connection to centreon_storage
     * @return int
     */
    public static function getIndexId($hostId, $serviceId, $dbc)
    {
        $query = "SELECT id FROM index_data
            WHERE host_id = " . $hostId . " AND service_id = " . $serviceId;
        $res = $dbc->query($query);
        $row = $res->fetchRow();

        if (false == $row) {
            throw new OutOfRangeException();
        }

        return $row['id'];
    }
}
