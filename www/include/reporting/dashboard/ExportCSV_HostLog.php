<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
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
		}else
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	$path = "./include/reporting/dashboard";
	require_once '../../../class/other.class.php';
	require_once '../../common/common-Func.php';
	require_once '../../common/common-Func-ACL.php';

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	require_once 'HostLog.php';

	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$mhost.".csv");


	echo _("Host").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $mhost."; ".$start_date_select."; ".$end_date_select."; ". Duration::toString($ed - $sd) ."\n";
	echo "\n";

	echo _("Status").";"._("Time").";"._("Total Time").";"._("Known Time")."; "._("Alert")."\n";
	foreach ($tab_resume as $tab) {
		echo $tab["state"]. ";".$tab["time"]. ";".$tab["pourcentTime"]. ";".$tab["pourcentkTime"]. ";".$tab["nbAlert"]. ";\n";
	}
	echo "\n";
	echo _("Service").";"._("OK")."; "._("OK")." Alert;"
				   ._("Warning")."; "._("Warning")." Alert;"
				   ._("Unknown")."; "._("Unknown")." Alert;"
				   ._("Critical")."; "._("Critical")." Alert;"
				   ._("Undetermined").";\n";
					
	foreach ($tab_svc as $tab) {
		echo $tab["svcName"]. ";".$tab["PtimeOK"]. "%;".$tab["OKnbEvent"].
							  ";".$tab["PtimeWARNING"]. "%;".$tab["WARNINGnbEvent"].
							  ";".$tab["PtimeCRITICAL"]. "%;".$tab["CRITICALnbEvent"].
							  ";".$tab["PtimeUNKNOWN"]. "%;".$tab["UNKNOWNnbEvent"].
							  ";".$tab["PtimeNONE"]. "%;;\n";
	}

	echo "\n";
	echo "\n";

	echo _("Day").";"._("Duration").";".
		 _("Up")." "._("Time").";"._("Up").";"._("Up")." "._("Alert").";".
		 _("Down")." "._("Time").";"._("Down").";"._("Down")." "._("Alert").";".
		 _("Unreachable")." "._("Time").";"._("Unreachable").";"._("Unreachable")." "._("Alert").";".
		 _("Undetermined")." "._("Time").";"._("Undetermined").";\n";

	foreach ($tab_report as $day => $report) {
		echo $day.";".$report["duration"].";".
		 	$report["uptime"].";".$report["pup"]."%;".$report["UPnbEvent"].";".
		 	$report["downtime"].";".$report["pdown"]."%;".$report["DOWNnbEvent"].";".
		 	$report["unreachalbetime"].";".$report["punreach"]."%;".$report["UNREACHABLEnbEvent"].";".
		 	$report["undeterminatetime"].";".$report["pundet"]."%;\n";
	}
	
	/*
	echo "<pre>";
	print_r($tab_report);
	echo "</pre>";
	*/
?>