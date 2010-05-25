<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	/*
	 * if debug == 0 => Normal, 
	 * debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';
	
	//include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once "/etc/centreon/centreon.conf.php";
	
	include_once $centreon_path . "www/class/centreonSession.class.php";
	include_once $centreon_path . "www/class/centreon.class.php";	
	include_once $centreon_path . "www/class/centreonDuration.class.php";
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";
			
	CentreonSession::start();
	$oreon =& $_SESSION["oreon"];
	
	$pearDB 	= new CentreonDB();
	$pearDBndo 	= new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	
	/* 
	 * security check 2/2
	 */
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');
	
	/*
	 * Get Acl Group list
	 */
	$is_admin = isUserAdmin($sid);
	$user_id = getUserIdFromSID($sid);
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups(); 
	$groupnumber = count($grouplist);
	
	$groupStr = "";
	foreach ($grouplist as $key => $value) {
		if ($groupStr != "")
			$groupStr .= ",";
		$groupStr .=  "'".$key."'";
	}
	
	if (!$is_admin) {
		$acl = $access->getHostGroups("ALIAS");
		$hostStr = $access->getHostsString("NAME", $pearDBndo);
	}

	/* 
	 * requisit 
	 */
	(isset($_GET["instance"]) && !check_injection($_GET["instance"])) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	/*
	 * Get Status Colors
	 */
	$general_opt =& $oreon->optGen;

	/*
	 * check is admin
	 */
	$is_admin = isUserAdmin($sid);

	if (!$is_admin)
		$_POST["sid"] = $sid;


	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$tab_color_service = array();
	$tab_color_service[0] = $general_opt["color_ok"];
	$tab_color_service[1] = $general_opt["color_warning"];
	$tab_color_service[2] = $general_opt["color_critical"];
	$tab_color_service[3] = $general_opt["color_unknown"];
	$tab_color_service[4] = $general_opt["color_pending"];

	$tab_color_host = array();
	$tab_color_host[0] = $general_opt["color_up"];
	$tab_color_host[1] = $general_opt["color_down"];
	$tab_color_host[2] = $general_opt["color_unreachable"];

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");


	/*
	 * Prepare search string
	 */

	$searchStr = "";
	if ($search != "")
		$searchStr = " AND nhg.alias LIKE '%$search%' ";

	/*
	 * Get Host Status request
	 */
	$stats = array();
	if ($is_admin) {
		$rq1 = 	"SELECT nhg.alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$ndo_base_prefix."hostgroup_members nhgm " .
						"INNER JOIN ".$ndo_base_prefix."objects noo ON (noo.object_id = nhgm.host_object_id ) " .
						"INNER JOIN ".$ndo_base_prefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
						"INNER JOIN ".$ndo_base_prefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$ndo_base_prefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 $searchStr" .
				"GROUP BY nhg.alias, nhs.current_state";
	} else {
		$rq1 = 	"SELECT nhg.alias, nhs.current_state, count(nhs.host_object_id) AS nb " .
				"FROM ".$ndo_base_prefix."hostgroup_members nhgm " .
						"INNER JOIN ".$ndo_base_prefix."objects noo ON (noo.object_id = nhgm.host_object_id ) " .
						"INNER JOIN ".$ndo_base_prefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
						"INNER JOIN ".$ndo_base_prefix."objects no ON (noo.name1 = no.name1) " .
						"INNER JOIN ".$ndo_base_prefix."hoststatus nhs ON (nhs.host_object_id = no.object_id) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 1 AND noo.name1 IN ($hostStr) AND noo.name2 IS NULL $searchStr" .
				"GROUP BY nhg.alias, nhs.current_state";
	}
	$DBRESULT =& $pearDBndo->query($rq1);
	while ($ndo =& $DBRESULT->fetchRow()) {
		if (!isset($stats[$ndo["alias"]]))
			$stats[$ndo["alias"]] = array("h" => array(0=>0,1=>0,2=>0,3=>0), "s" => array(0=>0,1=>0,2=>0,3=>0,3=>0,4=>0));
		$stats[$ndo["alias"]]["h"][$ndo["current_state"]] = $ndo["nb"];
	}

	/*
	 * Get Services request
	 */
	if ($is_admin) {
			$rq2 = 	"SELECT nhg.alias, nss.current_state, count( nss.service_object_id ) AS nb " .
			"FROM ".$ndo_base_prefix."hostgroup_members nhgm " .
				"INNER JOIN ".$ndo_base_prefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
				"INNER JOIN ".$ndo_base_prefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
				"INNER JOIN ".$ndo_base_prefix."objects no ON ( noo.name1 = no.name1 ) " .
				"INNER JOIN ".$ndo_base_prefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
			"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2 $searchStr " .
			"GROUP BY nhg.alias, nss.current_state";
		
	} else {
		$rq2 = 	"SELECT nhg.alias, nss.current_state, count( nss.service_object_id ) AS nb " .
				"FROM ".$ndo_base_prefix."hostgroup_members nhgm " .
					"INNER JOIN ".$ndo_base_prefix."objects noo ON ( noo.object_id = nhgm.host_object_id ) " .
					"INNER JOIN ".$ndo_base_prefix."hostgroups nhg ON (nhgm.hostgroup_id = nhg.hostgroup_id) " .
					"INNER JOIN ".$ndo_base_prefix."objects no ON ( noo.name1 = no.name1 ) " .
					//"INNER JOIN centreon_acl ON (noo.name1 = host_name AND noo.name2 = service_description AND group_id IN ($groupStr)) " .
					"INNER JOIN ".$ndo_base_prefix."servicestatus nss ON ( nss.service_object_id = no.object_id ) " .
				"WHERE nhg.alias != '%-hostgroup' AND no.objecttype_id = 2 AND noo.name1 IN ($hostStr) $searchStr " .
				"GROUP BY nhg.alias, nss.current_state";
	}
	$DBRESULT =& $pearDBndo->query($rq2);
	while ($ndo =& $DBRESULT->fetchRow()) {
		if ($stats[$ndo["alias"]])
			$stats[$ndo["alias"]]["s"][$ndo["current_state"]] = $ndo["nb"];
	}

	if ($order == "DESC")
		ksort($stats);
	else
		krsort($stats);
	/* 
	 * Get Pagination Rows 
	 */
	$numRows = count($stats);

	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("p", $p);
	$buffer->endElement();	
	$class = "list_one";
	$ct = 0;
	$flag = 0;
     
    $convertTable = array();
    $convertID = array();
    $DBRESULT =& $pearDB->query("SELECT hg_id, hg_alias, hg_name FROM hostgroup");
    while ($hg =& $DBRESULT->fetchRow()){
		$convertTable[$hg["hg_name"]] = $hg["hg_alias"];
	    $convertID[$hg["hg_alias"]] = $hg["hg_id"];
    }
    $DBRESULT->free();
    
    $i = 0;
	foreach ($stats as $name => $stat) {
		if (($i < (($num + 1) * $limit) && $i >= (($num) * $limit)) && ((isset($convertID[$name]) && isset($acl[$convertID[$name]])) || (!isset($acl))) && $name != "meta_hostgroup") {
			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			if (isset($stat["h"]) && count($stat["h"])) {
				$buffer->startElement("l");
				$buffer->writeAttribute("class", $class);
				$buffer->writeElement("o", $ct++);
				$buffer->writeElement("hn", $name);
				$buffer->writeElement("hu", $stat["h"][0]);
				$buffer->writeElement("huc", $tab_color_host[0]);
				$buffer->writeElement("hd", $stat["h"][1]);
				$buffer->writeElement("hdc", $tab_color_host[1]);				
				$buffer->writeElement("hur", $stat["h"][2]);
				$buffer->writeElement("hurc", $tab_color_host[2]);
				$buffer->writeElement("sk", $stat["s"][0]);
				$buffer->writeElement("skc", $tab_color_service[0]);
				$buffer->writeElement("sw", $stat["s"][1]);
				$buffer->writeElement("swc", $tab_color_service[1]);
				$buffer->writeElement("sc", $stat["s"][2]);
				$buffer->writeElement("scc", $tab_color_service[2]);				
				$buffer->writeElement("su", $stat["s"][3]);
				$buffer->writeElement("suc", $tab_color_service[3]);
				$buffer->writeElement("sp", $stat["s"][4]);
				$buffer->writeElement("spc", $tab_color_service[4]);				
				$buffer->endElement();			
			}
		}
		$i++;
	}
	
	if (!$ct)
		$buffer->writeElement("infos", "none");		

	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	$buffer->output();
?>