<?php
/*
 * Copyright 2005-2015 Centreon
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
 *
 */

require_once realpath(dirname(__FILE__) . "/../../../../../config/centreon.config.php");

/*
 * Include Classes
 */
require_once _CENTREON_PATH_ . "www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonUser.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonSession.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonACL.class.php";

session_start();
session_write_close();

/*
 * Check Sessions
 */
if (!isset($_SESSION['centreon']) || !isset($_POST['host_id'])) {
    exit;
}

/*
 * Get Params
 */
$centreon = $_SESSION['centreon'];
$acl = $centreon->user->access;
$hostId = $_POST['host_id'];

/*
 * Init DB Object
 */
$db = new CentreonDB();
$pearDB = $db;

$aclFrom = "";
$aclCond = "";
if (!$centreon->user->admin) {
    $dbmon = $acl->getNameDBAcl('broker');
    $aclFrom = ", $dbmon.centreon_acl acl ";
    $aclCond = " WHERE res.host_id = acl.host_id 
                 AND acl.service_id = res.service_id 
                 AND acl.group_id IN (".$acl->getAccessGroupsString().") ";
}

/*
 * Start XML
 */
$xml = new CentreonXML();
$xml->startElement("response");

if (isset($hostId)) {
    if ($hostId == 0) {
        $query = "SELECT DISTINCT res.service_id, res.service_description, res.host_name, res.host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id IS NULL 
                    AND h.host_id = hsr.host_host_id 
					AND s.service_id = hsr.service_service_id 
					AND s.service_register = '1'
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, hostgroup_relation hgr, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id = hgr.hostgroup_hg_id 
                    AND hgr.host_host_id = h.host_id 
					AND s.service_id = hsr.service_service_id 
					AND s.service_register = '1'
				) AS res $aclFrom $aclCond
				ORDER BY res.host_name, res.service_description";
    } else {
        $query = "SELECT DISTINCT res.service_id, res.service_description, res.host_name, res.host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.hostgroup_hg_id IS NULL 
                                        AND h.host_id = '" . $db->escape($hostId). "' 
                                        AND h.host_id = hsr.host_host_id 
                                        AND s.service_id = hsr.service_service_id 
                                        AND s.service_register = '1' 
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE hsr.host_host_id IS NULL 
                                        AND hsr.hostgroup_hg_id IN (SELECT hostgroup_hg_id 
                                                                    FROM hostgroup_relation 
                                                                    WHERE host_host_id = '" . $db->escape($hostId). "') 
                                        AND h.host_id = '" . $db->escape($hostId). "' 
                                        AND s.service_id = hsr.service_service_id 
                                        AND s.service_register = '1' 
                                ) AS res $aclFrom $aclCond
				ORDER BY res.host_name, res.service_description";
    }
    $res = $db->query($query);
    while ($row = $res->fetchRow()) {
        $xml->startElement("services");
        $xml->writeElement("id", $row['host_id']."_".$row['service_id']);
        $xml->writeElement("description", $row['host_name'] . " - " . $row['service_description']);
        $xml->endElement();
    }
}
$xml->endElement();

header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');

$xml->output();
