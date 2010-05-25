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

	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/class/centreonSession.class.php");
    include_once($centreon_path . "www/class/centreon.class.php");	
	include_once($centreon_path . "www/class/centreonDuration.class.php");
	include_once($centreon_path . "www/class/centreonACL.class.php");
	include_once($centreon_path . "www/class/centreonXML.class.php");
	include_once($centreon_path . "www/class/centreonDB.class.php");
	include_once($centreon_path . "www/include/monitoring/status/Common/common-Func.php");	
	include_once($centreon_path . "www/include/common/common-Func.php");

	CentreonSession::start();
    $oreon =& $_SESSION["oreon"];

	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();
	
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])) {
		$sid = htmlentities($_GET["sid"]);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	$user_id = getUserIdFromSID($sid);
	$is_admin = isUserAdmin($sid);
	$access = new CentreonACL($user_id, $is_admin);
	$grouplist = $access->getAccessGroups();
	$grouplistStr = $access->getAccessGroupsString();
	$groupnumber = count($grouplist);

	/* requisit */
	(isset($_GET["instance"])/* && !check_injection($_GET["instance"])*/) ? $instance = htmlentities($_GET["instance"]) : $instance = "ALL";
	(isset($_GET["num"]) && !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : get_error('num unknown');
	(isset($_GET["limit"]) && !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : get_error('limit unknown');
	(isset($_GET["search"]) && !check_injection($_GET["search"])) ? $search = htmlentities($_GET["search"]) : $search = "";
	(isset($_GET["sort_type"]) && !check_injection($_GET["sort_type"])) ? $sort_type = htmlentities($_GET["sort_type"]) : $sort_type = "host_name";
	(isset($_GET["order"]) && !check_injection($_GET["order"])) ? $order = htmlentities($_GET["order"]) : $oreder = "ASC";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";
	(isset($_GET["o"]) && !check_injection($_GET["o"])) ? $o = htmlentities($_GET["o"]) : $o = "h";
	(isset($_GET["p"]) && !check_injection($_GET["p"])) ? $p = htmlentities($_GET["p"]) : $p = "2";

	// if is admin -> lca
	if (!$is_admin){
		$_POST["sid"] = $sid;
	}
	
	/*
	 * Get status Color
	 */
	$general_opt = getStatusColor($pearDB);

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
	$rq1 = 		" SELECT " .
				" DISTINCT no.name1 as host_name, nhs.current_state" .
				" FROM " .$ndo_base_prefix."objects no, " .$ndo_base_prefix."hoststatus nhs";

	if (!$is_admin)	
		$rq1 .= ", centreon_acl ";

	$rq1 .=	" WHERE no.objecttype_id = 1 AND nhs.host_object_id = no.object_id ".
				" AND no.name1 NOT LIKE '_Module_%'";				

	if ($o == "svcSum_pb")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.current_state != 0)";

	if ($o == "svcSum_ack_0")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 0 AND nss.current_state != 0" .
				")";

	if ($o == "svcSum_ack_1")
		$rq1 .= " AND no.name1 IN (" .
				" SELECT nno.name1 FROM " .$ndo_base_prefix."objects nno," .$ndo_base_prefix."servicestatus nss " .
				" WHERE nss.service_object_id = nno.object_id AND nss.problem_has_been_acknowledged = 1 AND nss.current_state != 0" .
				")";

	if ($search != "")
		$rq1 .= " AND no.name1 LIKE '%" . $search . "%' ";	
	
	$rq1 .= $access->queryBuilder("AND", "no.name1", "centreon_acl.host_name").$access->queryBuilder("AND", "group_id", $grouplistStr);

	switch($sort_type){
		case 'current_state' : $rq1 .= " order by nhs.current_state ". $order.",no.name1 "; break;
		default : $rq1 .= " order by no.name1 ". $order; break;
	}

	$rq_pagination = $rq1;

	/* Get Pagination Rows */
	$DBRESULT_PAGINATION =& $pearDBndo->query($rq_pagination);
	$numRows = $DBRESULT_PAGINATION->numRows();
	/* End Pagination Rows */

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
	$class = "list_one";
	$ct = 0;
	$flag = 0;

	$tab_final = array();
	while ($ndo =& $DBRESULT_NDO1->fetchRow()){
		$tab_final[$ndo["host_name"]]["nb_service_k"] = 0;
		if ($o != "svcSum_pb" && $o != "svcSum_ack_1"  && $o !=  "svcSum_ack_0")
			$tab_final[$ndo["host_name"]]["nb_service_k"] =  get_services_status($ndo["host_name"], 0);
		$tab_final[$ndo["host_name"]]["nb_service_w"] = 0 + get_services_status($ndo["host_name"], 1);
		$tab_final[$ndo["host_name"]]["nb_service_c"] = 0 + get_services_status($ndo["host_name"], 2);
		$tab_final[$ndo["host_name"]]["nb_service_u"] = 0 + get_services_status($ndo["host_name"], 3);
		$tab_final[$ndo["host_name"]]["nb_service_p"] = 0 + get_services_status($ndo["host_name"], 4);
		$tab_final[$ndo["host_name"]]["cs"] = $ndo["current_state"];
	}

	foreach ($tab_final as $host_name => $tab)	{
		$class == "list_one" ? $class = "list_two" : $class = "list_one";
		$buffer->startElement("l");
		$buffer->writeAttribute("class", $class);
		$buffer->writeElement("o", $ct++);		
		$buffer->writeElement("hn", $host_name);
		$buffer->writeElement("hs", $tab_status_host[$tab["cs"]]);
		$buffer->writeElement("hc", $tab_color_host[$tab["cs"]]);
		$buffer->writeElement("sk", $tab["nb_service_k"]);
		$buffer->writeElement("skc", $tab_color_service[0]);
		$buffer->writeElement("sw", $tab["nb_service_w"]);
		$buffer->writeElement("swc", $tab_color_service[1]);
		$buffer->writeElement("sc", $tab["nb_service_c"]);
		$buffer->writeElement("scc", $tab_color_service[2]);
		$buffer->writeElement("su", $tab["nb_service_u"]);
		$buffer->writeElement("suc", $tab_color_service[3]);
		$buffer->writeElement("sp", $tab["nb_service_p"]);				
		$buffer->writeElement("spc", $tab_color_service[4]);
		$buffer->endElement();		
	}
	/* end */

	if (!$ct)
		$buffer->writeElement("infos", "none");

	$buffer->endElement();
	header('Content-Type: text/xml');
	header('Pragma: no-cache');
	header('Expires: 0');
	header('Cache-Control: no-cache, must-revalidate'); 
	$buffer->output();
?>