<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";
	include_once $centreon_path . "www/include/reporting/dashboard/common-Func.php";
	require_once $centreon_path . "www/class/centreonUser.class.php";
	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonDuration.class.php";
	include_once $centreon_path . "www/include/reporting/dashboard/DB-Func.php";

	session_start();
	/*
	 * DB Connexion
	 */
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");

    $sid = session_id();
	if (!empty($sid) && isset($_SESSION['centreon'])){
	    $oreon = $_SESSION['centreon'];
        $query = "SELECT user_id FROM session WHERE user_id = '".$pearDB->escape($oreon->user->user_id)."'";
        $res = $pearDB->query($query);
        if (!$res->numRows()) {
            get_error('bad session id');
        }
	} else {
		get_error('need session id!');
	}

	isset ($_GET["servicegroup"]) ? $id = htmlentities($_GET["servicegroup"], ENT_QUOTES, "UTF-8") : $id = "NULL";
	isset ($_POST["servicegroup"]) ? $id = htmlentities($_POST["servicegroup"], ENT_QUOTES, "UTF-8") : $id = $id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
	$end_date = htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
	$servicegroup_name = getServiceGroupNameFromId($id);

	/*
	 * file type setting
	 */
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-Type: application/octet-stream");
	header("Content-disposition: filename=".$servicegroup_name.".csv");

	echo _("ServiceGroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $servicegroup_name.";".date(_("d/m/Y H:i:s"), $start_date)."; ".date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date)."s\n\n";
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
	echo _("Host").";"._("Service").";"._("OK Time").";"._("OK Mean Time").";"._("OK Alerts"). ";".
										_("WARNING Time").";"._("WARNING Mean Time").";"._("WARNING Alerts"). ";".
										_("CRITICAL Time").";"._("CRITICAL Mean Time").";"._("CRITICAL Alerts"). ";".
										_("UNKNOWN Time").";"._("UNKNOWN Mean Time").";"._("UNKNOWN Alerts"). ";".
										_("UNDETERMINED Time").";"._("UNDETERMINED Mean Time").";"._("UNDETERMINED Alerts")."\n";
	foreach ($stats as $key => $tab) {
		if ($key != "average") {
			echo $tab["HOST_NAME"]. ";".$tab["SERVICE_DESC"].";".$tab["OK_TP"]. "%;".$tab["OK_MP"]. "%;".$tab["OK_A"].
							  ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_MP"]. "%;".$tab["WARNING_A"].
							  ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_MP"]. "%;".$tab["CRITICAL_A"].
							  ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_MP"]. "%;".$tab["UNKNOWN_A"].
							  ";".$tab["UNDETERMINED_TP"]. "%;;\n";
		}
	}
	echo "\n\n";

	/*
	 * Services group stats evolution
	 */
	echo _("Day").";"._("Duration").";"
				   ._("OK Mean Time").";"._("OK Alert").";"
				   ._("Warning Mean Time").";"._("Warning Alert").";"
				   ._("Unknown Mean Time").";"._("Unknown Alert").";"
				   ._("Critical Mean Time").";"._("Critical Alert").";"
                   ._("Day")."\n";
	$str = "";
	$request = "SELECT `service_service_id` FROM `servicegroup_relation` WHERE `servicegroup_sg_id` = '".$id."'";
	$DBRESULT = $pearDB->query($request);
	while ($sg = $DBRESULT->fetchRow()) {
		if ($str != "") {
			$str .= ", ";
		}
		$str .= "'" . $sg["service_service_id"] . "'";
	}
	$DBRESULT->free();
	if ($str == "") {
		$str = "''";
	}
	unset($sg);
	unset($DBRESULT);

	$request =  "SELECT `date_start`, `date_end`, sum(`OKnbEvent`) as OKnbEvent, sum(`CRITICALnbEvent`) as CRITICALnbEvent,".
				" sum(`WARNINGnbEvent`) as WARNINGnbEvent, sum(`UNKNOWNnbEvent`) as UNKNOWNnbEvent, ".
				"avg( `OKTimeScheduled` ) as OKTimeScheduled, ".
				"avg( `WARNINGTimeScheduled` ) as WARNINGTimeScheduled, ".
				"avg( `UNKNOWNTimeScheduled` ) as UNKNOWNTimeScheduled, ".
				"avg( `CRITICALTimeScheduled` ) as CRITICALTimeScheduled ".
				"FROM `log_archive_service` WHERE `service_id` IN (".$str.") " .
				"AND `date_start` >= '".$start_date."' " .
				"AND `date_end` <= '".$end_date."' " .
				"GROUP BY `date_end`, `date_start` order by `date_start` desc";
	$res = $pearDBO->query($request);
	$statesTab = array("OK", "WARNING", "CRITICAL", "UNKNOWN");
	while ($row = $res->fetchRow()) {
		$duration = $row["date_end"] - $row["date_start"];

		/* Percentage by status */
		$duration = $row["OKTimeScheduled"] + $row["WARNINGTimeScheduled"] + $row["UNKNOWNTimeScheduled"]
					+ $row["CRITICALTimeScheduled"];
		$row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
		$row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
		$row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
		$row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);

		echo $row["date_start"].";".$duration."s;".
		 	$row["OK_MP"]."%;".$row["OKnbEvent"].";".
		 	$row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";".
		 	$row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";".
		 	$row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";".
            date("Y-m-d H:i:s", $row["date_start"]).";\n";
	}
	$res->free();

?>