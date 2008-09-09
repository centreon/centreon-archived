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

	/* getting host and service id */
	isset ($_GET["host"]) ? $host_id = $_GET["host"] : $host_id = "NULL";
	isset ($_POST["host"]) ? $host_id = $_POST["host"] : $host_id;
	isset ($_GET["service"]) ? $service_id = $_GET["service"] : $service_id = "NULL";
	isset ($_POST["service"]) ? $service_id = $_POST["service"] : $service_id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = $dates[0];
	$end_date = $dates[1];
	$host_name = getHostNameFromId($host_id);
	$service_description = getServiceDescriptionFromId($service_id);
	/*
	 * file type setting
	 */
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$host_name. "_" .$service_description.".csv");

	echo _("Host").";"._("Service").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $host_name."; ".$service_description."; ".$start_date."; ".$end_date."; ".($end_date - $start_date)."\n";
	echo "\n";

	echo _("Status").";"._("Time").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	$reportingTimePeriod = getreportingTimePeriod();
	$serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod) ;
	echo "OK;".$serviceStats["OK_T"].";".$serviceStats["OK_TP"]."%;".$serviceStats["OK_MP"]. "%;".$serviceStats["OK_A"].";\n";
	echo "WARNING;".$serviceStats["WARNING_T"].";".$serviceStats["WARNING_TP"]."%;".$serviceStats["WARNING_MP"]. "%;".$serviceStats["WARNING_A"].";\n";
	echo "CRITICAL;".$serviceStats["CRITICAL_T"].";".$serviceStats["CRITICAL_TP"]."%;".$serviceStats["CRITICAL_MP"]. "%;".$serviceStats["CRITICAL_A"].";\n";
	echo "UNKNOWN;".$serviceStats["UNKNOWN_T"].";".$serviceStats["UNKNOWN_TP"]."%;".$serviceStats["UNKNOWN_MP"]. "%;".$serviceStats["UNKNOWN_A"].";\n";
	echo "UNDETERMINED;".$serviceStats["UNDETERMINED_T"].";".$serviceStats["UNDETERMINED_TP"]."%;;;\n";
	echo "\n";
	echo "\n";

	/*
	 * Getting evolution of service stats in time
	 */
	echo _("Day").";"._("Duration").";"
				   ._("OK")." "._("Time")."; "._("OK")."; "._("OK")." Alert;"
				   ._("Warning")." "._("Time")."; "._("Warning").";"._("Warning")." Alert;"
				   ._("Unknown")." "._("Time")."; "._("Unknown").";"._("Unknown")." Alert;"
				   ._("Critical")." "._("Time")."; "._("Critical").";"._("Critical")." Alert;\n";

	$request = "SELECT  * FROM `log_archive_service` WHERE `host_id` = '".$host_id."' AND `service_id` = ".$service_id." ORDER BY `date_start` DESC";
	$DBRESULT =& $pearDBO->query($request);
	if (PEAR::isError($DBRESULT)) 
  		die("MSQL Error : " . $DBRESULT->getDebugInfo());
	while ($row =& $DBRESULT->fetchRow()) {
		$duration = $row["date_end"] - $row["date_start"];
		/* Percentage by status */
		$duration = $row["OKTimeScheduled"] + $row["WARNINGTimeScheduled"] + $row["UNKNOWNTimeScheduled"]
					+ $row["CRITICALTimeScheduled"];
		$row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
		$row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
		$row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
		$row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);
		echo $row["date_start"].";".$duration.";".
		 	$row["OKTimeScheduled"].";".$row["OK_MP"]."%;".$row["OKnbEvent"].";".
		 	$row["WARNINGTimeScheduled"].";".$row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";".
		 	$row["UNKNOWNTimeScheduled"].";".$row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";".
		 	$row["CRITICALTimeScheduled"].";".$row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";\n";
	}
?>