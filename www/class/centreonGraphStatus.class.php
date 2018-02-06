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
     * @param int $end The end time
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
        $this->flushRrdCached($this->index);
        
        $commandLine = "";

        /* Build command line */
        $commandLine .= " xport ";
        $commandLine .= " --start " . $this->startTime;
        $commandLine .= " --end " . $this->endTime;
        $commandLine .= " --maxrows " . $rows;

        $metrics = array(
            'critical' => array(),
            'warning' => array(),
            'ok' => array(),
            'unknown' => array()
        );
        
        $path = $this->statusPath . '/' . $this->index . '.rrd';
        if (false === file_exists($path)) {
            throw new RuntimeException();
        }
        $commandLine .= " DEF:v1=" . $path . ":value:AVERAGE";
        $commandLine .= " XPORT:v1:status";

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'a'),
        );

        $process = proc_open($this->generalOpt["rrdtool_path_bin"] . " - ", $descriptorspec, $pipes, null, null);
        if (false === is_resource($process)) {
            throw new RuntimeException();
        }
        fwrite($pipes[0], $commandLine);
        fclose($pipes[0]);

        do {
            $status = proc_get_status($process);
        } while ($status['running']);

        $str = stream_get_contents($pipes[1]);

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
        $lastStatus = null;
        $interval = array();
        foreach ($rows as $row) {
            $time = null;
            $i = 0;
            foreach ($row->children() as $info) {
                if (is_null($time)) {
                    $time = (string)$info;
                } else {
                    $currentStatus;
                    $value = floatval((string)$info);
                    if ((string)$info === 'NaN') {
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
     * @return bool
     */
    public function flushRrdCached($indexData)
    {
        if (false === isset($this->generalOpt['rrdcached_enabled']) ||
           $this->generalOpt['rrdcached_enabled'] == 0) {
            return true;
        }
        
        /*
         * Connect to rrdcached
         */
        $errno = 0;
        $errstr = '';
        if (isset($this->general_opt['rrdcached_port'])
            && trim($this->general_opt['rrdcached_port']) != '') {
            $sock = @fsockopen('127.0.0.1', trim($this->general_opt['rrdcached_port']), $errno, $errstr);
            if ($sock === false) {
                return false;
            }
        } elseif (isset($this->general_opt['rrdcached_unix_path'])
            && trim($this->general_opt['rrdcached_unix_path']) != '') {
            $sock = @fsockopen('unix://' . trim($this->general_opt['rrdcached_unix_path']), $errno, $errstr);
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
            $fullpath = realpath($this->statusPath . $this->index . '.rrd');
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
     * Get general options
     *
     * @return array The list of genreal options
     */
    protected function getOptions()
    {
        $result = array();
        $query = 'SELECT `key`, `value` FROM options
            WHERE `key` IN ("rrdtool_path_bin", "rrdcached_enabled")';
        $res = $this->pearDB->query($query);
        if (PEAR::isError($res)) {
            throw new RuntimeException();
        }
        while ($row = $res->fetchRow()) {
            $result[$row['key']] = $row['value'];
        }
        return $result;
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
        $row = $res->fetchRow();
        if ($row === null) {
            throw new RuntimeException('Missing status directory configuration');
        }
        return $row['RRDdatabase_status_path'];
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
