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

use CentreonConfiguration\Repository\HostRepository as HostConfigurationRepository;
use CentreonConfiguration\Repository\ServiceRepository as ServiceConfigurationRepository;
use CentreonRealtime\Models\Service as ServiceRealtime;
use Centreon\Internal\Utils\Datetime;
use Centreon\Internal\Di;

/**
 * @author Sylvestre Ho <sho@merethis.com>
 * @package CentreonRealtime
 * @subpackage Repository
 */
class ServiceRepository extends \CentreonRealtime\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'rt_services';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Service';

    /**
     *
     * @var string
     */
    public static $objectId = 'service_id';

    /**
     *
     * @var string
     */
    public static $hook = 'displayServiceRtColumn';
    
    /**
     * Get service status
     *
     * @param int $host_id
     * @param int $service_id
     * @return mixed
     */
    public static function getStatus($host_id, $service_id)
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->prepare(
            'SELECT last_hard_state as state 
            FROM rt_services 
            WHERE service_id = ? 
            AND host_id = ? 
            AND enabled = 1 
            LIMIT 1'
        );
        $stmt->execute(array($service_id, $host_id));
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['state'];
        }
        return -1;
    }

    /**
     * Format small badge status
     *
     * @param int $status
     * @return string
     */
    public static function getStatusBadge($status)
    {
        switch ($status) {
            case 0:
                $status = "label-success";
                break;
            case 1:
                $status = "label-warning";
                break;
            case 2:
                $status = "label-danger";
                break;
            case 3:
                $status = "label-default";
                break;
            case 4:
                $status = "label-info";
                break;
            default:
                $status = "";
                break;
        }
        return "<span class='label $status pull-right overlay'>&nbsp;</span>";
    }
    
    /**
     * 
     * @param int $hostId
     * @param string $domain
     * @return array
     */
    public static function getServicesByDomainForHost($hostId, $domain)
    {
        static $servicesList = array();

        if (!isset($serviceList[$hostId])) {
            $serviceList[$hostId] = array();

            $db = Di::getDefault()->get('db_centreon');
            $query = "SELECT service_id, value as domain 
                FROM rt_customvariables 
                WHERE name = 'CENTREON_DOMAIN'
                AND host_id = :host";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':host', $hostId, \PDO::PARAM_INT);
            $stmt->execute();
            $servicesIdList = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($servicesIdList as $service) {
                $domain = $service['domain'];
                if (!isset($serviceList[$hostId][$domain])) {
                    $serviceList[$hostId][$domain] = array();
                }
                $servicesList[$hostId][$domain][] = ServiceRealtime::get($service['service_id']);
            }
        }

        if (isset($serviceList[$hostId][$domain])) {
            return $serviceList[$hostId][$domain];
        }
        return array();
    }
}
