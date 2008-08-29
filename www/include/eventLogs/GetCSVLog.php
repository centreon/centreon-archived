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
	 
	include_once("@CENTREON_ETC@/centreon.conf.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBOdsConnect.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($session =& $res->fetchRow()){
			$_POST["sid"] = $sid;
		} else
			get_error('bad session id');
			
	} else
			get_error('need session identifiant !');		
			
	require_once $centreon_path . "www/class/other.class.php";
	require_once $centreon_path . "www/include/common/common-Func.php";
	require_once $centreon_path . "www/include/common/common-Func-ACL.php";
	
	// save of the XML flow in $flux
	$csv_flag = 1; //setting the csv_flag variable to change limit in SQL request of getODSXmlLog.php when CSV exporting
	ob_start();
	require_once $centreon_path."www/include/eventLogs/GetODSXmlLog.php";
	$flux = ob_get_contents();
	ob_end_clean();
	
	$nom = "EventLog";
		
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$nom.".csv");
	
	$xml = new SimpleXMLElement($flux);

	echo _("Begin date")."; "._("End date").";\n";
	echo date('d/m/y (H:i:s)', intval($xml->infos->start)).";".date('d/m/y (H:i:s)', intval($xml->infos->end))."\n";
	echo "\n";
	
	echo _("Type").";"._("Notification").";"._("Alert").";"._("error")."\n";
	echo ";".$xml->infos->notification.";".$xml->infos->alert.";".$xml->infos->error."\n";
	echo "\n";
	
	echo _("Host").";"._("Up").";"._("Down").";"._("Unreachable")."\n";
	echo ";".$xml->infos->up.";".$xml->infos->down.";".$xml->infos->unreachable."\n";
	echo "\n";
	
	echo _("Service").";"._("Ok").";"._("Warning").";"._("Critical").";"._("Unknown")."\n";
	echo ";".$xml->infos->ok.";".$xml->infos->warning.";".$xml->infos->critical.";".$xml->infos->unknown."\n";
	echo "\n";
	
	echo _("Day").";"._("Time").";"._("Host").";".";"._("Status").";"._("Type").";"._("Retry").";"._("Output").";"._("Contact").";"._("Cmd")."\n";
	foreach ($xml->line as $line) {
		echo $line->date.";".$line->time.";".$line->host_name.";".$line->service_description.";".$line->status.";".$line->type.";".$line->retry.";".$line->output.";".$line->contact.";".$line->contact_cmd."\n";
	}
	
	?>