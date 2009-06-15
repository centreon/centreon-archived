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
 
 	ini_set("display_errors", "Off"); 

	/*
	 * if debug == 0 => Normal, 
	 * debug == 1 => get use, 
	 * debug == 2 => log in file (log.xml)
	 */
	$debugXML = 0;
	$buffer = '';

	/*
	 * XML tag
	 */

	stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ? header("Content-type: application/xhtml+xml") : header("Content-type: text/xml"); 
	
	/*
	 * pearDB init
	 */ 	
	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/include/eventLogs/common-Func.php");
	include_once $centreon_path . "www/class/centreonDB.class.php";
	
	require_once ($centreon_path . "www/class/Session.class.php");
	require_once ($centreon_path . "www/class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", $centreon_path . "/www/locale/");
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
	
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");
	$pearDBndo 	= new CentreonDB("ndo");	
	
	/*
	 * Include Access Class
	 */
	include_once $centreon_path . "www/class/centreonACL.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonGMT.class.php";
	
	include_once $centreon_path . "www/include/common/common-Func.php";
	
	/*
	 * Start XML document root
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("root");	
 
 	/*
	 * Security check
	 */	
	(isset($_GET["lang"]) 	&& !check_injection($_GET["lang"])) ? $lang_ = htmlentities($_GET["lang"], ENT_QUOTES) : $lang_ = "-1";
	(isset($_GET["id"]) 	&& !check_injection($_GET["id"])) ? $openid = htmlentities($_GET["id"], ENT_QUOTES) : $openid = "-1";
	(isset($_GET["sid"]) 	&& !check_injection($_GET["sid"])) ? $sid = htmlentities($_GET["sid"], ENT_QUOTES) : $sid = "-1";
 
 	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession($sid);
 
	$contact_id = check_session($sid, $pearDB);
	
	$is_admin = isUserAdmin($sid);
	if (isset($sid) && $sid){
		$access = new CentreonAcl($contact_id, $is_admin);
		$lca = array("LcaHost" => $access->getHostServicesName($pearDBndo), "LcaHostGroup" => $access->getHostGroups(), "LcaSG" => $access->getServiceGroups());
		$lcaSTR = $access->getHostsString("NAME", $pearDBndo);
		$servicestr = $access->getServicesString("NAME", $pearDBndo);
	}
	
	(isset($_GET["num"]) 		&& !check_injection($_GET["num"])) ? $num = htmlentities($_GET["num"]) : $num = "0";
	(isset($_GET["limit"])		&& !check_injection($_GET["limit"])) ? $limit = htmlentities($_GET["limit"]) : $limit = "30";
	(isset($_GET["StartDate"]) 	&& !check_injection($_GET["StartDate"])) ? $StartDate = htmlentities($_GET["StartDate"]) : $StartDate = "";
	(isset($_GET["EndDate"]) 	&& !check_injection($_GET["EndDate"])) ? $EndDate = htmlentities($_GET["EndDate"]) : $EndDate = "";
	(isset($_GET["StartTime"]) 	&& !check_injection($_GET["StartTime"])) ? $StartTime = htmlentities($_GET["StartTime"]) : $StartTime = "";
	(isset($_GET["EndTime"]) 	&& !check_injection($_GET["EndTime"])) ? $EndTime = htmlentities($_GET["EndTime"]) : $EndTime = "";
	(isset($_GET["period"]) 	&& !check_injection($_GET["period"])) ? $auto_period = htmlentities($_GET["period"]) : $auto_period = "-1";
	(isset($_GET["multi"]) 		&& !check_injection($_GET["multi"])) ? $multi = htmlentities($_GET["multi"]) : $multi = "-1";

	(isset($_GET["up"]) 		&& !check_injection($_GET["up"])) ? set_user_param($contact_id, $pearDB, "log_filter_host_up", htmlentities($_GET["up"])) : $up = "true";
	(isset($_GET["down"]) 		&& !check_injection($_GET["down"])) ? set_user_param($contact_id, $pearDB, "log_filter_host_down", htmlentities($_GET["down"])) : $down = "true";
	(isset($_GET["unreachable"])&& !check_injection($_GET["unreachable"])) ? set_user_param($contact_id, $pearDB, "log_filter_host_unreachable", htmlentities($_GET["unreachable"])) : $unreachable = "true";
	(isset($_GET["ok"]) 		&& !check_injection($_GET["ok"])) ? set_user_param($contact_id, $pearDB, "log_filter_svc_ok", htmlentities($_GET["ok"])) : $ok = "true";
	(isset($_GET["warning"]) 	&& !check_injection($_GET["warning"])) ? set_user_param($contact_id, $pearDB, "log_filter_svc_warning", htmlentities($_GET["warning"])) : $warning = "true";
	(isset($_GET["critical"]) 	&& !check_injection($_GET["critical"])) ? set_user_param($contact_id, $pearDB, "log_filter_svc_critical", htmlentities($_GET["critical"])) : $critical = "true";
	(isset($_GET["unknown"]) 	&& !check_injection($_GET["unknown"])) ? set_user_param($contact_id, $pearDB, "log_filter_svc_unknown", htmlentities($_GET["unknown"])) : $unknown = "true";
	
	(isset($_GET["notification"]) && !check_injection($_GET["notification"])) ? set_user_param($contact_id, $pearDB, "log_filter_notif", htmlentities($_GET["notification"])) : $notification = "false";
	(isset($_GET["alert"]) 		&& !check_injection($_GET["alert"])) ? set_user_param($contact_id, $pearDB, "log_filter_alert", htmlentities($_GET["alert"])) : $alert = "true";
	(isset($_GET["error"]) 		&& !check_injection($_GET["error"])) ? set_user_param($contact_id, $pearDB, "log_filter_error", htmlentities($_GET["error"])) : $error = "false";
	(isset($_GET["oh"]) 		&& !check_injection($_GET["oh"])) ? set_user_param($contact_id, $pearDB, "log_filter_oh", htmlentities($_GET["oh"])) : $oh = "false";

	(isset($_GET["search_H"]) 	&& !check_injection($_GET["search_H"])) ? set_user_param($contact_id, $pearDB, "search_H", htmlentities($_GET["search_H"])) : $search_H = "VIDE";
	(isset($_GET["search_S"]) 	&& !check_injection($_GET["search_S"])) ? set_user_param($contact_id, $pearDB, "search_S", htmlentities($_GET["search_S"])) : $search_S = "VIDE";
	(isset($_GET["search_service"]) 		&& !check_injection($_GET["search_service"])) ? $search_service = htmlentities($_GET["search_service"], ENT_QUOTES) : $search_service = "";

	if ($contact_id){
		$user_params = get_user_param($contact_id, $pearDB);		
		
		if (!isset($user_params["log_filter_host"]))
			$user_params["log_filter_host"] = 1;
		if (!isset($user_params["log_filter_svc"]))
			$user_params["log_filter_svc"] = 1;
		if (!isset($user_params["log_filter_host_down"]))
			$user_params["log_filter_host_down"] = 1;
		if (!isset($user_params["log_filter_host_up"]))
			$user_params["log_filter_host_up"] = 1;
		if (!isset($user_params["log_filter_host_unreachable"]))
			$user_params["log_filter_host_unreachable"] = 1;
		if (!isset($user_params["log_filter_svc_ok"]))
			$user_params["log_filter_svc_ok"] = 1;
		if (!isset($user_params["log_filter_svc_warning"]))
			$user_params["log_filter_svc_warning"] = 1;
		if (!isset($user_params["log_filter_svc_critical"]))
			$user_params["log_filter_svc_critical"] = 1;
		if (!isset($user_params["log_filter_svc_unknown"]))
			$user_params["log_filter_svc_unknown"] = 1;
		if (!isset($user_params["log_filter_notif"]))
			$user_params["log_filter_notif"] = 1;
		if (!isset($user_params["log_filter_error"]))
			$user_params["log_filter_error"] = 1;
		if (!isset($user_params["log_filter_alert"]))
			$user_params["log_filter_alert"] = 1;
			
		if (!isset($user_params["search_H"]))
			$user_params["search_H"] = "";
		if (!isset($user_params["search_S"]))
			$user_params["search_S"] = "";
		
		$alert = $user_params["log_filter_alert"];
		$notification = $user_params["log_filter_notif"];
		$error = $user_params["log_filter_error"];
		$unknown = $user_params["log_filter_svc_unknown"];
		$unreachable = $user_params["log_filter_host_unreachable"];
		$up = $user_params["log_filter_host_up"];
		$ok = $user_params["log_filter_svc_ok"];
		$down = $user_params["log_filter_host_down"];
		$warning = $user_params["log_filter_svc_warning"];
		$critical = $user_params["log_filter_svc_critical"];
		$oh = $user_params["log_filter_oh"];
		
		$search_H = $user_params["search_H"];
		$search_S = $user_params["search_S"];
	}

	if ($StartDate != "" && $StartTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
		$start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], -1) ;
	}
	if ($EndDate !=  "" && $EndTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
		$end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], -1) ;
	}

	$period = 86400;
	if ($auto_period > 0) {
		$period = $auto_period;
		$start = time() - ($period);
		$end = time();
	}

	$general_opt = getStatusColor($pearDB);

	$tab_color_service 	= array("OK" => $general_opt["color_ok"], "WARNING" => $general_opt["color_warning"], "CRITICAL" => $general_opt["color_critical"], "UNKNOWN" => $general_opt["color_unknown"], "PENDING" => $general_opt["color_pending"]);
	$tab_color_host		= array("UP" => $general_opt["color_up"], "DOWN" => $general_opt["color_down"], "UNREACHABLE" => $general_opt["color_unreachable"]);
	
	$tab_type 			= array("1" => "HARD", "0" => "SOFT");
	$tab_class 			= array("0" => "list_one", "1" => "list_two");
	$tab_status_host 	= array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");

	$logs = array();	

	/*
	 * Print infos..
	 */
	$buffer->startElement("infos"); 
	$buffer->writeElement("multi", $multi);
	$buffer->writeElement("sid", $sid);
	$buffer->writeElement("opid", $openid);
	$buffer->writeElement("start", $start);
	$buffer->writeElement("end", $end);
	$buffer->writeElement("notification", $notification);
	$buffer->writeElement("alert", $alert);
	$buffer->writeElement("error", $error);
	$buffer->writeElement("up", $up);
	$buffer->writeElement("down", $down);
	$buffer->writeElement("unreachable", $unreachable);
	$buffer->writeElement("ok", $ok);
	$buffer->writeElement("warning", $warning);
	$buffer->writeElement("critical", $critical);
	$buffer->writeElement("unknown", $unknown);
	$buffer->writeElement("oh", $oh);
	$buffer->writeElement("search_H", $search_H);
	$buffer->writeElement("search_S", $search_S);
	$buffer->endElement();
	
	$msg_type_set = array ();
	if ($alert == 'true' )
		array_push ($msg_type_set, "'0'");
	if ($alert == 'true' )
		array_push ($msg_type_set, "'1'");
	if ($notification == 'true')
		array_push ($msg_type_set, "'2'");
	if ($notification== 'true')
		array_push ($msg_type_set, "'3'");
	if ($error == 'true')
		array_push ($msg_type_set, "'4'");
	
	$msg_req = '';
	
	$msg_status_set = array();
	
	if ($error == 'true')
		array_push($msg_status_set, "'NULL'");
	if ($up == 'true')
		array_push($msg_status_set, "'UP'");
	if ($down == 'true' )
		array_push($msg_status_set, "'DOWN'");
	if ($unreachable == 'true' )
		array_push($msg_status_set, "'UNREACHABLE'");
	
	if ($ok == 'true')
		array_push($msg_status_set, "'OK'");
	if ($warning == 'true')
		array_push($msg_status_set, "'WARNING'");
	if ($critical == 'true')
		array_push($msg_status_set, "'CRITICAL'");
	if ($unknown == 'true')
		array_push($msg_status_set, "'UNKNOWN'");
	
	$flag_begin = 0;
	if ($notification == 'true') {
		$msg_req .= "AND (";
		$flag_begin = 1;
		$msg_req .= " (`msg_type` IN ('2', '3') ";
		if (count($msg_status_set) > 0)
			$msg_req .= " AND `status` IN (" . implode(',', $msg_status_set).")"; 	
	 	$msg_req .= ") ";
	}
	if ($alert == 'true') {
		if ($flag_begin == 0) {
			$msg_req .= "AND (";
			$flag_begin = 1;
		} else
			$msg_req .= " OR ";
		$msg_req .= " (`msg_type` IN ('0', '1') ";
		if (count($msg_status_set) > 0)
		 	$msg_req .= " AND `status` IN (" . implode(',', $msg_status_set) . ") ";
		if ($oh == 'true')
			$msg_req .= " AND `type` = 'HARD' ";
		$msg_req .=	") ";
	}
	if ($error == 'true') {
		if ($flag_begin == 0) {
			$msg_req .= "AND (";
			$flag_begin = 1;
		} else
			$msg_req .= " OR ";
		$msg_req .= " (`msg_type` IN ('4', '5', '6', '7', '8', '9') AND `status` IS NULL) ";
	}
	if ($flag_begin)	
		$msg_req .= ")";
		

	$multi = 1;

	/*
	 * If multi checked 
	 */
	if ($multi == 1) {
		$tab_id = split(",", $openid);
		$tab_host_name = array();
		$tab_svc = array();
		/*
		 * prepare tab with host and svc
		 */
		$strSG = "";
		$tab_SG = array();
		$flag_already_call = 0;
		
		foreach ($tab_id as $openid) {
			$tab_tmp = split("_", $openid);
			if (isset($tab_tmp[1]))
				$id = $tab_tmp[1];
			$type = $tab_tmp[0];
			if ($type == "HG" && isset($id)){
				$hosts = getMyHostGroupHosts($id);
				foreach ($hosts as $h_id)	{
					$host_name = getMyHostName($h_id);
					if ((isset($lca["LcaHost"][$host_name]) && !$is_admin) || $is_admin) {
					   $tab_host_name[] = $host_name;
					   $tab_svc[$host_name] = getMyHostActiveServices($h_id, $search_service);
					}
				}
			} else if ($type == 'ST'){
				$services = getMyServiceGroupServices($id);
				foreach ($services as $svc_id => $svc_name)	{
					$tab_tmp = split("_", $svc_id);
					if ((($is_admin) || (!$is_admin && isset($lca["LcaHost"][getMyHostName($tab_tmp[1])]) && isset($lca["LcaHost"][getMyHostName($id)][$svc_name]))))	{
						$tab_SG[$flag_already_call] = array("h" => getMyHostName($tab_tmp[0]), "s" => getMyServiceName($tab_tmp[1], $tab_tmp[0])); 
						$flag_already_call++;
					}
				}
			} else if ($type == "HH") {
				$host_name = getMyHostName($id);
				$tab_host_name[] = $host_name;
				$tmp_tab = getMyHostActiveServices($id, $search_service);
				foreach ($tmp_tab as $key => $value)
					if ((!$is_admin && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name][$value])) || $is_admin)
						$tab_svc[$host_name][$key] = $value;
			} else if ($type == "HS") {
				$host_name = getMyHostName(getMyHostIDService($id));
				$service_description = getMyServiceName($id);
				if ((!$is_admin && isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name][$service_description])) || $is_admin)
					$tab_svc[$host_name][$id] = $service_description;
				unset($host_name);
				unset($service_description);
			} else if ($type == "MS") {
				if ($id != 0) {
					$tmp["svc_name"] = "meta_".$id;
					$tmp["host_name"] = "_Module_Meta";
					$tab_svc[] = $tmp;
				}
			}
		}
		
		$req = "SELECT * FROM `log` WHERE `ctime` > '$start' AND `ctime` <= '$end' $msg_req";
		
		/*
		 * Add Host
		 */
		$str_unitH_flag = 0;
		$str_unitH = "";
		
		if (!$is_admin) {
			foreach ($tab_host_name as $host_name ) {
				if ($str_unitH != "")
					$str_unitH .= ", ";
				$str_unitH .= "'$host_name'";
			}
		}
		
		if (!$is_admin && $str_unitH != "") {
			$str_unitH = "(`host_name` IN ($str_unitH) AND service_description IS NULL)";
		}
		
		/*
		 * Add services
		 */
		$flag = 0;
		$str_unitSVC = "";
		if (count($tab_svc) > 0 && ($ok == 'true' || $warning == 'true' || $critical == 'true' || $unknown == 'true')) {
			foreach ($tab_svc as $host_name => $services) {
				$str = "";
				foreach ($services as $svc_id => $svc_name) {
					if ((isset($lca["LcaHost"][$host_name]) && isset($lca["LcaHost"][$host_name][$svc_name])) || $is_admin) {
						if ($str != "")
							$str .= ",";
						$str .= "'$svc_name'";
					}
				}
				if ($str != "") {
					($flag == 1 || $str_unitH != "") ? $str_unitSVC .= "OR" : NULL;
					$str_unitSVC .= " (`host_name` = '".$host_name."' AND `service_description` IN ($str)) ";
					$flag = 1;
				}
			}
		}
		if (count($tab_SG) > 0){
			foreach ($tab_SG as $SG){
				($flag == 1 || $str_unitH != "") ? $str_unitSVC .= "OR" : NULL;
				$str_unitSVC .= " (`host_name` = '".$SG["h"]."' AND `service_description` = '".$SG["s"]."') ";
				$flag = 1;
			}
		}
		if ($str_unitH || $str_unitSVC)
			$req .= " AND (".$str_unitH.$str_unitSVC.")";
		
		if ($str_unitH  == "" && $str_unitSVC == "" && !isset($_GET['export']))
			$req = "";
		
	} else {
	
		/*
		 * only click on one element
		 */  
		$id = substr($openid, 3, strlen($openid));
		$type = substr($openid, 0, 2);	
		if ($type == "HG"){
			$hosts = getMyHostGroupHosts($id);
			$tab_host_name= array();
			foreach ($hosts as $h_id)	{
				$host_name = getMyHostName($h_id);
				array_push ($tab_host_name, "'".$host_name."'");
			}
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (`host_name` IN (".implode(",", $tab_host_name).") ";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR `host_name` is null';
			$req .= ")";
		} else if($type == "HH") {
			$host_name = getMyHostName($id);
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (`host_name` like '".$host_name."' ";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR `host_name` is null';
			$req .= ")";
		} else if($type == "HS") {
			$service_description = getMyServiceName($id);
			$host_id = getMyHostActivateService($id, $search_service);
			$host_name = getMyHostName($host_id);
			
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (`host_name` like '".$host_name."'";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR `host_name` is null';				
			$req .= ")";
			$req .= " AND (`service_description` like '".$service_description."' ";
			$req .= ") ";		
		} if ($type == "MS") {
			if ($id != 0) {
				$other_services = array();
				$DBRESULT2 =& $pearDBO->query("SELECT * FROM index_data WHERE `trashed` = '0' AND special = '1' AND service_description = 'meta_".$id."' ORDER BY service_description");
				if ($svc_id =& $DBRESULT2->fetchRow()){
					if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
						$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
						$meta =& $DBRESULT_meta->fetchRow();
						$DBRESULT_meta->free();
						$svc_id["service_description"] = $meta["meta_name"];
					}	
					$svc_id["service_description"] = str_replace("#S#", "/", $svc_id["service_description"]);
					$svc_id["service_description"] = str_replace("#BS#", "\\", $svc_id["service_description"]);
					$svc_id[$svc_id["id"]] = $svc_id["service_description"];
				}
				$DBRESULT2->free();
			}
		} else { 
			if ($is_admin)
				$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req";
		}
	}

	/*
	 * calculate size before limit for pagination 
	 */
	
	if (isset($req) && $req) {
		$lstart = 0;
		$DBRESULT =& $pearDBO->query($req);
		$rows = $DBRESULT->numrows();
		if (($num * $limit) > $rows)
			$num = round($rows / $limit) - 1;
		$lstart = $num * $limit;
		
		if ($lstart <= 0)
			$lstart = 0;
		
		/*
		 * pagination
		 */
		$page_max = ceil($rows / $limit);
		if ($num > $page_max && $rows)
			$num = $page_max - 1;
			
		if ($num < 0)
			$num = 0;
		
		$pageArr = array();
		$istart = 0;
		
		for ($i = 5, $istart = $num; $istart > 0 && $i > 0; $i--)
			$istart--;
		
		for ($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit -1)) && ( $i2 < (5 + $i)); $i2++)
			$iend++;
		
		for ($i = $istart; $i <= $iend; $i++)
			$pageArr[$i] = array("url_page"=>"&num=$i&limit=".$limit, "label_page"=>($i +1),"num"=> $i);
	
		if ($i > 1){
			foreach ($pageArr as $key => $tab) {
				$buffer->startElement("page");				
				if ($tab["num"] == $num)
					$buffer->writeElement("selected", "1");					
				else
					$buffer->writeElement("selected", "0");					
				$buffer->writeElement("num", $tab["num"]);
				$buffer->writeElement("url_page", $tab["url_page"]);
				$buffer->writeElement("label_page", $tab["label_page"]);
				$buffer->endElement();				
			}
		}
		$num_page = 0;
		
		if ($num > 0 && $num < $rows)
			$num_page= $num * $limit;
	
		$prev = $num - 1;
		$next = $num + 1;
			
		if ($num > 0) {
			$buffer->startElement("first");
			$buffer->writeAttribute("show", "true");
			$buffer->text("0");
			$buffer->endElement();			
		} else {
			$buffer->startElement("first");
			$buffer->writeAttribute("show", "false");
			$buffer->text("none");
			$buffer->endElement();			
		}
	
		if ($num > 1) {
			$buffer->startElement("prev");
			$buffer->writeAttribute("show", "true");
			$buffer->text($prev);
			$buffer->endElement();			
		} else {
			$buffer->startElement("prev");
			$buffer->writeAttribute("show", "false");
			$buffer->text("none");
			$buffer->endElement();			
		}
	
		if ($num < $page_max - 1) {
			$buffer->startElement("next");
			$buffer->writeAttribute("show", "true");
			$buffer->text($next);
			$buffer->endElement();			
		} else {
			$buffer->startElement("next");
			$buffer->writeAttribute("show", "false");
			$buffer->text("none");
			$buffer->endElement();			
		}
	
		$last = $page_max - 1;
	
		if ($num < $page_max-1) {
			$buffer->startElement("last");
			$buffer->writeAttribute("show", "true");
			$buffer->text($last);
			$buffer->endElement();			
		} else {
			$buffer->startElement("last");
			$buffer->writeAttribute("show", "false");
			$buffer->text("none");
			$buffer->endElement();			
		}
		
		/*
		 * Full Request
		 */
	    if (isset($csv_flag) && ($csv_flag == 1))
	    	$req .= " ORDER BY ctime DESC,log_id DESC LIMIT 0,64000"; //limit a little less than 2^16 which is excel maximum number of lines
	    else
	    	$req .= " ORDER BY ctime DESC,log_id DESC LIMIT $lstart,$limit";
		
		$DBRESULT =& $pearDBO->query($req);
		
		$cpts = 0;
		while ($log =& $DBRESULT->fetchRow()) {
			$buffer->startElement("line");
			$buffer->writeElement("msg_type", $log["msg_type"]);				
			$log["msg_type"] > 1 ? $buffer->writeElement("retry", "") : $buffer->writeElement("retry", $log["retry"]);
			$log["msg_type"] == 2 || $log["msg_type"] == 3 ? $buffer->writeElement("type", "NOTIF") : $buffer->writeElement("type", $log["type"]);
	
			/*
			 * Color initialisation for services and hosts status
			 */
			$color = '';
            if (isset($log["status"])) {
               if (isset($tab_color_service[$log["status"]]))
                  $color = $tab_color_service[$log["status"]];
               else if (isset($tab_color_host[$log["status"]]))
                  $color = $tab_color_host[$log["status"]];
            }

			/*
			 * Variable initialisation to color "INITIAL STATE" on envent logs
			 */
	        if ($log["output"] == "" && $log["status"] != "")
	        	$log["output"] = "INITIAL STATE";
	
			$buffer->startElement("status");
			$buffer->writeAttribute("color", $color);
			$buffer->text($log["status"]);
			$buffer->endElement();			
			if ($log["host_name"] == "_Module_Meta") {
				preg_match('/meta_([0-9]*)/', $log["service_description"], $matches);
				$DBRESULT2 =& $pearDB->query("SELECT * FROM meta_service WHERE meta_id = '".$matches[1]."'");
				$meta =& $DBRESULT2->fetchRow();
				$DBRESULT2->free();
				$buffer->writeElement("host_name", $log["host_name"]);
				$buffer->writeElement("service_description", $meta["meta_name"]);				
				unset($meta);
			} else {
				$buffer->writeElement("host_name", $log["host_name"]);
				$buffer->writeElement("service_description", $log["service_description"]);				
			}
			$buffer->writeElement("class", $tab_class[$cpts % 2]);
			$buffer->writeElement("date", $centreonGMT->getDate(_("Y/m/d"), $log["ctime"]));
			$buffer->writeElement("time", $centreonGMT->getDate(_("H:i:s"), $log["ctime"]));
			$buffer->writeElement("output", $log["output"]);
			$buffer->writeElement("contact", $log["notification_contact"]);
			$buffer->writeElement("contact_cmd", $log["notification_cmd"]);
			$buffer->endElement();			
			$cpts++;
		}
	}
	
	/*
	 * Translation for Menu.
	 */
	$buffer->startElement("lang");
	$buffer->writeElement("ty", _("Message Type"));
	$buffer->writeElement("n", _("Notifications"));
	$buffer->writeElement("a", _("Alerts"));
	$buffer->writeElement("e", _("Errors"));
	$buffer->writeElement("s", _("Status"));
	$buffer->writeElement("do", _("Down"));
	$buffer->writeElement("up", _("Up"));
	$buffer->writeElement("un", _("Unreachable"));
	$buffer->writeElement("w", _("Warning"));
	$buffer->writeElement("ok", _("Ok"));
	$buffer->writeElement("cr", _("Critical"));
	$buffer->writeElement("uk", _("Unknown"));
	$buffer->writeElement("oh", _("Hard Only"));
	$buffer->writeElement("sch", _("Search"));
	
	/*
	 * Translation for tables.
	 */
	$buffer->writeElement("d", _("Day"));
	$buffer->writeElement("t", _("Time"));
	$buffer->writeElement("h", _("Host/Service"));
	$buffer->writeElement("sc", _("Service Status"));
	$buffer->writeElement("T", _("Type"));
	$buffer->writeElement("R", _("Retry"));
	$buffer->writeElement("o", _("Output"));
	$buffer->writeElement("c", _("Contact"));
	$buffer->writeElement("C", _("Command"));
	
	$buffer->endElement();
	$buffer->endElement();
	$buffer->output();
	
	/*
	 * Saves user's period selection 
	 */
	if ($period != "-1") {
		set_user_param($contact_id, $pearDB, "log_filter_period", $period);
	}
	else {
		set_user_param($contact_id, $pearDB, "log_filter_period", "0");
	}
?>