<?
/*
 * Created on 8 mars 07 by Cedrick Facon
 * Oreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/gpl.txt
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

	# pagination
	# set limit & num
	$DBRESULT =& $pearDB->query("SELECT maxViewMonitoring FROM general_opt LIMIT 1");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());		

	# pagination
	include("./include/common/autoNumLimit.php");
	
	$attrsTextDate 	= array("size"=>"11", "style"=>"font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsTextHour 	= array("size"=>"5", "style"=>"font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsText 		= array("size"=>"30", "style" => "font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$attrsText2 	= array("size"=>"60", "style" => "font-family:Verdana, Tahoma;font-size:9px;height:13px;border: 0.5px solid gray;");
	$inputstyle		= array("style"=>"font-family:Verdana, Tahoma;font-size:9px;width:130px;height:13px;border: 0.5px solid gray;");
	$attrsAdvSelect = array("style"=>"width:200px; height:100px;");
	
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$tab_type = array("0" => "SOFT", "1" => "HARD");
	$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");
	
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$tab = array ("contact_email" => "oreon");

	$sort_type = array(	""=>NULL,"host_name" => $lang['m_log_Host_name']);
	if (!isset($_GET["o"]) || (isset($_GET["o"]) && ($_GET["o"] == "alerts_svc" || $_GET["o"] == ""))) 
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
	
	if (!isset($end_fomated) && !isset($start_fomated)){
		$end_formated = $end;
		$start_formated = $start;	
	}
	
	if ($isRestreint){
		$lcaHostByName = getLcaHostByName($pearDB);
		$lcaHostByNameStr = getLCAHostStr($lcaHostByName["LcaHost"]);
		$restrict = " AND `host_name` IN ($lcaHostByNameStr) ";
		//print $lcaHostByNameStr;
	} else
		$restrict = "";
	
	$alerts = array();	
	if (isset($_GET["o"]) && $_GET["o"] == "alerts_host"){
		if (isset($_GET["search1"]) && isset($_GET["sort_type1"]) && $_GET["search1"] && $_GET["sort_type1"])
			$sort_str1 = " AND (`".$_GET["sort_type1"]."` LIKE '%".$_GET["search1"]."%' ";
		else 
			$sort_str1 = "";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] == $_GET["sort_type1"])
			$sort_str2 = " OR ";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] != $_GET["sort_type1"] && $_GET["search2"] != "")
			$sort_str2 = " AND ";
		else if ($sort_str1 && ($_GET["sort_type2"] == "" || $_GET["search2"] == ""))
			$sort_str1 .= ") ";
			
		if (isset($_GET["search2"]) && isset($_GET["sort_type2"]) && $_GET["search2"] && $_GET["sort_type2"])
			$sort_str2 .= " `".$_GET["sort_type2"]."` LIKE '%".$_GET["search2"]."%') ";
		else 
			$sort_str2 = "";

		$req = "SELECT ctime FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type = '1' ".$sort_str1.$sort_str2.$restrict;
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

		$req = "SELECT ctime,status,host_name,output,retry,type FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type = '1' ".$sort_str1.$sort_str2.$restrict."ORDER BY log_id DESC , ctime DESC LIMIT $lstart,$limit";
		$DBRESULT =& $pearDBO->query($req);
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
	    for ($cpt = 0;$DBRESULT->fetchInto($log);$cpt++){
			$log["status"] != "UP" ? $class = "list_down" : $class = $tab_class[$cpt % 2];
			$alerts[$cpt] = array(	"class"=>$class, 
									"date"=> date($lang["date_format"], $log["ctime"]), 
									"time" => date($lang["time_format"], $log["ctime"]), 
									"status" => $log["status"], 
									"type" => $log["type"], 
									"host_name" => $log["host_name"], 
									"output" => $log["output"], 
									"retry" => $log["retry"], 
									"background" => $oreon->optGen["color_".strtolower($log["status"])]);
	    }
	} else if (isset($_GET["o"]) && $_GET["o"] == "alerts_svc"){
		if (isset($_GET["search1"]) && isset($_GET["sort_type1"]) && $_GET["search1"] && $_GET["sort_type1"])
			$sort_str1 = " AND (`".$_GET["sort_type1"]."` LIKE '%".$_GET["search1"]."%' ";
		else 
			$sort_str1 = "";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] == $_GET["sort_type1"])
			$sort_str2 = " OR ";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] != $_GET["sort_type1"] && $_GET["search2"] != "")
			$sort_str2 = " AND ";
		else if ($sort_str1 && ($_GET["sort_type2"] == "" || $_GET["search2"] == ""))
			$sort_str1 .= ") ";
			
		if (isset($_GET["search2"]) && isset($_GET["sort_type2"]) && $_GET["search2"] && $_GET["sort_type2"])
			$sort_str2 .= " `".$_GET["sort_type2"]."` LIKE '%".$_GET["search2"]."%') ";
		else 
			$sort_str2 = "";

		$req = "SELECT ctime FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type = '0' ".$sort_str1.$sort_str2;
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
			
		$DBRESULT =& $pearDBO->query("SELECT ctime,status,host_name,service_description,output,retry,type FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type = '0' ".$sort_str1.$sort_str2.$restrict."ORDER BY log_id DESC , ctime DESC LIMIT $lstart,$limit");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
	    for ($cpt = 0;$DBRESULT->fetchInto($log);$cpt++){
			$log["status"] == "CRITICAL" ? $class = "list_down" : $class = $tab_class[$cpt % 2];
			$alerts[$cpt] = array(	"class"=>$class, 
									"date"=>date($lang["date_format"], $log["ctime"]), 
									"time" => date($lang["time_format"], $log["ctime"]), 
									"status" => $log["status"], 
									"type" => $log["type"], 
									"host_name" => $log["host_name"],
									"service_description" => $log["service_description"], 
									"output" => $log["output"], 
									"retry" => $log["retry"], 
									"background" => $oreon->optGen["color_".strtolower($log["status"])]);
	    }
	} else if (!isset($_GET["o"]) || (isset($_GET["o"]) && !$_GET["o"])) {
		if (isset($_GET["search1"]) && isset($_GET["sort_type1"]) && $_GET["search1"] && $_GET["sort_type1"])
			$sort_str1 = " AND (`".$_GET["sort_type1"]."` LIKE '%".$_GET["search1"]."%' ";
		else 
			$sort_str1 = "";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] == $_GET["sort_type1"])
			$sort_str2 = " OR ";
		if (isset($_GET["sort_type2"]) && isset($_GET["sort_type1"]) && $_GET["sort_type2"] != $_GET["sort_type1"] && $_GET["search2"] != "")
			$sort_str2 = " AND ";
		else if ($sort_str1 && ($_GET["sort_type2"] == "" || $_GET["search2"] == ""))
			$sort_str1 .= ") ";
			
		if (isset($_GET["search2"]) && isset($_GET["sort_type2"]) && $_GET["search2"] && $_GET["sort_type2"])
			$sort_str2 .= " `".$_GET["sort_type2"]."` LIKE '%".$_GET["search2"]."%') ";
		else 
			$sort_str2 = "";

		$req = "SELECT ctime FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type <= '1' ".$sort_str1.$sort_str2.$restrict;
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
			
		$DBRESULT =& $pearDBO->query("SELECT ctime,status,host_name,service_description,output,retry,type FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type <= '1' ".$sort_str1.$sort_str2.$restrict."ORDER BY log_id DESC, ctime DESC LIMIT $lstart,$limit");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
	    for ($cpt = 0;$DBRESULT->fetchInto($log);$cpt++){
			if (isset($log["service_description"]) && !$log["service_description"]){
				$log["status"] != "UP" ? $class = "list_down" : $class = $tab_class[$cpt % 2];
				$alerts[$cpt] = array(	"class"=>$class, 
										"date"=>date($lang["date_format"], $log["ctime"]), 
										"time" => date($lang["time_format"], $log["ctime"]), 
										"status" => $log["status"], 
										"type" => $log["type"], 
										"host_name" => $log["host_name"], 
										"output" => $log["output"], 
										"retry" => $log["retry"], 
										"background" => $oreon->optGen["color_".strtolower($log["status"])]);
			} else { 
				$log["status"] == "CRITICAL" ? $class = "list_down" : $class = $tab_class[$cpt % 2];
				$alerts[$cpt] = array(	"class"=>$class, 
										"date"=>date($lang["date_format"], $log["ctime"]), 
										"time" => date($lang["time_format"], $log["ctime"]), 
										"status" => $log["status"], 
										"type" => $log["type"], 
										"host_name" => $log["host_name"],
										"service_description" => $log["service_description"], 
										"output" => $log["output"], 
										"retry" => $log["retry"], 
										"background" => $oreon->optGen["color_".strtolower($log["status"])]);
			}
	    }
	}
	$tab_value = array("end"=> date("m/d/Y", $end), "start" =>date("m/d/Y", $start), "end_time"=> date($lang["time_formatWOs"], $end), "start_time" =>date($lang["time_formatWOs"], $start));
	
	$form->addElement('text', 'start', $lang["m_from"], $attrsTextDate);
	$form->addElement('text', 'start_time', $lang["m_from"], $attrsTextHour);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	
	$form->addElement('text', 'end', $lang["m_to"], $attrsTextDate);
	$form->addElement('text', 'end_time', $lang["m_to"], $attrsTextHour);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	
	$form->addElement('text', 'search1', $lang["m_log_search1"], $inputstyle);
    $form->addElement('text', 'search2', $lang["m_log_search1"], $inputstyle);
    
    $form->addElement('select', 'sort_type1', $lang["m_log_select1"], $sort_type);
   	$form->addElement('select', 'sort_type2', $lang["m_log_select2"], $sort_type);   	
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
	
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign("alerts", $alerts);
	$tpl->assign("lang", $lang);
	$tpl->display("viewAlerts.ihtml");
?>