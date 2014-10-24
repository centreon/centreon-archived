<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace CentreonPerformance\Repository\Graph\Storage;

/**
 * Storage Graph for RRD
 *
 * @author Maximilien Bersoult <mbersoult@merethis.com>
 * @version 3.0.0
 * @package Centreon
 */
class Rrd extends AbstractStorage
{
    private $rrdPath = null;
    private $rrdStatusPath = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        /* Get the path for rrd metrics and status */
        $query = "SELECT RRDdatabase_path, RRDdatabase_status_path FROM config WHERE id = 1";
        $stmt = $dbconn->query($query);
        $row = $stmt->fetch();
        $this->rrdPath = $row['RRDdatabase_path'];
        $this->rrdStatusPath = $row['RRDdatabase_status_path'];

        parent::__construct();
    }

    /**
     * Get the values of a metric
     *
     * @param int $metricId The metric id
     * @return array
     */
    public function getValues($metricId)
    {
        $options = array(
            '--start', $this->startTime,
            '--end', $this->endTime,
            '--maxrows', 100,
            'DEF:metric=' . $this->rrdPath . '/' . $metricId . '.rrd:value:AVERAGE',
            'XPORT:metric:"Values"'
        );
        $values = rrd_xport($options);
        if (false === $values) {
            throw new \Exception("Error when getting metric values");
        }
        return $values['data'][0]['data'];
    }
}
