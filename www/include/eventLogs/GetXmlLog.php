<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';

	/*
	 * XML tag
	 */
	
	stristr($_SERVER["HTTP_ACCEPT"],"application/xhtml+xml") ? header("Content-type: application/xhtml+xml") : header("Content-type: text/xml"); 
	echo("<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n");
	
	/*
	 * Start XML document root
	 */
 
	echo "<root>";
	
	/*
	 * pearDB init
	 */ 
	require_once 'DB.php';
	
	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/include/eventLogs/common-Func.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBOdsConnect.php");
	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");
	
	/*
	 * Lang file
	 */
	 
	(isset($_GET["lang"]) 	&& !check_injection($_GET["lang"])) ? $lang_ = htmlentities($_GET["lang"]) : $lang_ = "-1";
	(isset($_GET["id"]) 	&& !check_injection($_GET["id"])) ? $openid = htmlentities($_GET["id"]) : $openid = "-1";
	(isset($_GET["sid"])	&& !check_injection($_GET["sid"])) ? $sid = htmlentities($_GET["sid"]) : $sid = "-1";

	$contact_id = check_session($sid,$pearDB);
	
	$is_admin = isUserAdmin($sid);
	if (!$is_admin){
		$_POST["sid"] = $sid;	
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
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
	}

	if ($StartDate !=  "" && $StartTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $StartDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $StartTime, $matchesT);
		$start = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 1) ;
	}
	if ($EndDate !=  "" && $EndTime != ""){
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $EndDate, $matchesD);
		preg_match("/^([0-9]*):([0-9]*)/", $EndTime, $matchesT);
		$end = mktime($matchesT[1], $matchesT[2], "0", $matchesD[1], $matchesD[2], $matchesD[3], 1) ;
	}

	$period = 86400;
	if ($auto_period > 0){
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
	 
	echo "<infos>";
	echo "<multi>".$multi."</multi>";
	echo "<sid>".$sid."</sid>";
	echo "<opid>".$openid."</opid>";
	echo "<start>".$start."</start>";
	echo "<end>".$end."</end>";
	echo "<notification>".$notification."</notification>";
	echo "<alert>".$alert."</alert>";
	echo "<error>".$error."</error>";
	echo "<up>".$up."</up>";
	echo "<down>".$down."</down>";
	echo "<unreachable>".$unreachable."</unreachable>";
	echo "<ok>".$ok."</ok>";
	echo "<warning>".$warning."</warning>";
	echo "<critical>".$critical."</critical>";
	echo "<unknown>".$unknown."</unknown>";
	echo "<oh>".$oh."</oh>";
	echo "</infos>";
	
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
	
	if (count($msg_type_set) > 0)
		$msg_req .= ' AND msg_type IN (' . implode(",",$msg_type_set). ') ';
	
	$msg_status_set = array ();
	
	if ($error == 'true')
		array_push ($msg_status_set, "'NULL'");
	if ($up == 'true' )
		array_push ($msg_status_set, "'UP'");
	if ($down == 'true' )
		array_push ($msg_status_set, "'DOWN'");
	if ($unreachable == 'true' )
		array_push ($msg_status_set, "'UNREACHABLE'");
	
	if ($ok == 'true')
		array_push ($msg_status_set, "'OK'");
	if ($warning == 'true')
		array_push ($msg_status_set, "'WARNING'");
	if ($critical == 'true')
		array_push ($msg_status_set, "'CRITICAL'");
	if ($unknown == 'true')
		array_push ($msg_status_set, "'UNKNOWN'");
	
	if (count($msg_status_set) > 0 ){
		$msg_req .= ' AND (status IN (' . implode(",",$msg_status_set). ') ';
		if ($error  == 'true' || $notification == 'true')
			$msg_req .= 'OR status is null';
		$msg_req .=')';
	}

	if ($oh == 'true')
		$msg_req .= " AND `type` = 'HARD' ";

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
			$tab_tmp = split("_",$openid);
			$id = $tab_tmp[1];
			$type = $tab_tmp[0];
			if ($type == "HG"){
				$hosts = getMyHostGroupHosts($id);
				foreach ($hosts as $h_id)	{
					$host_name = getMyHostName($h_id);
					array_push ($tab_host_name, "'".$host_name."'");
				}
			} else if ($type == 'ST'){
				$services = getMyServiceGroupServices($id);
				foreach ($services as $svc_id => $svc_name)	{
					$tab_tmp = split("_", $svc_id);
					if (service_has_graph($tab_tmp[0], $tab_tmp[1]) && (($is_admin) || (!$is_admin && isset($lca["LcaHost"][getMyHostName($tab_tmp[1])]) && isset($lca["LcaHost"][getMyHostName($id)]["svc"][$svc_name]))))	{
						$tab_SG[$flag_already_call] = array("h" => getMyHostName($tab_tmp[0]), "s" => getMyServiceName($tab_tmp[1], $tab_tmp[0])); 
						$flag_already_call++;
					}
				}
			} else if ($type == "HH"){
				$host_name = getMyHostName($id);
				array_push ($tab_host_name, "'".$host_name."'");		
			} else if ($type == "HS"){
				$service_description = getMyServiceName($id);
				$host_id = getMyHostIDService($id);
				$host_name = getMyHostName($host_id);
				$tmp["svc_name"] = $service_description;
				$tmp["host_name"] = $host_name;
				array_push($tab_svc, $tmp);
			} else if ($type == "MS"){
				$service_description = "meta_".$id;
				$host_name = "Meta_Module";
				$tmp["svc_name"] = $service_description;
				$tmp["host_name"] = $host_name;
				array_push($tab_svc, $tmp);
			}
		}
		/*
		 * Building request
		 */
		
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req";
		
		/*
		 * Add Host
		 */
		$str_unitH_flag = 0;
		$str_unitH = "";
		if	(count($tab_host_name) > 0){
			$str_unitH .= " (`host_name` in (".implode(",", $tab_host_name).")) ";
			if ($error  == 'true' || $notification == 'true')
				$str_unitH .= ' OR host_name is null';
			$str_unitH_flag = 1;
		}
		if ($str_unitH !=  "" && (count($tab_svc) || count($tab_SG)))
			$str_unitH .= " OR (";
		/*
		 * Concat 
		 */
		$flag = 0;
		$str_unitSVC = "";
		if (count($tab_svc) > 0){
			foreach ($tab_svc as $svc){
				($flag == 1) ? $str_unitSVC .= " OR " : NULL;
				$str_unitSVC .= " (`host_name` = '".$svc["host_name"]."' AND `service_description` = '".$svc["svc_name"]."') ";
				$flag = 1;			
			}
			
		}
		if (count($tab_SG) > 0){
			foreach ($tab_SG as $SG){
				($flag && strlen($str_unitSVC)) ? $str_unitSVC .= " OR " : NULL;
				$str_unitSVC .= " (host_name = '".$SG["h"]."' AND service_description = '".$SG["s"]."') ";
				$flag = 1;			
			}
		}  
		if ($str_unitH !=  "" && (count($tab_svc) || count($tab_SG)))
			$str_unitSVC .= " )";
		
		if ($str_unitH || $str_unitSVC)
			$req .= " AND (".$str_unitH.$str_unitSVC.")";
		/* 
		 * Debug
		 */
		//print "\n\n\n".$req."\n\n\n";
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
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (host_name in(".implode(",",$tab_host_name).") ";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR host_name is null';
			$req .= ")";
		} else if($type == "HH") {
			$host_name = getMyHostName($id);
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (host_name like '".$host_name."' ";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR host_name is null';
			$req .= ")";
		} else if($type == "HS"){
			$service_description = getMyServiceName($id);
			$host_id = getMyHostIDService($id);
			$host_name = getMyHostName($host_id);
		
			$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND (host_name like '".$host_name."'";
			if ($error  == 'true' || $notification == 'true')
				$req .= ' OR host_name is null';				
			$req .= ")";
			$req .= " AND (service_description like '".$service_description."' ";
			$req .= ") ";		
		} if ($type == "MS"){			
			$other_services = array();
			
			$DBRESULT2 =& $pearDBO->query("SELECT * FROM index_data WHERE `trashed` = '0' AND special = '1' AND service_description = 'meta_".$id."' ORDER BY service_description");
			if (PEAR::isError($DBRESULT2))
				print "Mysql Error : ".$DBRESULT2->getDebugInfo();
			if ($svc_id =& $DBRESULT2->fetchRow()){
				if (preg_match("/meta_([0-9]*)/", $svc_id["service_description"], $matches)){
					$DBRESULT_meta =& $pearDB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
					if (PEAR::isError($DBRESULT_meta))
						print "Mysql Error : ".$DBRESULT_meta->getDebugInfo();
					$meta =& $DBRESULT_meta->fetchRow();
					$DBRESULT_meta->free();
					$svc_id["service_description"] = $meta["meta_name"];
				}	
				$svc_id["service_description"] = str_replace("#S#", "/", $svc_id["service_description"]);
				$svc_id["service_description"] = str_replace("#BS#", "\\", $svc_id["service_description"]);
				$svc_id[$svc_id["id"]] = $svc_id["service_description"];
			}
			$DBRESULT2->free();
		} else { 
			if ($is_admin)
				$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req";
			else
				$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $msg_req AND host_name IN ($lcaSTR)";
		}
	}

	/*
	 * calculate size before limit for pagination 
	 */
	 
	$lstart = 0;
	$DBRESULT =& $pearDBO->query($req);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage() . "\n\n\n $req";
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
			echo "<page>";
			if ($tab["num"] == $num)
				echo "<selected>1</selected>";
			else
				echo "<selected>0</selected>";
			echo "<num><![CDATA[".$tab["num"]."]]></num>";
			echo "<url_page><![CDATA[".$tab["url_page"]."]]></url_page>";
			echo "<label_page><![CDATA[".$tab["label_page"]."]]></label_page>";
			echo "</page>";
		}
	}
	$num_page = 0;
	
	if ($num > 0 && $num < $rows)
		$num_page= $num * $limit;

	$prev = $num - 1;
	$next = $num + 1;
		
	if ($num > 0)
		echo "<first show='true'>0</first>";
	else
		echo "<first show='false'>none</first>";

	if ($num > 1)
		echo "<prev show='true'>$prev</prev>";
	else
		echo "<prev show='false'>none</prev>";

	if ($num < $page_max - 1)
		echo "<next show='true'>$next</next>";
	else
		echo "<next show='false'>none</next>";

	$last = $page_max - 1;

	if ($num < $page_max-1)
		echo "<last show='true'>$last</last>";
	else
		echo "<last show='false'>none</last>";
	
	/*
	 * Full Request
	 */
    if (isset($csv_flag) && ($csv_flag == 1))
    	$req .= " ORDER BY ctime DESC,log_id DESC LIMIT 0,64000"; //limit a little less than 2^16 which is excel maximum number of lines
    else
    	$req .= " ORDER BY ctime DESC,log_id DESC LIMIT $lstart,$limit";

	$DBRESULT =& $pearDBO->query($req);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	
	$cpts = 0;
	while ($log =& $DBRESULT->fetchRow()) {
		
		echo "<line><msg_type>".$log["msg_type"]."</msg_type>";
		echo ($log["msg_type"] > 1) ? "<retry></retry>" : "<retry>".$log["retry"]."</retry>";
		echo ($log["msg_type"] == 2 || $log["msg_type"] == 3) ? "<type>NOTIF</type>" : "<type>".$log["type"]."</type>";

		# Color initialisation for services and hosts status
		$color = '';
		if ($log["msg_type"] == 0 || $log["msg_type"] == 2)
			$color = $tab_color_service[$log["status"]];
		if ($log["msg_type"] == 1 || $log["msg_type"] == 3)
			$color = $tab_color_host[$log["status"]];

		# Variable initialisation to color "INITIAL STATE" on envent logs
        if ($log["msg_type"] == 8)
        	$color = $tab_color_service[$log["status"]];
        if ($log["msg_type"] == 9)
        	$color = $tab_color_host[$log["status"]];
        if($log["output"] == "" && $log["status"] != "")
        	$log["output"] = "INITIAL STATE";

		echo '<status color="'.$color.'">'.$log["status"].'</status>';
		echo "<service_description>".$log["service_description"]."</service_description>";
		echo "<host_name>".$log["host_name"]."</host_name>";
		echo "<class>".$tab_class[$cpts % 2]."</class>";
		echo "<date>".date(_("Y/m/d"), $log["ctime"])."</date>";
		echo "<time>".date(_("H:i:s"), $log["ctime"])."</time>";
		echo "<output><![CDATA[".$log["output"]."]]></output>";
		echo "<contact><![CDATA[".$log["notification_contact"]."]]></contact>";
		echo "<contact_cmd><![CDATA[".$log["notification_cmd"]."]]></contact_cmd>";
		echo "</line>";
		$cpts++;
	}

	echo "<lang>";
	/*
	 * Translation for Menu.
	 */
	echo "<ty>"._("Type")."</ty>";
	echo "<n>"._("Notifications")."</n>";
	echo "<a>"._("Alerts")."</a>";
	echo "<e>"._("Errors")."</e>";
	echo "<s>"._("Services")."</s>";
	echo "<do>"._("Down")."</do>";
	echo "<up>"._("Up")."</up>";
	echo "<un>"._("Unreachable")."</un>";
	echo "<w>"._("Warning")."</w>";
	echo "<ok>"._("Ok")."</ok>";
	echo "<cr>"._("Critical")."</cr>";
	echo "<uk>"._("Unknown")."</uk>";
	echo "<oh>"._("Hard Only")."</oh>";
	/*
	 * Translation for tables.
	 */
	echo "<d>"._("Day")."</d>";
	echo "<t>"._("Time")."</t>";
	echo "<h>"._("Host")."</h>";
	echo "<s>"._("Status")."</s>";
	echo "<T>"._("Type")."</T>";
	echo "<R>"._("Retry")."</R>";
	echo "<o>"._("Output")."</o>";
	echo "<c>"._("Contact")."</c>";
	echo "<C>"._("Cmd")."</C>";
	
	echo "</lang>";
	echo "</root>";
?>