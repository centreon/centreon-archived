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

/**
 * Repository for service data
 *
 * @author Sylvestre Ho <sho@merethis.com>
 * @version 3.0.0
 */
class ServicedetailRepository
{
    /**
     * Get real time data of a service
     * 
     * @param int $serviceId
     * @return array
     */
    public static function getRealtimeData($serviceId)
    {
        $db = \Centreon\Internal\Di::getDefault()->get('db_storage');
        $sql = 'SELECT h.name as host_name, s.acknowledged, s.scheduled_downtime_depth, s.output, s.latency,
            s.last_check, s.next_check, s.check_period, i.name as instance_name, s.state, 
            s.description as service_description, s.state_type, s.perfdata
            FROM hosts h, services s, instances i
            WHERE i.instance_id = h.instance_id
            AND h.host_id = s.host_id
            AND s.enabled = 1
            AND s.service_id = ?';
        $stmt = $db->prepare($sql);
        $stmt->execute(array($serviceId));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

