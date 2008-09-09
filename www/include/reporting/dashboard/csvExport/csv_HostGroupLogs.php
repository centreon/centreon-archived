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

	/* getting hostgroup id */
	isset ($_GET["hostgroup"]) ? $id = $_GET["hostgroup"] : $id = "NULL";
	isset ($_POST["hostgroup"]) ? $id = $_POST["hostgroup"] : $id;

	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = $dates[0];
	$end_date = $dates[1];
	$hostgroup_name = getHostgroupNameFromId($id);
	
	/*
	 * file type setting
	 */
	header("Content-Type: application/csv-tab-delimited-table");
	header("Content-disposition: filename=".$hostgroup_name.".csv");


	echo _("Hostgroup").";"._("Begin date")."; "._("End date")."; "._("Duration")."\n";
	echo $hostgroup_name."; ".$start_date."; ".$end_date."; ".($end_date - $start_date)."\n";
	echo "\n";

	echo _("Status").";"._("Total Time").";"._("Mean Time")."; "._("Alert")."\n";
	/*
	 * Getting stats on Host
	 */
	$reportingTimePeriod = getreportingTimePeriod();
	$hostgroupStats = array();
	$hostgroupStats = getLogInDbForHostGroup($id, $start_date, $end_date, $reportingTimePeriod) ;
	echo _("DOWN").";".$hostgroupStats["average"]["DOWN_TP"].";".$hostgroupStats["average"]["DOWN_MP"].";".$hostgroupStats["average"]["DOWN_A"].";\n";
	echo _("UP").";".$hostgroupStats["average"]["UP_TP"].";".$hostgroupStats["average"]["UP_MP"].";".$hostgroupStats["average"]["UP_A"].";\n";
	echo _("UNREACHABLE").";".$hostgroupStats["average"]["UNREACHABLE_TP"].";".$hostgroupStats["average"]["UNREACHABLE_MP"].";".$hostgroupStats["average"]["UNREACHABLE_A"].";\n";
	echo _("UNDETERMINED").";".$hostgroupStats["average"]["UNDETERMINED_TP"].";".$hostgroupStats["average"]["UNDETERMINED_MP"].";".$hostgroupStats["average"]["UNDETERMINED_A"].";\n";
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
	$DBRESULT = & $pearDB->query($request);
	if (PEAR::isError($DBRESULT))
		die( "MySQL Error : ".$DBRESULT->getDebugInfo());
	while ($hg =& $DBRESULT->fetchRow()) {
		if ($str != "")
			$str .= ", ";
		$str .= $hg["host_host_id"]; 
	}
	unset($hg);
	unset($DBRESULT);
	/*
	 * Getting hostgroup stats evolution
	 */
	$rq = 'SELECT `date_start`, `date_end`, sum(`UPnbEvent`) as UPnbEvent, sum(`DOWNnbEvent`) as DOWNnbEvent, sum(`UNREACHABLEnbEvent`) as UNREACHABLEnbEvent, '.
			'avg( `UPTimeScheduled` ) as "UPTimeScheduled", '.
			'avg( `DOWNTimeScheduled` ) as "DOWNTimeScheduled", '.
			'avg( `UNREACHABLETimeScheduled` ) as "UNREACHABLETimeScheduled" '.
			'FROM `log_archive_host` WHERE `host_id` IN ('.$str.') GROUP BY `date_end`, `date_start`  ORDER BY `date_start` desc';
	$DBRESULT = & $pearDBO->query($rq);
	if (PEAR::isError($DBRESULT))
		die( "MySQL Error : ".$DBRESULT->getDebugInfo());

	echo _("Day").";"._("Duration").";".
	 	_("Up Mean Time").";"._("Up Alert").";".
	 	_("Down Mean Time").";"._("Down Alert").";".
	 	_("Unreachable Mean Time").";"._("Unreachable Alert").";\n";
	while ($row =& $DBRESULT->fetchRow()) {
		$duration = $row["UPTimeScheduled"] + $row["DOWNTimeScheduled"] + $row["UNREACHABLETimeScheduled"];
		/* Percentage by status */
		$row["UP_MP"] = round($row["UPTimeScheduled"] * 100 / $duration, 2);
		$row["DOWN_MP"] = round($row["DOWNTimeScheduled"] * 100 / $duration, 2);
		$row["UNREACHABLE_MP"] = round($row["UNREACHABLETimeScheduled"] * 100 / $duration, 2);
		echo $row["date_start"].";".$duration.";".
		 	$row["UP_MP"]."%;".$row["UPnbEvent"].";".
		 	$row["DOWN_MP"]."%;".$row["DOWNnbEvent"].";".
		 	$row["UNREACHABLE_MP"]."%;".$row["UNREACHABLEnbEvent"].";\n";
	}
?>