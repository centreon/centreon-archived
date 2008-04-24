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

	include_once("/etc/centreon/centreon.conf.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBOdsConnect.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			$_POST["sid"] = $sid;
		} else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	require_once $centreon_path . "www/class/other.class.php";
	require_once $centreon_path . "www/include/common/common-Func.php";
	require_once $centreon_path . "www/include/common/common-Func-ACL.php";

	isset ($_GET["servicegroup"]) ? $mservicegroup = $_GET["servicegroup"] : $mservicegroup = NULL;
	isset ($_POST["servicegroup"]) ? $mservicegroup = $_POST["servicegroup"] : $mservicegroup = $mservicegroup;

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$mservicegroup.".csv");

	echo _("ServiceGroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $mservicegroup.";".$start_date_select."; ".$end_date_select."; ". Duration::toString($ed - $sd) ."\n";
	echo "\n";

	echo _("Status").";"._("Time").";"._("Total Time").";"._("Known Time")."; "._("Alert")."\n";
	foreach ($tab_resume as $tab)
		echo $tab["state"]. ";".$tab["time"]. ";".$tab["pourcentTime"]. " %;".$tab["pourcentkTime"]. ";".$tab["nbAlert"]. ";\n";
	echo "\n";


	
	echo "Hostname;Servicename;"._("OK")."; "._("OK")." Alert;"
				   ._("Warning")."; "._("Warning")." Alert;"
				   ._("Unknown")."; "._("Unknown")." Alert;"
				   ._("Critical")."; "._("Critical")." Alert;"
				   ._("Undetermined")."%;\n";

	foreach ($tab_svc as $tab) {
		echo $tab["hostName"]. ";".$tab["serviceName"]. ";".$tab["PtimeOK"]. ";%".$tab["OKnbEvent"]. 
							  ";".$tab["PtimeWARNING"]. "%;".$tab["WARNINGnbEvent"].
							  ";".$tab["PtimeCRITICAL"]. "%;".$tab["CRITICALnbEvent"]. 
							  ";".$tab["PtimeUNKNOWN"]. "%;".$tab["UNKNOWNnbEvent"].
							  ";".$tab["PtimeUNDETERMINATED"]. "%;;\n";
	}
	echo "\n\n";

	echo _("Day").";"._("Duration").";" 
				   ._("OK")." "._("Time")."; "._("OK")."; "._("OK")." Alert;"
				   ._("Warning")." "._("Time")."; "._("Warning").";"._("Warning")." Alert;"
				   ._("Unknown")." "._("Time")."; "._("Unknown").";"._("Unknown")." Alert;"
				   ._("Critical")." "._("Time")."; "._("Critical").";"._("Critical")." Alert;"
				   ._("Undetermined")." "._("Time").";\n";

	foreach ($tab_report as $day => $report) {
		echo $day.";".$report["duration"].";".
		 	$report["oktime"].";".$report["pok"]."%;".$report["OKnbEvent"].";".
		 	$report["criticaltime"].";".$report["pcritical"]."%;".$report["CRITICALnbEvent"].";".
		 	$report["warningtime"].";".$report["pwarning"]."%;".$report["WARNINGnbEvent"].";".
		 	$report["pendingtime"].";".$report["ppending"]."%;".
		 	$report["unknowntime"].";".$report["punknown"]."%;\n";
	}

?>