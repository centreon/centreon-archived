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

	include_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";

	/** ************************************
	 * start init db
	 */
	$pearDB = new CentreonDB();

	/** ************************************
	 * start XML Flow
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("services");

	$empty = 0;
	if (isset($_POST["host_id"])){
		$traps = array();
		if ($_POST["host_id"] == -1) {
			$DBRESULT = $pearDB->query("SELECT s.service_id, s.service_description, h.host_name, h.host_id FROM service s, host h, host_service_relation hsr WHERE h.host_id = hsr.host_host_id AND s.service_id = hsr.service_service_id ORDER BY h.host_name, s.service_description");
		} else if ($_POST["host_id"] == -2) {
			$empty = 1;
		} else if ($_POST["host_id"] != 0) {
			$DBRESULT = $pearDB->query("SELECT s.service_id, s.service_description, h.host_name, h.host_id FROM service s, host h, host_service_relation hsr WHERE h.host_id = " . $_POST["host_id"]. " AND h.host_id = hsr.host_host_id AND s.service_id = hsr.service_service_id ORDER BY h.host_name, s.service_description");
		}

		if ($empty != 1) {
			while ($service = $DBRESULT->fetchRow()){
				$buffer->startElement("service");
				$buffer->writeElement("id", $service["host_id"] . "-" . $service['service_id']);
				$buffer->writeElement("name", $service["host_name"] . " - " . $service['service_description'], false);
				$buffer->endElement();
			}
			$DBRESULT->free();
		}
	} else {
		$buffer->writeElement("error", "host_id not found");
	}
	$buffer->endElement();
	header('Content-Type: text/xml');
	$buffer->output();
?>