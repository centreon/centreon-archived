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


	isset ($_GET["hostgroup"]) ? $mhostgroup = $_GET["hostgroup"] : $mhostgroup = NULL;
	isset ($_POST["hostgroup"]) ? $mhostgroup = $_POST["hostgroup"] : $mhostgroup = $mhostgroup;

	require_once $centreon_path . "www/class/other.class.php";
	require_once $centreon_path . "www/include/common/common-Func.php";
	require_once $centreon_path . "www/include/common/common-Func-ACL.php";

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	require_once $centreon_path."www/include/reporting/dashboard/dataEngine/HostGroupLog.php";
	
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$mhostgroup.".csv");


	echo _("Hostgroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $mhostgroup."; ".$start_date_select."; ".$end_date_select."; ". Duration::toString($ed - $sd) ."\n";
	echo "\n";

	echo _("Status").";"._("Time").";"._("Total Time").";"._("Known Time")."; "._("Alert")."\n";
	foreach ($tab_resume as $tab) {
		echo $tab["state"]. ";".$tab["time"]. ";".$tab["pourcentTime"]. ";".$tab["pourcentkTime"]. ";".$tab["nbAlert"]. ";\n";
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
		 	$report["uptime"].";".$report["pup"].";".$report["UPnbEvent"].";".
		 	$report["downtime"].";".$report["pdown"].";".$report["DOWNnbEvent"].";".
		 	$report["unreachalbetime"].";".$report["punreach"].";".$report["UNREACHABLEnbEvent"].";".
		 	$report["undeterminatetime"].";".$report["pundet"].";\n";
	}

?>