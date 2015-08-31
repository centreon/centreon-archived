<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');

	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";
	require_once $centreon_path."/www/class/centreonXML.class.php";

	function compare($a, $b) {
		if ( $a["metric_name"] == $b["metric_name"] )
			return 0;
		return ( $a["metric_name"] < $b["metric_name"] ) ? -1 : 1;
	}

	$pearDB = new CentreonDB();
	$pearDBO = new CentreonDB("centstorage");

        /*
	 * Get session
	 */
	require_once ($centreon_path . "www/class/centreonSession.class.php");
	require_once ($centreon_path . "www/class/centreon.class.php");
	if(!isset($_SESSION['centreon'])) {
		CentreonSession::start();
	}

	if (isset($_SESSION['centreon'])) {
            $oreon = $_SESSION['centreon']; 
	} else {
            exit;
	}
        
	/*
	 * Get language 
	 */
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages",  $centreon_path . "www/locale/");;
	bind_textdomain_codeset("messages", "UTF-8"); 
	textdomain("messages");
        
	#
	# Existing Real Metric List comes from DBO -> Store in $rmetrics Array
	#
	$s_datas = array();
	$o_datas = array(""=> utf8_decode(sprintf("%s%s", _("List of known metrics"), "&nbsp;&nbsp;&nbsp;")));
	$mx_l = strlen($o_datas[""]);
	$where = "";
	$def_type = array(0=>"CDEF",1=>"VDEF");

	if (isset($_GET["vdef"]) && $_GET["vdef"] == 0)
		$where = " AND def_type='".$_GET["vdef"]."'";

	if (isset($_GET["index_id"]) && $_GET["index_id"] != 0) {
		$pq_sql = $pearDBO->query("SELECT metric_id, metric_name FROM metrics as ms, index_data as ixd WHERE ms.index_id = ixd.id and ms.index_id='".$pearDB->escape($_GET["index_id"])."';");
		while($fw_sql = $pq_sql->fetchRow()) {
			$sd_l = strlen($fw_sql["metric_name"]);
			$fw_sql["metric_name"] = $fw_sql["metric_name"]."&nbsp;&nbsp;&nbsp;";
			$s_datas[] = $fw_sql;
			if ( $sd_l > $mx_l )
				$mx_l = $sd_l;
    	}
		$pq_sql->free();
		$pq_sql = $pearDB->query("SELECT vmetric_id, vmetric_name, def_type FROM virtual_metrics WHERE index_id='".$pearDB->escape($_GET["index_id"])."'".$where.";");

		while($fw_sql = $pq_sql->fetchRow()) {
			$sd_l = strlen($fw_sql["vmetric_name"]." [CDEF]");
			$fw_sql["metric_name"] = $fw_sql["vmetric_name"]." [".$def_type[$fw_sql["def_type"]]."]&nbsp;&nbsp;&nbsp;";
			$fw_sql["metric_id"] = "v".$fw_sql["vmetric_id"];
			$s_datas[] = $fw_sql;
			if ( $sd_l > $mx_l )
				$mx_l = $sd_l;
    	}
		$pq_sql->free();
	}

	usort($s_datas, "compare");

	foreach ($s_datas as $key => $om) {
		$o_datas[$om["metric_id"]] = $om["metric_name"];
	}

	for ($i = strlen($o_datas[""]); $i != $mx_l; $i++)
		$o_datas[""] .= "&nbsp;";

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
