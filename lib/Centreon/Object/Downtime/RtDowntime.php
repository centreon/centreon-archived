<?php
/*
 * Copyright 2005-2017 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once "Centreon/Object/ObjectRt.php";

/**
 * Used for interacting with downtime objects
 *
 * @author Baldo Guillaume
 */
class Centreon_Object_RtDowntime extends Centreon_ObjectRt
{
    protected $table = "downtimes";
    protected $name = "downtime_name";
    protected $primaryKey = "downtime_id";
    protected $uniqueLabelField = "comment_data";

    /**
     * @param array $hostList
     * @return array
     */
    public function getHostDowntimes($hostList = array())
    {
        $hostFilter = '';

        if (!empty($hostList)) {
            $hostFilter = "AND h.name IN ('" . implode("','", $hostList) . "') ";
        }

        $query = "SELECT downtime_id, name, author, actual_start_time , actual_end_time, " .
            "start_time, end_time, comment_data, duration, fixed " .
            "FROM downtimes d, hosts h " .
            "WHERE d.host_id = h.host_id " .
            "AND d.cancelled = 0 " .
            "AND type = 2 " .
            "AND end_time > UNIX_TIMESTAMP(NOW()) " .
            $hostFilter .
            "ORDER BY actual_start_time, name";

        return $this->getResult($query);
    }

    /**
     * @param array $svcList
     * @return array
     */
    public function getSvcDowntimes($svcList = array())
    {
        $serviceFilter = '';

        if (!empty($svcList)) {
            $serviceFilter = 'AND (';
            $filterTab = array();
            for ($i = 0; $i < count($svcList); $i += 2) {
                $hostname = $svcList[$i];
                $serviceDescription = $svcList[$i + 1];
                $filterTab[] = '(h.name = "' . $hostname . '" AND s.description = "' . $serviceDescription . '")';
            }
            $serviceFilter .= implode(' AND ', $filterTab) . ') ';
        }

        $query = "SELECT d.downtime_id, h.name, s.description, author, actual_start_time, actual_end_time, " .
            "start_time, end_time, comment_data, duration, fixed " .
            "FROM downtimes d, hosts h, services s " .
            "WHERE d.service_id = s.service_id " .
            "AND d.host_id = s.host_id " .
            "AND s.host_id = h.host_id " .
            "AND d.cancelled = 0 " .
            "AND d.type = 1 " .
            "AND end_time > UNIX_TIMESTAMP(NOW()) " .
            $serviceFilter .
            "ORDER BY actual_start_time, h.name, s.description";

        return $this->getResult($query);
    }

    /**
     * @param $id
     * @return array
     */
    public function getCurrentDowntime($id)
    {
        $query = "SELECT * FROM downtimes WHERE ISNULL(actual_end_time) " .
            " AND end_time > " . time() . " AND downtime_id = " . $id;
        return $this->getResult($query, array(), 'fetch');
    }
}
