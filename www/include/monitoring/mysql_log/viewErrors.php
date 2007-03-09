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
	
	$attrsTextDate 	= array("size"=>"11", "style"=>"border:1;");
	$attrsTextHour 	= array("size"=>"5");
	$attrsText 		= array("size"=>"30");
	$attrsText2 	= array("size"=>"60");
	$attrsAdvSelect = array("style"=>"width: 200px; height: 100px;");
	
	$tab_class = array("0" => "list_one", "1" => "list_two");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");
	$tab_status_service = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN");
	
	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$tab = array ("contact_email" => "oreon");


	$sort_type = array(	""=>NULL,
						"date" => $lang['m_log_day'], 
						"host" => $lang['m_log_Host_name'], 
						"service" => $lang['m_log_Service_desc'], 
						"status" => $lang['m_log_status'], 
						"retry" => $lang['m_log_retry'],
						"infos" => $lang['m_log_informations']);

	$form->addElement('hidden', 'p', $p);
	$form->addElement('hidden', 'o', $o);
	
	if (isset($_GET["end"]) && isset($_GET["start"])){
		$_GET["end"] .= " ".$_GET["end_time"];
		$_GET["start"] .= " ".$_GET["start_time"];
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)\ ([0-9]*):([0-9]*)/", $_GET["start"] , $matches);
		$_GET["start"] = mktime($matches[4], $matches[5], "0", $matches[1], $matches[2], $matches[3]) ;
		preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)\ ([0-9]*):([0-9]*)/", $_GET["end"], $matches);
		$_GET["end"] = mktime($matches[4], $matches[5], "59", $matches[1], $matches[2], $matches[3]);
	}
	
	isset($_GET["end"]) && $_GET["end"] ? $end = $_GET["end"] : $end = time();
	isset($_GET["start"]) && $_GET["start"] ? $start = $_GET["start"] : $start = time() - (60*60*24);
	
	$alerts = array();	
	if (isset($_GET["search"]) && $_GET["search"])
		$req = "SELECT * FROM log WHERE  `output` LIKE '%".$_GET["search"]."%' AND ctime > '$start' AND ctime <= '$end' AND msg_type = '4' ORDER BY log_id DESC , ctime DESC";
	else
		$req = "SELECT * FROM log WHERE ctime > '$start' AND ctime <= '$end' AND msg_type = '4' ORDER BY log_id DESC , ctime DESC";
	$DBRESULT =& $pearDBO->query($req);
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getMessage();
    for ($cpt = 0;$DBRESULT->fetchInto($log);$cpt++){	
		$alerts[$cpt] = array(	"class"=>$tab_class[$cpt % 2], 
								"date"=>date($lang["date_format"], $log["ctime"]), 
								"time" => date($lang["time_format"], $log["ctime"]), 
								"output" => $log["output"]);
    }
	
	$tab_value = array("end"=> date("m/d/Y", $end), "start" =>date("m/d/Y", $start), "end_time"=> date($lang["time_formatWOs"], $end), "start_time" =>date($lang["time_formatWOs"], $start));
	
	$form->addElement('text', 'start', $lang["m_from"], $attrsTextDate);
	$form->addElement('text', 'start_time', $lang["m_from"], $attrsTextHour);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	
	$form->addElement('text', 'end', $lang["m_to"], $attrsTextDate);
	$form->addElement('text', 'end_time', $lang["m_to"], $attrsTextHour);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));
	
	$form->addElement('text', 'search', $lang["quicksearch"], $attrsText);
   	$form->setDefaults($tab_value);
   	
   	$sub =& $form->addElement('submit', 'submit', $lang["m_log_view"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./include/monitoring/mysql_log/templates/", $tpl);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	
   	$tpl->assign('form', $renderer->toArray());
	
	$tpl->assign("alerts", $alerts);
	$tpl->assign("lang", $lang);
	$tpl->display("viewErrors.ihtml");
?>