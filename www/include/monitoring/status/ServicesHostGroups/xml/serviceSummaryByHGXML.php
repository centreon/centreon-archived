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

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/other.class.php");
	include_once($centreon_path . "www/class/centreonACL.class.php");
	include_once($centreon_path . "www/class/centreonXML.class.php");
	include_once($centreon_path . "www/class/centreonDB.class.php");
	include_once($centreon_path . "www/include/monitoring/status/Common/common-Func.php");	
	include_once($centreon_path . "www/include/common/common-Func.php");

	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	$general_opt = getStatusColor($pearDB);
	
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $order = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// check is admin
	$is_admin = isUserAdmin($sid);
	$user_id = getUserIdFromSID($sid);
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups();
	$grouplistStr = $access->getAccessGroupsString(); 
	$groupnumber = count($grouplist);

		
	/*
	 * invert HG Name and Alias
	 */

	$tabAliasName = array();
	$row = array();
	$DBRESULT_PAGINATION =& $pearDB->query("SELECT hg_name, hg_alias FROM hostgroup");
	while ($row &= $DBRESULT_PAGINATION->numRows())
		$tabAliasName[$row["hg_name"]] = $row["hg_alias"];

	/*
	 * Get Acl Group list
	 */
	 
	function get_services($host_name){
		
		global $pearDBndo, $pearDB, $ndo_base_prefix, $general_opt, $o, $instance, $is_admin, $groupnumber, $grouplist, $access;

		$rq = 		" SELECT no.name1, no.name2 as service_name, nss.current_state" .
					" FROM `" .$ndo_base_prefix."servicestatus` nss, `" .$ndo_base_prefix."objects` no";
						
		if (!$is_admin)
			$rq .= ", centreon_acl ";
					
		$rq .= 		" WHERE no.object_id = nss.service_object_id" .
					" AND no.name1 NOT LIKE '_Module_%'";					
		
		$grouplistStr = $access->getAccessGroupsString(); 
		$rq .= 	$access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $access->queryBuilder("AND", "no.name2", "centreon_acl.service_description") . $access->queryBuilder("AND", "centreon_acl.group_id", $grouplistStr);

		if	($o == "svcgridHG_pb" || $o == "svcOVHG_pb")
			$rq .= 	" AND nss.current_state != 0" ;

		if ($o == "svcgridHG_ack_0" || $o == "svcOVHG_ack_0")
			$rq .= 	" AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" ;

		if ($o == "svcgridHG_ack_1" || $o == "svcOVHG_ack_1")
			$rq .= 	" AND nss.problem_has_been_acknowledged = 1" ;

		$rq .= 		" AND no.object_id" .
					" IN (" .
					" SELECT nno.object_id" .
					" FROM ".$ndo_base_prefix."objects nno" .
					" WHERE nno.objecttype_id =2" .
					" AND nno.name1 = '".$host_name."'" .
					" AND nno.name1 NOT LIKE '_Module_%'".
					" )";
		
		if($instance != "ALL")
			$rq .= " AND no.instance_id = ".$instance;

		$DBRESULT =& $pearDBndo->query($rq);
		$tab = array();
		while ($svc =& $DBRESULT->fetchRow()){
			$tab[$svc["service_name"]] = $svc["current_state"];
		}
		return($tab);
	}
		

	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	$tab_color_service = array(0 => $general_opt["color_ok"], 1 => $general_opt["color_warning"], 2 => $general_opt["color_critical"], 3 => $general_opt["color_unknown"], 4 => $general_opt["color_pending"]);
    $tab_color_host = array(0 => $general_opt["color_up"], 1 => $general_opt["color_down"], 2 => $general_opt["color_unreachable"]);

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	/* Get Host status */

	$rq1 = 			" SELECT DISTINCT no.name1 as host_name, hg.alias, hgm.hostgroup_id, hgm.host_object_id, hs.current_state".
					" FROM " .$ndo_base_prefix."hostgroups hg," .$ndo_base_prefix."hostgroup_members hgm, " .$ndo_base_prefix."hoststatus hs, " .$ndo_base_prefix."objects no";
		
	if (!$is_admin)
		$rq1 .= ", centreon_acl ";
	
	$rq1 .= 		" WHERE hs.host_object_id = hgm.host_object_id".
					" AND no.object_id = hgm.host_object_id" .
					" AND hgm.hostgroup_id = hg.hostgroup_id".
					" AND no.name1 not like '_Module_%'";
		
	if (!$is_admin)
		$rq1 .= $access->queryBuilder("AND", "no.name1", "centreon_acl.host_name") . $access->queryBuilder("AND", "group_id", $grouplistStr) . " " . $access->queryBuilder("AND", "hg.alias", $access->getHostGroupsString());
	
	if ($instance != "ALL")
		$rq1 .= 	" AND no.instance_id = ".$instance;
	
	if ($o == "svcSumHG_ack_0") {
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0)";
	}
	if ($o == "svcSumHG_ack_1"){
		$rq1 .= 	" AND no.name1 IN (" .
					" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
					" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0)";
	}
		
	if ($search != "")
		$rq1 .= " AND no.name1 like '%" . $search . "%' ";
		
	$rq_pagination = $rq1;

	/* 
	 * Get Pagination Rows
	 */
	 
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " ORDER BY hg.alias";
	$rq1 .= " LIMIT ".($num * $limit).",".$limit;

	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("p", $p);
	$o == "svcOVHG" ? $buffer->writeElement("s", "1") : $buffer->writeElement("s", "0");
	$buffer->endElement();

	$DBRESULT_NDO =& $pearDBndo->query($rq1);
	
	$class = "list_one";
	$ct = 0;
	$flag = 0;

	$tab_final = array();
	while ($ndo =& $DBRESULT_NDO->fetchRow()) {
		if (!isset($tab_final[$ndo["alias"]]))
			$tab_final[$ndo["alias"]] = array();
		if (!isset($tab_final[$ndo["alias"]][$ndo["host_name"]]))
			$tab_final[$ndo["alias"]][$ndo["host_name"]] = array("0"=>0,"1"=>0,"2"=>0,"3"=>0,"4"=>0);
			
		$tab_svc = get_services($ndo["host_name"]);
		foreach ($tab_svc as $name => $status)
			$tab_final[$ndo["alias"]][$ndo["host_name"]][$status]++;
		$tab_final[$ndo["alias"]][$ndo["host_name"]]["cs"] = $ndo["current_state"];
	}

	$hg = "";
	if (isset($tab_final))
		foreach ($tab_final as $hg_name => $tab_host) {
			foreach ($tab_host as $host_name => $tab) {
				$class == "list_one" ? $class = "list_two" : $class = "list_one";
				if (isset($hg_name) && $hg != $hg_name){
					if ($hg != "")
						$buffer->endElement();
					$hg = $hg_name;
					$buffer->startElement("hg");
					$buffer->writeElement("hgn", $hg_name);					
				}
				$buffer->startElement("l");
				$buffer->writeAttribute("class", $class);
				$buffer->writeElement("sk", $tab[0]);
				$buffer->writeElement("skc", $tab_color_service[0]);
				$buffer->writeElement("sw", $tab[1]);
				$buffer->writeElement("swc", $tab_color_service[1]);
				$buffer->writeElement("sc", $tab[2]);
				$buffer->writeElement("scc", $tab_color_service[2]);
				$buffer->writeElement("su", $tab[3]);
				$buffer->writeElement("suc", $tab_color_service[3]);
				$buffer->writeElement("sp", $tab[4]);
				$buffer->writeElement("spc", $tab_color_service[4] );
				$buffer->writeElement("o", $ct++);
				$buffer->writeElement("hn", $host_name);
				$buffer->writeElement("hs", $tab_status_host[$tab["cs"]]);
				$buffer->writeElement("hc", $tab_color_host[$tab["cs"]]);				
				$buffer->endElement();				
			}
		}
	$buffer->endElement();

	if (!$ct)
		$buffer->writeElement("infos", "none");

	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	$buffer->output();
?>