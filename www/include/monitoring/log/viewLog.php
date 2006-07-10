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
	
	# Init Logo table
	
	$tab_logo = array(	"HOST NOTIFICATION" => "./img/icones/16x16/mail_attachment.gif", 
						"SERVICE NOTIFICATION" => "./img/icones/16x16/mail_attachment.gif",  
						"HOST ALERT-UP" => "./img/icones/12x12/recovery.gif", 
						"HOST ALERT-DOWN" => "./img/icones/12x12/alert.gif", 
						"HOST ALERT-UNREACHABLE" => "./img/icones/12x12/alert.gif", 
						"SERVICE ALERT-OK" => "./img/icones/12x12/recovery.gif",
						"SERVICE ALERT-WARNING" => "./img/icones/12x12/alert.gif", 
						"SERVICE ALERT-UNKNOWN" => "./img/icones/12x12/alert.gif", 
						"SERVICE ALERT-CRITICAL" => "./img/icones/12x12/alert.gif", 
						"EXTERNAL COMMAND" => "./img/icones/14x14/undo.gif", 
						"CURRENT SERVICE STATE" => "./img/icones/12x12/info.gif", 
						"CURRENT HOST STATE" => "./img/icones/12x12/info.gif");
	
	function getLogData($time_event, $data){
		global $lang, $tab_logo;
		$tab["time"] = date($lang["header_format"], $time_event);
		$tab_data = split("\:", $data);
		if (isset($tab_logo[$tab_data["0"]]))
			$tab["logo"] = $tab_logo[$tab_data["0"]];
		else {
			if (isset($tab_data["1"])){
				$tab2 = split("\;", $tab_data["1"]);
				if (isset($tab2[2]) && isset($tab_logo[$tab_data["0"]."-".$tab2[2]]))
					$tab["logo"] = $tab_logo[$tab_data["0"]."-".$tab2[2]];
				else if (isset($tab2[1]) && isset($tab_logo[$tab_data["0"]."-".$tab2[1]]))
					$tab["logo"] = $tab_logo[$tab_data["0"]."-".$tab2[1]];
				else
					$tab["logo"] = "";	
			}
		}
		$tab["data"] = $data;
		return $tab ;
	}
	
	include("./include/monitoring/log/choose_log_file.php");
	
	if (isset($_POST["file"]) && is_file($oreon->Nagioscfg["log_archive_path"] . $_POST["file"]))
		$log = fopen($oreon->Nagioscfg["log_archive_path"] . $_POST["file"], "r");
	else {
		if (file_exists($oreon->Nagioscfg["log_file"]) && !($log = fopen($oreon->Nagioscfg["log_file"], "r")))
			echo $lang["pel_cant_open"] . $oreon->Nagioscfg["log_file"] . "<br>";
	}
	
	for ($i = 0; $str = fgets($log); $i++){
		if (preg_match("/^\[([0-9]*)\] (.+)/", $str, $matches)){
			$time_event = $matches[1];
			$tab_log[$i] = getLogData($time_event, $matches[2]);
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
	
	$tpl->assign('form', $renderer->toArray());	
	$tpl->assign('o', $o);		
		
	$tpl->assign("tab_log", $tab_log);
	$tpl->assign('lang', $lang);			
	$tpl->assign("p", $p);
	$tpl->display("viewLog.ihtml");
?>
