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

require_once realpath(dirname(__FILE__) . '/../../../../config/centreon.config.php');

require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonACL.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonLog.class.php';
require_once _CENTREON_PATH_ . '/www/class/centreonUser.class.php';
require_once _CENTREON_PATH_ . '/www/include/common/common-Func.php';

session_start();
session_write_close();

/* Initialize database connection */
$pearDB = new CentreonDB();
$pearDBO = new CentreonDB('centstorage');

/* Load session */
$centreon = $_SESSION['centreon'];

/* Validate session and get contact */
$sid = session_id();
$contactId = check_session($sid, $pearDB);
$isAdmin = isUserAdmin($sid);

$access = new CentreonACL($contactId, $isAdmin);

$lca = $access->getHostsServices($pearDBO);

/* Build list of services */
$servicesReturn = array();

/**
 * Get the list of graph by host
 *
 * Apply ACL and if the service has a graph
 *
 * @param int $host The host ID
 * @param bool $isAdmin If the contact is admin
 * @param array $lca The ACL of the contact
 */
function getServiceGraphByHost($host, $isAdmin, $lca)
{
    $listGraph = array();
    if ($isAdmin || (!$isAdmin && isset($lca[$host]))) {
        $services =  getMyHostServices($host);
        foreach ($services as $svcId => $svcName) {
            $svcGraph = getGraphByService($host, $svcId, $svcName, $isAdmin, $lca);
            if ($svcGraph !== false) {
                $listGraph[] = $svcGraph;
            }
        }
    }
    return $listGraph;
}

/**
 * Get the graph of a service
 *
 * Apply ACL and if the service has a graph
 *
 * @param int $host The host ID
 * @param int $svcId The service ID
 * @param string $svcName The service name
 * @param bool $isAdmin If the contact is admin
 * @param array $lca The ACL of the contact
 */
function getGraphByService($host, $svcId, $title, $isAdmin, $lca)
{
    if (service_has_graph($host, $svcId) && ($isAdmin || (!$isAdmin && isset($lca[$host][$svcId])))) {
        return array(
            'type' => 'service',
            'id' => $host . '_' . $svcId,
            'title' => $title
        );
    }
    return false;
}

/* By hostgroups */
if (isset($_POST['host_group_filter'])) {
    foreach ($_POST['host_group_filter'] as $hgId) {
        $hosts = getMyHostGroupHosts($hgId);
        foreach ($hosts as $host) {
            $servicesReturn = array_merge($servicesReturn, getServiceGraphByHost($host, $isAdmin, $lca));
        }
    }
}
/* By hosts */
if (isset($_POST['host_selector'])) {
    foreach ($_POST['host_selector'] as $host) {
        $servicesReturn = array_merge($servicesReturn, getServiceGraphByHost($host, $isAdmin, $lca));
    }
}

/* By servicegroups */
if (isset($_POST['service_group_filter'])) {
    foreach ($_POST['service_group_filter'] as $sgId) {
        $services = getMyServiceGroupServices($sgId);
        foreach ($services as $hostSvcId => $svcName) {
            list($hostId, $svcId) = explode('_', $hostSvcId);
            $servicesReturn[] = getGraphByService($hostId, $svcId, $svcName, $isAdmin, $lca);
        }
    }
}

/* By service */
if (isset($_POST['service_selector'])) {
    foreach ($_POST['service_selector'] as $selectedService) {
        list($hostId, $svcId) = explode('-', $selectedService['id']);
        $svcGraph = getGraphByService($hostId, $svcId, $selectedService['text'], $isAdmin, $lca);
        if ($svcGraph !== false) {
            $servicesReturn[] = $svcGraph;
        }
    }
}

/* By metaservice */
// @todo

header('Content-type: application/json');
print json_encode(array_unique($servicesReturn, SORT_REGULAR));
