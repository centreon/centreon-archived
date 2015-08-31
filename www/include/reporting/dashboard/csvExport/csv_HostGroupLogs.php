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

	/*
	 * getting hostgroup id
	 */
	isset ($_GET["hostgroup"]) ? $id = htmlentities($_GET["hostgroup"], ENT_QUOTES, "UTF-8") : $id = "NULL";
	isset ($_POST["hostgroup"]) ? $id = htmlentities($_POST["hostgroup"], ENT_QUOTES, "UTF-8") : $id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = htmlentities($_GET['start'], ENT_QUOTES, "UTF-8");
	$end_date = htmlentities($_GET['end'], ENT_QUOTES, "UTF-8");
	$hostgroup_name = getHostgroupNameFromId($id);

	/*
	 * file type setting
	 */
	header("Cache-Control: public");
	header("Pragma: public");
	header("Content-Type: application/octet-stream");
	header("Content-disposition: filename=".$hostgroup_name.".csv");


	echo _("Hostgroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $hostgroup_name."; ".date(_("d/m/Y H:i:s"), $start_date)."; ".date(_("d/m/Y H:i:s"), $end_date)."; ".($end_date - $start_date)."s\n";
	echo "\n";

	echo _("Status").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	/*
	 * Getting stats on Host
	 */
	$reportingTimePeriod = getreportingTimePeriod();
	$hostgroupStats = array();
	$hostgroupStats = getLogInDbForHostGroup($id, $start_date, $end_date, $reportingTimePeriod) ;
	echo _("DOWN").";".$hostgroupStats["average"]["DOWN_TP"].";".$hostgroupStats["average"]["DOWN_MP"]."%;".$hostgroupStats["average"]["DOWN_A"].";\n";
	echo _("UP").";".$hostgroupStats["average"]["UP_TP"].";".$hostgroupStats["average"]["UP_MP"]."%;".$hostgroupStats["average"]["UP_A"].";\n";
	echo _("UNREACHABLE").";".$hostgroupStats["average"]["UNREACHABLE_TP"].";".$hostgroupStats["average"]["UNREACHABLE_MP"]."%;".$hostgroupStats["average"]["UNREACHABLE_A"].";\n";
	echo _("UNDETERMINED").";".$hostgroupStats["average"]["UNDETERMINED_TP"].";\n";
	echo "\n\n";
	echo _("Hosts Group").";"._("Up Time").";"._("Up Mean Time").";"._("Up Alerts").";".
		_("Down Time").";"._("Down Mean Time").";"._("Down Alerts").";".
		_("Unreachable Time").";"._("Unreachable Mean Time").";"._("Unreachable Alerts").";".
		_("Undetermined Time").";\n";
	foreach ($hostgroupStats as $key => $tab) {
		if ($key != "average") {
			echo $tab["NAME"].";".$tab["UP_TP"].";".$tab["UP_MP"].";".$tab["UP_A"].";".
			$tab["DOWN_TP"].";".$tab["DOWN_MP"].";".$tab["DOWN_A"].";".
			$tab["UNREACHABLE_TP"].";".$tab["UNREACHABLE_MP"].";".$tab["UNREACHABLE_A"].";".
			$tab["UNDETERMINED_TP"].";\n";
		}
	}
	echo "\n\n";
	/*
	 * getting all hosts from hostgroup
	 */
	$str = "";
	$request = "SELECT host_host_id FROM `hostgroup_relation` WHERE `hostgroup_hg_id` = '" .$id."'";
	$DBRESULT = $pearDB->query($request);
	while ($hg = $DBRESULT->fetchRow()) {
		if ($str != "")
			$str .= ", ";
		$str .= "'".$hg["host_host_id"]."'";
	}
	if ($str == "")
		$str = "''";
	unset($hg);
	unset($DBRESULT);
	/*
	 * Getting hostgroup stats evolution
	 */
	$rq = "SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, ".
			"avg( `UPTimeScheduled` ) as UPTimeScheduled, ".
			"avg( `DOWNTimeScheduled` ) as DOWNTimeScheduled, ".
			"avg( `UNREACHABLETimeScheduled` ) as UNREACHABLETimeScheduled ".
			"FROM `log_archive_host` WHERE `host_id` IN (".$str.") " .
			"AND `date_start` >= '".$start_date."' " .
			"AND `date_end` <= '".$end_date."' " .
			"GROUP BY `date_end`, `date_start` ORDER BY `date_start` desc";
	$DBRESULT = $pearDBO->query($rq);

	echo _("Day").";"._("Duration").";".
	 	_("Up Mean Time").";"._("Up Alert").";".
	 	_("Down Mean Time").";"._("Down Alert").";".
	 	_("Unreachable Mean Time").";"._("Unreachable Alert")._("Day").";\n";
	while ($row = $DBRESULT->fetchRow()) {
		$duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];

		/* Percentage by status */
		$row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
		$row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
		$row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);

		echo $row["date_start"].";".$duration."s;".
		 	$row["UP_MP"]."%;".$row["UPnbEvent"].";".
		 	$row["DOWN_MP"]."%;".$row["DOWNnbEvent"].";".
		 	$row["UNREACHABLE_MP"]."%;".$row["UNREACHABLEnbEvent"].";".
            date("Y-m-d H:i:s", $row["date_start"]).";\n";
	}
	$DBRESULT->free();
?>