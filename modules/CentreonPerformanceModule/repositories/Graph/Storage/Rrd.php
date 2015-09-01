<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace CentreonPerformance\Repository\Graph\Storage;

/**
 * Storage Graph for RRD
 *
 * @author Maximilien Bersoult <mbersoult@centreon.com>
 * @version 3.0.0
 * @package Centreon
 */
class Rrd extends AbstractStorage
{
    private $rrdPath = null;
    private $rrdStatusPath = null;

    /**
     * Constructor
     * @todo use global options
     */
    public function __construct()
    {
        /*$di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        /* Get the path for rrd metrics and status *
        $query = "SELECT RRDdatabase_path, RRDdatabase_status_path FROM rt_configs WHERE id = 1";
        $stmt = $dbconn->query($query);
        $row = $stmt->fetch();
        $this->rrdPath = $row['RRDdatabase_path'];
        $this->rrdStatusPath = $row['RRDdatabase_status_path'];*/
        
        $this->rrdPath = '/var/lib/centreon/metrics/';
        $this->rrdStatusPath = '/var/lib/centreon/status/';

        parent::__construct();
    }

    /**
     * Get the values of a metric
     *
     * @param int $metricId The metric id
     * @return array
     */
    public function getValues($metricId, $rows = 100)
    {
        $rrdFile = $this->rrdPath . '/' . $metricId . '.rrd';
        if (file_exists($rrdFile)) {
            $options = array(
                '--start', $this->startTime,
                '--end', $this->endTime,
                '--maxrows', $rows,
                'DEF:metric=' . $rrdFile . ':value:AVERAGE',
                'XPORT:metric:"Values"'
            );

            $values = rrd_xport($options);
            if (false === $values) {
                throw new \Exception("Error when getting metric values");
            }

            return $values['data'][0]['data'];
        } else {
            throw new \Exception("rrd file " . $rrdFile . " doesn't exist");
        }
    }
    
    public function getSpecificValues($metricId)
    {
        $rrdFile = $this->rrdPath . '/' . $metricId . '.rrd';
        if (file_exists($rrdFile)) {
            $values = rrd_fetch(
                $rrdFile,
                array("AVERAGE", "--resolution", "300", "--start", "now", "--end", "start-1h" )
            );
        } else {
            throw new \Exception("rrd file " . $rrdFile . " doesn't exist");
        }
        
        return $values;
    }
}
