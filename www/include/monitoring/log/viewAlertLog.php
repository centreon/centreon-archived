<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/
	if (!isset($oreon))
		exit();
	
	$lcaHostByName = getLcaHostByName($pearDB);
	
	function getLogData($time_event, $host, $service, $status, $output, $type){
		global $lang;
		$tab["time"] = date($lang["header_format"], $time_event);
		$tab["host"] = $host;
		$tab["service"] = $service;
		$tab["status"] = $status;
		$tab["output"] = $output;
		$tab["type"] = $type;
		return $tab ;
	}

	include("./include/monitoring/log/choose_log_file.php");

	$log = NULL;	
	$tab_log = array();	
	if (isset($_POST["file"]) && is_file($oreon->Nagioscfg["log_archive_path"] . $_POST["file"])){
		$log = fopen($oreon->Nagioscfg["log_archive_path"] . $_POST["file"], "r");
	} else {
		if (file_exists($oreon->Nagioscfg["log_file"]) && !($log = fopen($oreon->Nagioscfg["log_file"], "r")))
			echo $lang["pel_cant_open"] . $oreon->Nagioscfg["log_file"] . "<br>";
	}
	if ($log)
		for ($i = 0; $str = fgets($log); $i++){
			if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){
				$time_event = $matches[1];
				$res = preg_split("/:/", $matches[2], 2);
				if (isset($res[1])) 
					$res1 = preg_split("/;/", $res[1]);			
				$type = $res[0];
				if (isset($_POST["host"]) && strlen($_POST["host"])) {
					$res1[0] = str_replace(" ", "", $res1[0]);
					if (!strncmp($type, "HOST ALERT", 10) && !strcmp($_POST["host"], $res1[0]) && IsHostReadable($lcaHostByName, $res1[0]))
						$tab_log[$i] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);
					else if (!strncmp($type, "SERVICE ALERT", 13) && !strcmp($_POST["host"], $res1[0])&& IsHostReadable($lcaHostByName, $res1[0]))
						$tab_log[$i] = getLogData($time_event, $res1[0], $res1[1], $res1[2], $res1[5], $type);
				} else {
					if (!strncmp($type, "HOST ALERT", 10)&& IsHostReadable($lcaHostByName, $res1[0]))
						$tab_log[$i] = getLogData($time_event, $res1[0], "", $res1[1], $res1[4], $type);
					else if (!strncmp($type, "SERVICE ALERT", 13)&& IsHostReadable($lcaHostByName, $res1[0]))
						$tab_log[$i] = getLogData($time_event, $res1[0], $res1[1], $res1[2], $res1[5], $type);
				
				}
			}
		}

	if (isset($tab_log) && $tab_log)
		krsort($tab_log);

	$path = "./include/monitoring/log/";
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "templates/");
	
	#Apply a template definition			
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);	

	$tpl->assign('o', $o);		
	$tpl->assign('form', $renderer->toArray());	
	$tpl->assign('lang', $lang);			
	$tpl->assign("tab_log", $tab_log);
	$tpl->assign("p", $p);
	$tpl->display("viewAlertLog.ihtml");
?>
