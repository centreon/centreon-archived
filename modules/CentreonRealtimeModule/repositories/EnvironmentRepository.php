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
 */

namespace CentreonRealtime\Repository;

use Centreon\Internal\Di;

/**
 * Repository for environment status
 *
 * @author Sylvestre Ho <sho@centreon.com>
 * @version 3.0.0
 */
class EnvironmentRepository
{
    /**
     * Get host group alerts by environment
     * 
     * @return array
     */
    public static function getHostgroupAlerts()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare(
            "SELECT COUNT(service_id) as alerts, hg.hostgroup_id, hg.name
            FROM rt_services s, rt_hosts_hostgroups hhg, rt_hostgroups hg
            WHERE s.host_id = hhg.host_id
            AND hhg.hostgroup_id = hg.hostgroup_id
            AND s.state != 0
            AND s.state_type = 1
            AND s.acknowledged = 0
            AND s.scheduled_downtime_depth = 0
            AND s.enabled = 1
            GROUP BY hg.name"
        );
        $stmt->execute();
        $results = array();
        while ($row = $stmt->fetch()) {
            $results[$row['hostgroup_id']] = $row['alerts'];
        }
        return $results;
    }

    /**
     * Get service group alerts by environment
     *
     * @return array
     */
    public static function getServicegroupAlerts()
    {
        $db = Di::getDefault()->get('db_centreon');
        $stmt = $db->prepare(
            "SELECT COUNT(s.service_id) as alerts, sg.servicegroup_id, sg.name
            FROM rt_services s, rt_services_servicegroups ssg, rt_servicegroups sg
            WHERE s.service_id = ssg.service_id
            AND ssg.servicegroup_id = sg.servicegroup_id
            AND s.state != 0
            AND s.state_type = 1
            AND s.acknowledged = 0
            AND s.scheduled_downtime_depth = 0
            AND s.enabled = 1
            GROUP BY sg.name"
        );
        $stmt->execute();
        $results = array();
        while ($row = $stmt->fetch()) {
            $results[$row['servicegroup_id']] = $row['alerts'];
        }
        return $results;
    }
}
