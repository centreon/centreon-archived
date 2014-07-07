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

namespace CentreonConfiguration\Repository;

/**
 * @author Lionel Assepo <lassepo@merethis.com>
 * @package Centreon
 * @subpackage Repository
 */
class PollerRepository extends \CentreonConfiguration\Repository\Repository
{
    /**
     *
     * @var string
     */
    public static $objectName = 'Poller';

    /**
     *
     * Check if a service or an host has been
     * changed for a specific poller.
     * @param unknown_type $poller_id
     * @param unknown_type $last_restart
     * @return number
     */
    public static function checkChangeState($poller_id, $last_restart)
    {
        if (!isset($last_restart) || $last_restart == "") {
            return 0;
        }

        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconnStorage = $di->get('db_storage');

        $request = "SELECT *
            FROM log_action
            WHERE
                action_log_date > $last_restart AND
                ((object_type = 'host' AND
                object_id IN (
                    SELECT host_host_id
                        FROM centreon.ns_host_relation
                        WHERE nagios_server_id = '$poller_id'
                )) OR
                    (object_type = 'service') AND
                        object_id IN (
                    SELECT service_service_id
                    FROM centreon.ns_host_relation nhr, centreon.host_service_relation hsr
                    WHERE nagios_server_id = '$poller_id' AND hsr.host_host_id = nhr.host_host_id
        ))";
        $DBRESULT = $dbconnStorage->query($request);
        if ($DBRESULT->rowCount()) {
            return 1;
        }
        return 0;
    }
    
    /**
     * 
     * @param array $params
     * @return integer
     */
    public static function getTotalRecordsForDatatable($params)
    {
        // Get centreon DB and centreon storage DB connection
        $di = \Centreon\Internal\Di::getDefault();
        $dbconn = $di->get('db_centreon');
        
        //
        $sqlCalNagiosServer = "SELECT COUNT(`id`) as nb_poller FROM `nagios_server`";
        $stmtCalNagiosServer = $dbconn->query($sqlCalNagiosServer);
        $resultCalNagiosServer = $stmtCalNagiosServer->fetchAll(\PDO::FETCH_ASSOC);
        
        return $resultCalNagiosServer[0]['nb_poller'];
    }
}
