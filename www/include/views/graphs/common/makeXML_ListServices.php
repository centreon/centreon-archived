<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 *
 * File makeXML_ListServices.php D.Porte
 *
 */
 
	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');
	
	require_once "/etc/centreon/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";	
	require_once $centreon_path."/www/class/centreonXML.class.php";
		
	$pearDBO = new CentreonDB("centstorage");

	# replace array
	$a_this = array( "#S#", "#BS#" );
	$a_that = array( "/", "\\" );
	
	#
	# Existing services data comes from DBO -> Store in $s_datas Array
	$s_datas = array(""=>"&nbsp;");
	$mx_l = strlen($s_datas[""]);

	if (isset($_GET["host_id"]) && $_GET["host_id"] != 0) {
		$pq_sql =& $pearDBO->query("SELECT id index_id, service_description FROM index_data WHERE host_id='".$_GET['host_id']."'ORDER BY service_description");
		while($fw_sql = $pq_sql->fetchRow()) {
			$fw_sql["service_description"] = str_replace($a_this, $a_that, $fw_sql["service_description"]);
			$s_datas[$fw_sql["index_id"]] = $fw_sql["service_description"];
		}
		$pq_sql->free();
	}
	
	/*
	 *  The first element of the select is empty
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("options_data");
	$buffer->writeElement("td_id", "td_list_hsr");	
	$buffer->writeElement("select_id", "sl_list_services");	
	
	/*
	 *  Now we fill out the select with templates id and names
	 */
	foreach ($s_datas as $o_id => $o_alias){
		$buffer->startElement("option");
		$buffer->writeElement("o_id", $o_id);
		$buffer->writeElement("o_alias", $o_alias);
		$buffer->endElement();				
	}
	$buffer->endElement();
	$buffer->output();
?>
