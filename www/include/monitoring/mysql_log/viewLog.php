<?php
/*
 * Created on 8 mars 07 by Cedrick Facon
 * Oreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * OREON makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * safety, contents, performance, merchantability, non-infringement or suitability for
 * any particular or intended purpose of the Software found on the OREON web site.
 * In no event will OREON be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if OREON has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 * Last Modification : 12:01:36 by julio 
*/
	if (!isset($oreon))
		exit();

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
		
	if (is_file("./DBOdsConnect.php"))
		include_once("./DBOdsConnect.php");

	include("./include/common/autoNumLimit.php");
	
	# pagination
	# set limit & num
	$DBRESULT =& $pearDB->query("SELECT maxViewMonitoring FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	$attrsTextDate 	= array("size"=>"11", "style"=>"font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsTextHour 	= array("size"=>"5", "style"=>"font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsText 		= array("size"=>"30", "style" => "font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsText2 	= array("size"=>"60", "style" => "font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$inputstyle		= array("style"=>"font-family:Verdana, Tahoma;font-size:9px;width:130px;height:13px;border: 0.5px solid gray;");
	$attrsAdvSelect = array("style"=>"width:200px; height:100px;");
	
	$tab_type = array("1" => "HARD", "0" => "SOFT");
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");
	
	$form = new HTML_QuickForm('form', 'get', "?p=".$p); # pagination use form name ='form'
	$tab = array ("contact_email" => "oreon");


	$sort_type = array(	""=>NULL,"host_name" => $lang['m_log_Host_name']);
	if (isset($_GET["o"]) && $_GET["o"] == "notif_svc") 
		$sort_type["service_description"] = $lang['m_log_Service_desc'];
	$sort_type["status"] = $lang['m_log_status'];
	$sort_type["output"] = $lang['m_log_informations'];

	$form->addElement('hidden', 'p', $p);
	$form->addElement('hidden', 'o', $o);
	
	if (isset($_GET["end"]) && !$_GET["end"])
		$_GET["end"] = time();
	if (isset($_GET["start"]) && !$_GET["start"])
		$_GET["start"] = time() - 60*60*24;
		
	if (isset($_GET["end"]) && isset($_GET["start"])){
		$start_formated = $_GET["start"];
		$end_formated = $_GET["end"];
		if (strpos($_GET["end"], "/")){
			$_GET["end"] .= " ".$_GET["end_time"];
			$_GET["start"] .= " ".$_GET["start_time"];
			preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)\ ([0-9]*):([0-9]*)/", $_GET["start"] , $matches);
			$_GET["start"] = mktime($matches[4], $matches[5], "0", $matches[1], $matches[2], $matches[3]);
			preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)\ ([0-9]*):([0-9]*)/", $_GET["end"], $matches);
			$_GET["end"] = mktime($matches[4], $matches[5], "59", $matches[1], $matches[2], $matches[3]);
		} else {
			$tab_end = split("/:/", $_GET["end_time"]);
			$tab_start = split("/:/", $_GET["start_time"]);
			$end = $_GET["end"] + $tab_end[0]*60 + $tab_end[1];
			$start = $_GET["start"] + $tab_start[0]*60 + $tab_start[1];
		}	
	}
	
	isset($_GET["end"]) && $_GET["end"] ? $end = $_GET["end"] : $end = time();
	isset($_GET["start"]) && $_GET["start"] ? $start = $_GET["start"] : $start = time() - (60*60*24);
		
	$logs = array();	
	if (isset($_GET["search1"]) && $_GET["search1"])
		$sort_str1 = " AND (`host_name` LIKE '%".$_GET["search1"]."%' OR `service_description` LIKE '%".$_GET["search1"]."%' OR `output` LIKE '%".$_GET["search1"]."%' OR `notification_cmd` LIKE '%".$_GET["search1"]."%' OR `notification_contact` LIKE '%".$_GET["search1"]."%')";
	else 
		$sort_str1 = "";

	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1";

	$DBRESULT =& $pearDBO->query($req);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$rows = $DBRESULT->numrows();
	
	include("./include/common/checkPagination.php");

	if(($num * $limit) > $rows)
		$num = round($rows / $limit) - 1;
	$lstart = $num * $limit;

	if ($lstart <= 0)
		$lstart = 0;

	$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' $sort_str1 ORDER BY log_id DESC , ctime DESC LIMIT $lstart,$limit";

	$DBRESULT =& $pearDBO->query($req);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	for ($cpts = 0;$DBRESULT->fetchInto($log);$cpts++){
		if ($log["msg_type"] == 0){ # Service Alerte
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),
								"line" => "SERVICE ALERT: ".$log["host_name"].";".$log["service_description"].";".$log["status"].";".$log["type"].";".$log["retry"].";".$log["output"]);
		} else if ($log["msg_type"] == 1){ # Host Alerte
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),
								"line" => "HOST ALERT: ".$log["host_name"].";".$log["status"].";".$log["type"].";".$log["retry"].";".$log["output"]);
		} else if ($log["msg_type"] == 2){ # Service Notifi
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),
								"line" => "SERVICE NOTIFICATION: ".$log["notification_contact"].";".$log["host_name"].";".$log["service_description"].";".$log["status"].";".$log["notification_cmd"].";".$log["output"]);
		} else if ($log["msg_type"] == 3){	# Host notifi
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),
								"line" => "HOST NOTIFICATION: ".$log["notification_contact"].";".$log["host_name"].";".$log["status"].";".$log["notification_cmd"].";".$log["output"]);
		} else if ($log["msg_type"] == 4){ # Warning
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),"line" => $log["output"]);
		} else if ($log["msg_type"] == 5){ # Others logs
			$logs[$cpts] = array("class" => $tab_class[$cpts % 2], "date"=>date($lang["date_format"], $log["ctime"]), "time" => date($lang["time_format"], $log["ctime"]),"line" => $log["output"]);
		}
	}
	$tab_value = array("end"=> date("m/d/Y", $end), "start" =>date("m/d/Y", $start), "end_time"=> date($lang["time_formatWOs"], $end), "start_time" =>date($lang["time_formatWOs"], $start));


	$form->addElement('text', 'start', $lang["m_from"], $attrsTextDate);
	$form->addElement('text', 'start_time', $lang["m_from"], $attrsTextHour);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	
	$form->addElement('text', 'end', $lang["m_to"], $attrsTextDate);
	$form->addElement('text', 'end_time', $lang["m_to"], $attrsTextHour);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	
	$form->addElement('text', 'search1', $lang["m_log_search1"], $attrsText);
    $form->addElement('select', 'sort_type1', $lang["m_log_select1"], $sort_type);    	
   	$form->setDefaults($tab_value);
   	
   	$sub =& $form->addElement('submit', 'ssubmit', $lang["m_log_view"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./include/monitoring/mysql_log/templates/", $tpl);
	
	# pagination
	$tpl->assign('limit', $limit);
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	$tpl->assign("num", $num);
	$tpl->assign("limit", $limit);
	$tpl->assign("p", $p);
	$tpl->assign('o', $o);
	# pagination
	
   	$tpl->assign('form', $renderer->toArray());
	
	$tpl->assign("logs", $logs);
	$tpl->assign("lang", $lang);
	$tpl->display("viewLog.ihtml");
?>