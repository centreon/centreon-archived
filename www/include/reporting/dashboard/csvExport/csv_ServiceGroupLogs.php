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
	require_once $centreon_path."www/class/other.class.php";
	require_once $centreon_path."www/include/common/common-Func-ACL.php";
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
	isset ($_GET["servicegroup"]) ? $id = $_GET["servicegroup"] : $id = "NULL";
	isset ($_POST["servicegroup"]) ? $id = $_POST["servicegroup"] : $id = $id;
	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = $dates[0];
	$end_date = $dates[1];
	$servicegroup_name = getServiceGroupNameFromId($id);
	/*
	 * file type setting
	 */
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$servicegroup_name.".csv");

	echo _("ServiceGroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $servicegroup_name.";".$start_date."; ".$end_date."; ".($end_date - $start_date)."\n\n\n";
	/*
     * Getting service group start
     */
	echo _("Status").";"._("Time").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
   	$reportingTimePeriod = getreportingTimePeriod();
	$stats = array();
	$stats = getLogInDbForServicesGroup($id, $start_date, $end_date, $reportingTimePeriod);
	echo _("OK").";".$stats["average"]["OK_TP"]."%;".$stats["average"]["OK_MP"]. "%;".$stats["average"]["OK_A"].";\n";
	echo _("WARNING").";".$stats["average"]["WARNING_TP"]."%;".$stats["average"]["WARNING_MP"]. "%;".$stats["average"]["WARNING_A"].";\n";
	echo _("CRITICAL").";".$stats["average"]["CRITICAL_TP"]."%;".$stats["average"]["CRITICAL_MP"]. "%;".$stats["average"]["CRITICAL_A"].";\n";
	echo _("UNKNOWN").";".$stats["average"]["UNKNOWN_TP"]."%;".$stats["average"]["UNKNOWN_MP"]. "%;".$stats["average"]["UNKNOWN_A"].";\n";
	echo _("UNDETERMINED").";".$stats["average"]["UNDETERMINED_TP"]."%;;;\n\n";
	/*
	 * Services group services stats
	 */
	echo _("Host").";"._("Service").";"._("OK Time").";"._("OK Mean Time").";"._("OK Alerts").
										_("WARNING Time").";"._("WARNING Mean Time").";"._("WARNING Alerts").
										_("CRITICAL Time").";"._("CRITICAL Mean Time").";"._("CRITICAL Alerts").
										_("UNKNOWN Time").";"._("UNKNOWN Mean Time").";"._("UNKNOWN Alerts").
										_("UNDETERMINED Time").";"._("UNDETERMINED Mean Time").";"._("UNDETERMINED Alerts")."\n";
	foreach ($stats as $key => $tab) {
		if ($key != "average")
			echo $tab["HOST_NAME"]. ";".$tab["SERVICE_DESC"].";".$tab["OK_TP"]. "%;".$tab["OK_MP"]. "%;".$tab["OK_A"]. 
							  ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_MP"]. "%;".$tab["WARNING_A"]. 
							  ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_MP"]. "%;".$tab["CRITICAL_A"]. 
							  ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_MP"]. "%;".$tab["UNKNOWN_A"]. 
							  ";".$tab["UNDETERMINED_TP"]. "%;".$tab["UNDETERMINED_MP"]. "%;;\n"; 
	}
	echo "\n\n";
	/*
	 * Services group stats evolution
	 */
	echo _("Day").";"._("Duration").";" 
				   ._("OK Mean Time")."; "." Alert;"
				   ._("Warning Mean Time")." Alert;"
				   ._("Unknown Mean Time")." Alert;"
				   ._("Critical Mean Time").";\n";
	$str = "";
	$request = "SELECT `service_service_id` FROM `servicegroup_relation` WHERE `servicegroup_sg_id` = '".$id."'";
	$DBRESULT = & $pearDB->query($request);
	while ($sg =& $DBRESULT->fetchRow()) {
		if ($str != "")
			$str .= ", ";
		$str .= $sg["service_service_id"]; 
	}
	unset($sg);
	unset($DBRESULT);

	$request =  'SELECT `date_start`, `date_end`, sum(`OKnbEvent`) as OKnbEvent, sum(`CRITICALnbEvent`) as CRITICALnbEvent,'.
				' sum(`WARNINGnbEvent`) as WARNINGnbEvent, sum(`UNKNOWNnbEvent`) as UNKNOWNnbEvent, '.
				'avg( `OKTimeScheduled` ) as "OKTimeScheduled", '.
				'avg( `WARNINGTimeScheduled` ) as "WARNINGTimeScheduled", '.
				'avg( `UNKNOWNTimeScheduled` ) as "UNKNOWNTimeScheduled", '.
				'avg( `CRITICALTimeScheduled` ) as "CRITICALTimeScheduled" '.
				'FROM `log_archive_service` WHERE `service_id` IN ('.$str.') group by `date_end`, `date_start` order by `date_start` desc';
	$res = & $pearDBO->query($request);
	$statesTab = array("OK", "WARNING", "CRITICAL", "UNKNOWN");
	while ($row =& $res->fetchRow()){
		$duration = $row["date_end"] - $row["date_start"];
		/* Percentage by status */
		$duration = $row["OKTimeScheduled"] + $row["WARNINGTimeScheduled"] + $row["UNKNOWNTimeScheduled"]
					+ $row["CRITICALTimeScheduled"];
		$row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
		$row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
		$row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
		$row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);
		echo $row["date_start"].";".$duration.";".
		 	$row["OK_MP"]."%;".$row["OKnbEvent"].";".
		 	$row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";".
		 	$row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";".
		 	$row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";\n";
	}

?>