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

	include_once("@CENTREON_PATH@/centreon.conf.php");
	include_once($centreon_path . "www/DBconnect.php");
	include_once($centreon_path . "www/DBOdsConnect.php");
	include_once($centreon_path . "www/include/common/common-Func.php");
	include_once($centreon_path . "www/include/reporting/dashboard/common-Func.php");
	require_once $centreon_path . "www/class/other.class.php";
	require_once $centreon_path . "www/include/common/common-Func-ACL.php";
	include_once($centreon_path . "www/include/reporting/dashboard/DB-Func.php");
	/*
	 * Checking session
	 */
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			$_POST["sid"] = $sid;
		} else
			get_error('bad session id');
	} else
		get_error('need session id!');

	/* getting host id */
	isset ($_GET["host"]) ? $id = $_GET["host"] : $id = NULL;
	isset ($_POST["host"]) ? $id = $_POST["host"] : $id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = $dates[0];
	$end_date = $dates[1];
	$host_name = getHostNameFromId($id);
	
	/*
	 * file type setting
	 */
	
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$host_name.".csv");


	echo _("Host").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $host_name."; ".$start_date."; ".$end_date."; ".($end_date - $start_date) ."\n";
	echo "\n";
	echo _("Status").";"._("Duration").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	/*
	 * Getting stats on Host
	 */
	$reportingTimePeriod = getreportingTimePeriod();
	$hostStats = getLogInDbForHost($id, $start_date, $end_date, $reportingTimePeriod) ;
	echo _("DOWN").";".$hostStats["DOWN_T"].";".$hostStats["DOWN_TP"].";".$hostStats["DOWN_MP"].";".$hostStats["DOWN_A"].";\n";
	echo _("UP").";".$hostStats["UP_T"].";".$hostStats["UP_TP"].";".$hostStats["UP_MP"].";".$hostStats["UP_A"].";\n";
	echo _("UNREACHABLE").";".$hostStats["UNREACHABLE_T"].";".$hostStats["UNREACHABLE_TP"].";".$hostStats["UNREACHABLE_MP"].";".$hostStats["UNREACHABLE_A"].";\n";
	echo _("UNDETERMINED").";".$hostStats["UNDETERMINED_T"].";".$hostStats["UNDETERMINED_TP"].";".$hostStats["UNDETERMINED_MP"].";".$hostStats["UNDETERMINED_A"].";\n";	
	echo "\n";
	
	echo _("Service").";"._("OK")."; "._("OK")." Alert;"
				   ._("Warning")."; "._("Warning")." Alert;"
				   ._("Unknown")."; "._("Unknown")." Alert;"
				   ._("Critical")."; "._("Critical")." Alert;"
				   ._("Undetermined").";\n";
	$hostServicesStats =  getLogInDbForHostSVC($id, $start_date, $end_date, $reportingTimePeriod);
	foreach ($hostServicesStats as $tab) {
		if (isset($tab["DESCRIPTION"]) && $tab["DESCRIPTION"] != "")
			echo $tab["DESCRIPTION"]. ";".$tab["OK_TP"]. "%;".$tab["OK_A"].
							 	 ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_A"].
							 	 ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_A"].
							 	 ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_A"].
							 	 ";".$tab["UNDETERMINED_TP"]. "%;;\n";
	}

	echo "\n";
	echo "\n";

	/*
	 * Evolution of host availability in time
	 */
	echo _("Day").";"._("Duration").";".
		 _("Up")." "._("Time").";"._("Up").";"._("Up")." "._("Alert").";".
		 _("Down")." "._("Time").";"._("Down").";"._("Down")." "._("Alert").";".
		 _("Unreachable")." "._("Time").";"._("Unreachable").";"._("Unreachable")." "._("Alert").";\n";
	$rq = 'SELECT  * FROM `log_archive_host` WHERE `host_id` = '.$id.' ORDER BY `date_start` desc';			
	$DBRESULT = & $pearDBO->query($rq);
	if (PEAR::isError($DBRESULT))
	 	die("DB ERROR : ".$DBRESULT->getDebugInfo()."<br/>");
	while ($row =& $DBRESULT->fetchRow()) {
			$duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];
			/* Percentage by status */
			$row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
			$row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
			$row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);
			echo $row["date_start"].";".$duration.";".
			 	$row["UPTimeScheduled"].";".$row["UP_MP"]."%;".$row["UPnbEvent"].";".
			 	$row["DOWNTimeScheduled"].";".$row["DOWN_MP"]."%;".$row["DOWNnbEvent"].";".
			 	$row["UNREACHABLETimeScheduled"].";".$row["UNREACHABLE_MP"]."%;".$row["UNREACHABLEnbEvent"].";\n";
	}
?>