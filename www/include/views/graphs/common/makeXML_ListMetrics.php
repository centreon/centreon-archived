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
 * File makeXML_ListMetrics.php D.Porte
 *
 */

	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');

	require_once "@CENTREON_ETC@//centreon.conf.php";
	//require_once "/etc/centreon/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";
	require_once $centreon_path."/www/class/centreonXML.class.php";

	function compare($a, $b) {
		if ( $a["metric_name"] == $b["metric_name"] )
			return 0;
		return ( $a["metric_name"] < $b["metric_name"] ) ? -1 : 1;
	}

	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");

	# replace array
	$a_this = array( "#S#", "#BS#" );
	$a_that = array( "/", "\\" );

	#
	# Existing Real Metric List comes from DBO -> Store in $rmetrics Array
	#
	$s_datas = array();
	$o_datas = array(""=>"&nbsp;");
	$where = "";
	$def_type = array(0=>"CDEF",1=>"VDEF");

	if (isset($_GET["vdef"]) && $_GET["vdef"] == 0)
		$where = " AND def_type='".$_GET["vdef"]."'";

	if (isset($_GET["index_id"]) && $_GET["index_id"] != 0) {
		$pq_sql = $pearDBO->query("SELECT metric_id, metric_name FROM metrics as ms, index_data as ixd WHERE ms.index_id = ixd.id and ms.index_id='".$_GET["index_id"]."';");
		while($fw_sql = $pq_sql->fetchRow()) {
			$fw_sql["metric_name"] = str_replace($a_this, $a_that, $fw_sql["metric_name"]);
			$s_datas[] = $fw_sql;
		}
		$pq_sql->free();
		$pq_sql = $pearDB->query("SELECT vmetric_id, vmetric_name, def_type FROM virtual_metrics WHERE index_id='".$_GET["index_id"]."'".$where.";");

		while($fw_sql = $pq_sql->fetchRow()) {
			$fw_sql["metric_name"] = $fw_sql["vmetric_name"]." [".$def_type[$fw_sql["def_type"]]."]";
			$fw_sql["metric_id"] = "v".$fw_sql["vmetric_id"];
			$s_datas[] = $fw_sql;
		}
		$pq_sql->free();
	}

	usort($s_datas, "compare");

	foreach ($s_datas as $key => $om) {
		$o_datas[$om["metric_id"]] = $om["metric_name"];
	}


	/*
	 *  The first element of the select is empty
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("options_data");
	$buffer->writeElement("td_id", "td_list_metrics");
	$buffer->writeElement("select_id", "sl_list_metrics");

	/*
	 *  Now we fill out the select with templates id and names
	 */
	foreach ($o_datas as $o_id => $o_alias){
		$buffer->startElement("option");
		$buffer->writeElement("o_id", $o_id);
		$buffer->writeElement("o_alias", $o_alias);
		$buffer->endElement();
	}
	$buffer->endElement();
	$buffer->output();
?>
