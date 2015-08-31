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
	require_once $centreon_path . "www/class/centreonDuration.class.php";
	require_once $centreon_path . "www/class/centreonUser.class.php";
	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	include_once $centreon_path . "www/include/reporting/dashboard/DB-Func.php";

	session_start();
	/*
	 * DB connexion
	 */
	$pearDB 	= new CentreonDB();
	$pearDBO 	= new CentreonDB("centstorage");

	/*
	 * Checking session
	 */
    $sid = session_id();
	if (!empty($sid) && isset($_SESSION['centreon'])) {
        $oreon = $_SESSION['centreon'];
        $query = "SELECT user_id FROM session WHERE user_id = '".$pearDB->escape($oreon->user->user_id)."'";
        $res = $pearDB->query($query);
        if (!$res->numRows()) {
            get_error('bad session id');
        }
	} else {
		get_error('need session id!');
	}

	/*
	 * getting host id
	 */
	isset ($_GET["host"]) ? $id = htmlentities($_GET["host"], ENT_QUOTES, "UTF-8") : $id = NULL;
	isset ($_POST["host"]) ? $id = htmlentities($_POST["host"], ENT_QUOTES, "UTF-8") : $id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
	$end_date = htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
	$host_name = getHostNameFromId($id);

	/*
	 * file type setting
	 */
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-Type: application/octet-stream");
	header("Content-disposition: filename=".$host_name.".csv");

	echo _("Host").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $host_name."; ".date(_("d/m/Y H:i:s"), $start_date)."; ".date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date) ."s\n";
	echo "\n";
	echo _("Status").";"._("Duration").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	/*
	 * Getting stats on Host
	 */
	$reportingTimePeriod = getreportingTimePeriod();
	$hostStats = getLogInDbForHost($id, $start_date, $end_date, $reportingTimePeriod) ;
	echo _("DOWN").";".$hostStats["DOWN_T"]."s;".$hostStats["DOWN_TP"]."%;".$hostStats["DOWN_MP"]."%;".$hostStats["DOWN_A"].";\n";
	echo _("UP").";".$hostStats["UP_T"]."s;".$hostStats["UP_TP"]."%;".$hostStats["UP_MP"]."%;".$hostStats["UP_A"].";\n";
	echo _("UNREACHABLE").";".$hostStats["UNREACHABLE_T"]."s;".$hostStats["UNREACHABLE_TP"]."%;".$hostStats["UNREACHABLE_MP"]."%;".$hostStats["UNREACHABLE_A"].";\n";
	echo _("UNDETERMINED").";".$hostStats["UNDETERMINED_T"]."s;".$hostStats["UNDETERMINED_TP"]."%;\n";
	echo "\n";

	echo _("Service").";"._("OK")."; "._("OK")." Alert;"
				   ._("Warning")."; "._("Warning")." Alert;"
				   ._("Critical")."; "._("Critical")." Alert;"
				   ._("Unknown")."; "._("Unknown")." Alert;"
				   ._("Undetermined").";\n";
	$hostServicesStats =  getLogInDbForHostSVC($id, $start_date, $end_date, $reportingTimePeriod);
	foreach ($hostServicesStats as $tab) {
		if (isset($tab["DESCRIPTION"]) && $tab["DESCRIPTION"] != "") {
			echo $tab["DESCRIPTION"]. ";".$tab["OK_TP"]. "%;".$tab["OK_A"].
							 	 ";".$tab["WARNING_TP"]. "%;".$tab["WARNING_A"].
							 	 ";".$tab["CRITICAL_TP"]. "%;".$tab["CRITICAL_A"].
							 	 ";".$tab["UNKNOWN_TP"]. "%;".$tab["UNKNOWN_A"].
							 	 ";".$tab["UNDETERMINED_TP"]. "%;;\n";
		}
	}

	echo "\n";
	echo "\n";

	/*
	 * Evolution of host availability in time
	 */
	echo _("Day").";"._("Duration").";".
		 _("Up")." "._("Time").";"._("Up").";"._("Up")." "._("Alert").";".
		 _("Down")." "._("Time").";"._("Down").";"._("Down")." "._("Alert").";".
		 _("Unreachable")." "._("Time").";"._("Unreachable").";"._("Unreachable")." "._("Alert").
         _("Day").";\n";
	$rq = "SELECT  * " .
			"FROM `log_archive_host` " .
			"WHERE `host_id` = '".$id."' " .
			"AND `date_start` >= '".$start_date."' " .
			"AND `date_end` <= '".$end_date."' " .
			"ORDER BY `date_start` desc";
	$DBRESULT = $pearDBO->query($rq);
	while ($row = $DBRESULT->fetchRow()) {
			$duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];
			/* Percentage by status */
			$row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
			$row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
			$row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);
			echo $row["date_start"].";".$duration.";".
			 	$row["UPTimeScheduled"].";".$row["UP_MP"]."%;".$row["UPnbEvent"].";".
			 	$row["DOWNTimeScheduled"].";".$row["DOWN_MP"]."%;".$row["DOWNnbEvent"].";".
			 	$row["UNREACHABLETimeScheduled"].";".$row["UNREACHABLE_MP"]."%;".$row["UNREACHABLEnbEvent"].";".
                date("Y-m-d H:i:s", $row["date_start"]).";\n";
	}
?>