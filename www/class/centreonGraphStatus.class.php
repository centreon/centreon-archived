<?php
/**
 * Copyright 2005-2016 Centreon
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
 */

/**
 * Class for get status for a service and return this on JSON
 *
 */
class CentreonGraphStatus
{
    /**
     * Constructor
     *
     * @param int $index The index data id
     * @param int $start The start time
     * @param int $end   The end time
     */
    public function __construct($index, $start, $end)
    {
        $this->pearDB = new CentreonDB();
        $this->pearDBMonitoring = new CentreonDB('centstorage');
        $this->index = $index;
        $this->startTime = $start;
        $this->endTime = $end;
        $this->statusPath = $this->getStatusPath();
        $this->generalOpt = $this->getOptions();
        $this->rrdCachedOptions = $this->getRrdCachedOptions();
    }

    /**
     * Get the metrics
     *
     * @return mixed
     */
    public function getData()
    {
        $this->setRRDOption("imgformat", "JSONTIME");
        $this->setRRDOption("start", $this->startTime);
        $this->setRRDOption("end", $this->endTime);

        $path = $this->statusPath . '/' . $this->index . '.rrd';
        if (false === file_exists($path)) {
            throw new RuntimeException();
        }

        $this->addArgument("DEF:v1=" . $path . ":value:AVERAGE");
        $this->addArgument("VDEF:v1Average=v1,AVERAGE");
        $this->addArgument("LINE1:v1#0000ff:v1");

        $jsonData = $this->getJsonStream();

        $metrics = array(
            'critical' => array(),
            'warning' => array(),
            'ok' => array(),
            'unknown' => array()
        );

        $lastStatus = null;
        $interval = array();
        foreach ($jsonData['data'] as $row) {
            $time = (string)$row[0];
            $value = $row[1];
            if (is_null($value)) {
                $currentStatus = 'unknown';
            } elseif ($value < 75) {
                $currentStatus = 'critical';
            } elseif ($value == 100) {
                $currentStatus = 'ok';
            } elseif ($value > 74) {
                $currentStatus = 'warning';
            } else {
                $currentStatus = 'unknown';
            }
            if (is_null($lastStatus)) {
                $interval = array(
                    'start' => $time,
                    'end' => null
                );
                $lastStatus = $currentStatus;
            } elseif ($lastStatus !== $currentStatus) {
                $interval['end'] = $time;
                $metrics[$lastStatus][] = $interval;
                $lastStatus = $currentStatus;
                $interval = array(
                    'start' => $time,
                    'end' => null
                );
            }
        }

        $interval['end'] = $time;
        $metrics[$lastStatus][] = $interval;

        return $metrics;
    }
    
    /**
     * Flush status rrdfile from cache
     *
     * @param int $indexData The index data id
     *
     * @return bool
     */
    public function flushRrdCached($indexData)
    {
        if (!isset($this->rrdCachedOptions['rrd_cached_option'])
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
            return false;
        }

        if (false === fputs($sock, "BATCH\n")) {
            @fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            @fclose($sock);
            return false;
        }

        $fullpath = realpath($this->statusPath . $indexData . '.rrd');
        $cmd = 'FLUSH ' . $fullpath;
        if (false === fputs($sock, $cmd . "\n")) {
            @fclose($sock);
            return false;
        }

        if (false === fputs($sock, ".\n")) {
            @fclose($sock);
            return false;
        }
        if (false === fgets($sock)) {
            @fclose($sock);
            return false;
        }

        fputs($sock, "QUIT\n");
        @fclose($sock);
        return true;
    }

    /**
     * Get general options
     *
     * @return array The list of genreal options
     */
    protected function getOptions()
    {
        $result = array();
        $query = "SELECT `key`, `value` FROM options
            WHERE `key` IN ('rrdtool_path_bin', 'rrdcached_enabled', 'debug_rrdtool', 'debug_path')";
        try {
            $res = $this->pearDB->query($query);
        } catch (\PDOException $e) {
            throw new RuntimeException();
        }
        while ($row = $res->fetch()) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
    }

    /**
     * Get the RRDCacheD options of local RRD Broker
     *
     * @return array of RRDCacheD options
     */
    protected function getRrdCachedOptions()
    {
        $result = $this->pearDB->query(
            "SELECT config_key, config_value
            FROM cfg_centreonbroker_info AS cbi
            INNER JOIN cfg_centreonbroker AS cb ON (cb.config_id = cbi.config_id)
            INNER JOIN nagios_server AS ns ON (ns.id = cb.ns_nagios_server)
            WHERE ns.localhost = '1'
            AND cbi.config_key IN ('rrd_cached_option', 'rrd_cached')"
        );

        $rrdCachedOptions = [];
        while ($row = $result->fetch()) {
            $this->rrdCachedOptions[$row['config_key']] = $row['config_value'];
        }

        return $rrdCachedOptions;
    }
    
    /**
     * Get the status RRD path
     *
     * @return string The status RRD path
     */
    protected function getStatusPath()
    {
        $query = 'SELECT RRDdatabase_status_path FROM config';
        $res = $this->pearDBMonitoring->query($query);
        $row = $res->fetch();
        if ($row === null) {
            throw new RuntimeException('Missing status directory configuration');
        }
        return $row['RRDdatabase_status_path'];
    }

    /**
     * Get the index data id for a service
     *
     * @param int        $hostId The host id
     * @param int        $serviceId The service id
     * @param CentreonDB $dbc The database connection to centreon_storage
     *
     * @return int
     */
    public static function getIndexId($hostId, $serviceId, $dbc)
    {
        $query = "SELECT id FROM index_data WHERE host_id = " . $hostId . " AND service_id = " . $serviceId;
        $res = $dbc->query($query);
        $row = $res->fetch();

        if (false == $row) {
            throw new OutOfRangeException();
        }
        return $row['id'];
    }

    /**
     * Add argument rrdtool
     *
     * @param string $arg
     *
     * @return void
     */
    protected function addArgument($arg)
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
    protected function setRRDOption($name, $value = null)
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
     * Get rrdtool result
     *
     * @return mixed
     */
    private function getJsonStream()
    {
        $this->flushRrdcached($this->index);

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
                            0 => array("pipe", "r"),
                            1 => array("pipe", "w"),
                            2 => $stderr
                        );

        $process = proc_open($this->generalOpt['rrdtool_path_bin']. " - ", $descriptorspec, $pipes, null, null);

        if (is_resource($process)) {
            fwrite($pipes[0], $commandLine);
            fclose($pipes[0]);

            $str = stream_get_contents($pipes[1]);
            $returnValue = proc_close($process);

            $str = preg_replace("/OK u:.*$/", "", $str);
            $rrdData = json_decode($str, true);
        }

        return $rrdData;
    }
}
