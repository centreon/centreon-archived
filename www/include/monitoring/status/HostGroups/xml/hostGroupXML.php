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
 * SVN : $URL
 * SVN : $Id: 
 * 
 */

	/*
	 * if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';
	
	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/other.class.php");
	include_once($centreon_path . "www/class/centreonACL.class.php");
	include_once($centreon_path . "www/class/centreonXML.class.php");
	include_once($centreon_path . "www/class/centreonDB.class.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");

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
	$general_opt = getStatusColor($pearDB);

	function get_hosts_status($host_group_id, $status){
		global $pearDB, $pearDBndo, $ndo_base_prefix, $general_opt, $is_admin, $grouplist, $groupnumber, $access;		

		$rq = 	" SELECT count( nhs.host_object_id ) AS nb".
				" FROM " .$ndo_base_prefix."hoststatus nhs".
				" WHERE nhs.current_state = '".$status."'".
				" AND nhs.host_object_id IN (" .
				"SELECT nhgm.host_object_id FROM ".$ndo_base_prefix."hostgroup_members nhgm, ".$ndo_base_prefix."hosts nh, centreon_acl WHERE nhgm.hostgroup_id =".$host_group_id." AND nh.host_object_id = nhgm.host_object_id " .
				$access->queryBuilder("AND", "nh.display_name", "centreon_acl.host_name") . $access->queryBuilder("AND", "centreon_acl.group_id", $access->getAccessGroupsString()) . ")";

		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab =& $DBRESULT->fetchRow();
		return($tab["nb"]);
	}

	function get_services_status($host_group_id, $status){
		global $pearDB, $pearDBndo, $ndo_base_prefix, $general_opt, $instance,$lcaSTR, $is_admin, $grouplist, $access;

		$rq = 			" SELECT count( nss.service_object_id ) AS nb".
						" FROM " .$ndo_base_prefix."servicestatus nss ".
						" WHERE nss.current_state = '".$status."'".
						" AND nss.service_object_id".
						" IN (SELECT nno.object_id FROM " .$ndo_base_prefix."objects nno, centreon_acl WHERE nno.objecttype_id = '2' ";
								
		$rq .= 	$access->queryBuilder("AND", "nno.name1", "centreon_acl.host_name") . $access->queryBuilder("AND", "nno.name2", "centreon_acl.service_description") . $access->queryBuilder("AND", "centreon_acl.group_id", $access->getAccessGroupsString());

		if ($instance != "ALL")
			$rq .= 	" AND nno.instance_id = ".$instance;

		$rq .= 		" AND nno.name1 IN (".
						" SELECT no.name1".
						" FROM " .$ndo_base_prefix."objects no, " .$ndo_base_prefix."hostgroup_members nhgm".
						" WHERE nhgm.hostgroup_id =".$host_group_id.
						" AND no.object_id = nhgm.host_object_id".")".
					" )";
		$DBRESULT =& $pearDBndo->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$tab =& $DBRESULT->fetchRow();
		return($tab["nb"]);
	}

	// check is admin
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


	/* Get Host status */
	$rq1 = 	" SELECT " .
			" no.name1 as hostgroup_name," .
			" hg.hostgroup_id" .
			" FROM " .$ndo_base_prefix."hostgroups hg, ".$ndo_base_prefix."objects no, centreon_acl ".
			" WHERE no.object_id = hg.hostgroup_object_id AND hg.alias != 'meta_hostgroup'";

	if ($search != "")
		$rq1 .= 	" AND no.name1 like '%" . $search . "%' ";
	
	if ($instance != "ALL")
		$rq1 .= 	" AND no.instance_id = ".$instance;
	$rq1 .= 		" group by no.name1 ";
	$rq1 .= 		" order by no.name1 ". $order;

	$rq_pagination = $rq1;

	/* 
	 * Get Pagination Rows 
	 */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	if (PEAR::isError($DBRESULT_PAGINATION))
		print "DB Error : ".$DBRESULT_PAGINATION->getDebugInfo()."<br />";
	$numRows = $DBRESULT_PAGINATION->numRows();

	$rq1 .= " LIMIT ".($num * $limit).",".$limit;


	$buffer = new CentreonXML();
	$buffer->startElement("reponse");
	$buffer->startElement("i");
	$buffer->writeElement("numrows", $numRows);
	$buffer->writeElement("num", $num);
	$buffer->writeElement("limit", $limit);
	$buffer->writeElement("p", $p);
	$buffer->endElement();
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$class = "list_one";
	$ct = 0;
	$flag = 0;

	while ($ndo =& $DBRESULT_NDO1->fetchRow()) 
	{
		if (isset($lca["LcaHostGroup"][$ndo["hostgroup_name"]]) || !isset($lca)) {
			$nb_host_up = 0 + get_hosts_status($ndo["hostgroup_id"], 0);
			$nb_host_down = 0 + get_hosts_status($ndo["hostgroup_id"], 1);
			$nb_host_unreachable = 0 + get_hosts_status($ndo["hostgroup_id"], 2);
	
			$nb_service_k = 0 + get_services_status($ndo["hostgroup_id"], 0);
			$nb_service_w = 0 + get_services_status($ndo["hostgroup_id"], 1);
			$nb_service_c = 0 + get_services_status($ndo["hostgroup_id"], 2);
			$nb_service_u = 0 + get_services_status($ndo["hostgroup_id"], 3);
			$nb_service_p = 0 + get_services_status($ndo["hostgroup_id"], 4);
	
			$passive = 0;
			$active = 1;
			$last_check = " ";
			$duration = " ";
	
			$class == "list_one" ? $class = "list_two" : $class = "list_one";
			if ($nb_host_up || $nb_host_down || $nb_host_unreachable || $nb_service_k || $nb_service_w || $nb_service_c || $nb_service_u || $nb_service_p){
				$buffer->startElement("l");
				$buffer->writeAttribute("class", $class);
				$buffer->writeElement("o", $ct++);
				$buffer->writeElement("hn", $ndo["hostgroup_name"]);
				$buffer->writeElement("hu", $nb_host_up);
				$buffer->writeElement("huc", $tab_color_host[0]);
				$buffer->writeElement("hd", $nb_host_down);
				$buffer->writeElement("hdc", $tab_color_host[1]);				
				$buffer->writeElement("hur", $nb_host_unreachable);
				$buffer->writeElement("hurc", $tab_color_host[2]);
				$buffer->writeElement("sk", $nb_service_k);
				$buffer->writeElement("skc", $tab_color_service[0]);
				$buffer->writeElement("sw", $nb_service_w);
				$buffer->writeElement("swc", $tab_color_service[1]);
				$buffer->writeElement("sc", $nb_service_c);
				$buffer->writeElement("scc", $tab_color_service[2]);				
				$buffer->writeElement("su", $nb_service_u);
				$buffer->writeElement("suc", $tab_color_service[3]);
				$buffer->writeElement("sp", $nb_service_p);
				$buffer->writeElement("spc", $tab_color_service[4]);				
				$buffer->endElement();				
			}
		}
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