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

	$pearDBO = new CentreonDB("centstorage");

	/*
	 * Get language
	 */
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages",  $centreon_path . "www/locale/");;
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");

	# replace array
	$a_this = array( "#S#", "#BS#" );
	$a_that = array( "/", "\\" );

	#
	# Existing services data comes from DBO -> Store in $s_datas Array
	$s_datas = array(""=> sprintf("%s%s", _("Service list"), "&nbsp;&nbsp;&nbsp;"));
	$mx_l = strlen($s_datas[""]);

	if (isset($_GET["host_id"]) && $_GET["host_id"] != 0) {
		$pq_sql = $pearDBO->query("SELECT id index_id, service_description FROM index_data WHERE host_id='".$_GET['host_id']."'ORDER BY service_description");
		while($fw_sql = $pq_sql->fetchRow()) {
			$fw_sql["service_description"] = str_replace($a_this, $a_that, $fw_sql["service_description"]);
			$s_datas[$fw_sql["index_id"]] = $fw_sql["service_description"]."&nbsp;&nbsp;&nbsp;";
			$sd_l = strlen($fw_sql["service_description"]);
			if ( $sd_l > $mx_l)
				$mx_l = $sd_l;
    	}
		$pq_sql->free();
	}
    for ($i = strlen($s_datas[""]); $i != $mx_l; $i++)
		$s_datas[""] .= "&nbsp;";

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
