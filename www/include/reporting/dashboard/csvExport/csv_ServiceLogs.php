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
	 * DB connexion
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

	/*
	 * getting host and service id
	 */
	isset ($_GET["host"]) ? $host_id =  htmlentities($_GET["host"], ENT_QUOTES, "UTF-8") : $host_id = "NULL";
	isset ($_POST["host"]) ? $host_id =  htmlentities($_POST["host"], ENT_QUOTES, "UTF-8") : $host_id;
	isset ($_GET["service"]) ? $service_id =  htmlentities($_GET["service"], ENT_QUOTES, "UTF-8") : $service_id = "NULL";
	isset ($_POST["service"]) ? $service_id =  htmlentities($_POST["service"], ENT_QUOTES, "UTF-8") : $service_id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date =  htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
	$end_date =  htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
	$host_name = getHostNameFromId($host_id);
	$service_description = getServiceDescriptionFromId($service_id);

	/*
	 * file type setting
	 */
	
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-Type: application/octet-stream");
	header("Content-disposition: attachment ; filename=".$host_name. "_" .$service_description.".csv");

	echo _("Host").";"._("Service").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $host_name."; ".$service_description."; ".date(_("d/m/Y H:i:s"), $start_date)."; ".date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date)."s\n";
	echo "\n";

	echo _("Status").";"._("Time").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	$reportingTimePeriod = getreportingTimePeriod();
	$serviceStats = getLogInDbForOneSVC($host_id, $service_id, $start_date, $end_date, $reportingTimePeriod) ;
	echo "OK;".$serviceStats["OK_T"]."s;".$serviceStats["OK_TP"]."%;".$serviceStats["OK_MP"]. "%;".$serviceStats["OK_A"].";\n";
	echo "WARNING;".$serviceStats["WARNING_T"]."s;".$serviceStats["WARNING_TP"]."%;".$serviceStats["WARNING_MP"]. "%;".$serviceStats["WARNING_A"].";\n";
	echo "CRITICAL;".$serviceStats["CRITICAL_T"]."s;".$serviceStats["CRITICAL_TP"]."%;".$serviceStats["CRITICAL_MP"]. "%;".$serviceStats["CRITICAL_A"].";\n";
	echo "UNKNOWN;".$serviceStats["UNKNOWN_T"]."s;".$serviceStats["UNKNOWN_TP"]."%;".$serviceStats["UNKNOWN_MP"]. "%;".$serviceStats["UNKNOWN_A"].";\n";
	echo "UNDETERMINED;".$serviceStats["UNDETERMINED_T"]."s;".$serviceStats["UNDETERMINED_TP"]."%;;;\n";
	echo "\n";
	echo "\n";

	/*
	 * Getting evolution of service stats in time
	 */
	echo _("Day").";"._("Duration").";"
				   ._("OK")." "._("Time")."; "._("OK")."; "._("OK")." Alert;"
				   ._("Warning")." "._("Time")."; "._("Warning").";"._("Warning")." Alert;"
				   ._("Unknown")." "._("Time")."; "._("Unknown").";"._("Unknown")." Alert;"
				   ._("Critical")." "._("Time")."; "._("Critical").";"._("Critical")." Alert;"
                   ._("Day").";\n";

	$request = "SELECT  * FROM `log_archive_service` " .
			"WHERE `host_id` = '".$host_id."' " .
			"AND `service_id` = '".$service_id."' " .
			"AND `date_start` >= '".$start_date."' " .
			"AND `date_end` <= '".$end_date."' " .
			"ORDER BY `date_start` DESC";
	$DBRESULT = $pearDBO->query($request);
	while ($row = $DBRESULT->fetchRow()) {
		$duration = $row["date_end"] - $row["date_start"];
		/* Percentage by status */
		$duration = $row["OKTimeScheduled"] + $row["WARNINGTimeScheduled"] + $row["UNKNOWNTimeScheduled"] + $row["CRITICALTimeScheduled"];
		$row["OK_MP"] = round($row["OKTimeScheduled"] * 100 / $duration, 2);
		$row["WARNING_MP"] = round($row["WARNINGTimeScheduled"] * 100 / $duration, 2);
		$row["UNKNOWN_MP"] = round($row["UNKNOWNTimeScheduled"] * 100 / $duration, 2);
		$row["CRITICAL_MP"] = round($row["CRITICALTimeScheduled"] * 100 / $duration, 2);
		echo $row["date_start"].";".$duration.";".
		 	$row["OKTimeScheduled"]."s;".$row["OK_MP"]."%;".$row["OKnbEvent"].";".
		 	$row["WARNINGTimeScheduled"]."s;".$row["WARNING_MP"]."%;".$row["WARNINGnbEvent"].";".
		 	$row["UNKNOWNTimeScheduled"]."s;".$row["UNKNOWN_MP"]."%;".$row["UNKNOWNnbEvent"].";".
		 	$row["CRITICALTimeScheduled"]."s;".$row["CRITICAL_MP"]."%;".$row["CRITICALnbEvent"].";".
            date("Y-m-d H:i:s", $row["date_start"]).";\n";
	}
	$DBRESULT->free();
?>