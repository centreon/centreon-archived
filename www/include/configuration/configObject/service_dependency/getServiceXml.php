<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

require_once "@CENTREON_ETC@/centreon.conf.php";

/*
 * Include Classes
 */
require_once $centreon_path . "www/class/centreon.class.php";
require_once $centreon_path . "www/class/centreonSession.class.php";
require_once $centreon_path . "www/class/centreonXML.class.php";
require_once $centreon_path . "www/class/centreonDB.class.php";

session_start();

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
$hostId = $_POST['host_id'];

/*
 * Init DB Object
 */
$db = new CentreonDB();

/*
 * Start XML
 */
$xml = new CentreonXML();
$xml->startElement("response");

if (isset($hostId)) {
	if ($hostId == 0) {
		$query = "SELECT service_id, service_description, host_name, host_id FROM (
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, host h, host_service_relation hsr 
					WHERE 
						hsr.hostgroup_hg_id IS NULL AND 
						h.host_id = hsr.host_host_id AND 
						s.service_id = hsr.service_service_id 
					UNION 
					SELECT s.service_id, s.service_description, h.host_name, h.host_id 
					FROM service s, hostgroup_relation hgr, host h, host_service_relation hsr 
					WHERE 
						hsr.hostgroup_hg_id = hgr.hostgroup_hg_id AND
						hgr.host_host_id = h.host_id AND
						s.service_id = hsr.service_service_id 
				) AS res
				ORDER BY res.host_name, res.service_description";
	} else {
		$query = "SELECT service_id, service_description, host_name, host_id FROM (
							SELECT s.service_id, s.service_description, h.host_name, h.host_id 
							FROM service s, host h, host_service_relation hsr 
							WHERE 
								hsr.hostgroup_hg_id IS NULL AND 
								h.host_id = '" . $db->escape($hostId). "' AND 
								h.host_id = hsr.host_host_id AND 
								s.service_id = hsr.service_service_id AND 
								s.service_register = '1' 
							UNION 
							SELECT s.service_id, s.service_description, h.host_name, h.host_id 
							FROM service s, host h, host_service_relation hsr 
							WHERE 
								hsr.host_host_id IS NULL AND 
								hsr.hostgroup_hg_id IN (SELECT hostgroup_hg_id FROM hostgroup_relation WHERE host_host_id = '" . $db->escape($hostId). "') AND 
								h.host_id = '" . $db->escape($hostId). "' AND
								s.service_id = hsr.service_service_id AND 
								s.service_register = '1' 
						) AS res
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

?>