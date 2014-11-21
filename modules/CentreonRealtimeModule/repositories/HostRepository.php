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

use \Centreon\Internal\Utils\Datetime;
use \Centreon\Internal\Utils\Status as UtilStatus;

/**
 * @author Julien Mathis <jmathis@merethis.com>
 * @package CentreonRealtime
 * @subpackage Repository
 */
class HostRepository extends \CentreonRealtime\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $tableName = 'rt_hosts';
    
    /**
     *
     * @var string
     */
    public static $objectName = 'Host';

    /**
     *
     * @var string
     */
    public static $objectId = 'host_id';

    /**
     *
     * @var string
     */
    public static $hook = 'displayHostRtColumn';
    
    /**
     * Get service status
     *
     * @param int $hostId
     * @return mixed
     */
    public static function getStatus($hostId)
    {
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');

        $stmt = $dbconn->prepare('SELECT state as state FROM rt_hosts WHERE host_id = ? AND enabled = 1 LIMIT 1');
        $stmt->execute(array($hostId));

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $row['state'];
        }
        return -1;
    }
    
    /**
     * 
     * @param type $hostId
     * @return type
     */
    public static function getHostShortInfo($hostId)
    {
        $finalInfo = array();
        
        // Initializing connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        $stmt = $dbconn->prepare('SELECT '
            . 'state as state, output as output, last_state_change as lastChange, '
            . 'last_check as lastCheck, next_check as nextCheck '
            . 'FROM rt_hosts WHERE host_id = ? LIMIT 1');
        $stmt->execute(array($hostId));
        
        $infos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (count($infos) > 0) {
            $finalInfo['status'] = strtolower(UtilStatus::numToString($infos[0]['state'], UtilStatus::TYPE_HOST));
            $finalInfo['output'] = $infos[0]['output'];
            $finalInfo['lastChange'] = $infos[0]['lastChange'];
            $finalInfo['lastCheck'] = $infos[0]['lastCheck'];
            $finalInfo['nextCheck'] = $infos[0]['nextCheck'];
        } else {
            $finalInfo['status'] = -1;
            $finalInfo['output'] = '';
            $finalInfo['lastChange'] = '';
            $finalInfo['lastCheck'] = '';
            $finalInfo['nextCheck'] = '';
        }
        
        
        return $finalInfo;
    }

    /**
     * Format small badge status
     *
     * @param int $status
     */
    public static function getStatusBadge($status)
    {
        switch ($status) {
            case 0:
                $status = "label-success";
                break;
            case 1:
                $status = "label-danger";
                break;
            case 2:
                $status = "label-default";
                break;
            default:
                $status = "";
                break;
        }
        return "<span class='label $status pull-right overlay'>&nbsp;"
            . "<!--<i class='fa fa-check-square-o'>--></i></span>";
    }
}
