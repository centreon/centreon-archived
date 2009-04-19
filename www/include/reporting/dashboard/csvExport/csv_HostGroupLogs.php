<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */

	include_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path . "www/class/centreonDB.class.php";
	include_once($centreon_path . "www/include/common/common-Func.php");
	include_once($centreon_path . "www/include/reporting/dashboard/common-Func.php");
	require_once $centreon_path . "www/class/User.class.php";
	require_once $centreon_path . "www/class/Oreon.class.php";
	require_once $centreon_path . "www/class/other.class.php";	
	include_once($centreon_path . "www/include/reporting/dashboard/DB-Func.php");

	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$res =& $pearDB->query("SELECT * FROM contact, session WHERE session.session_id='".$_GET['sid']."' AND session.user_id = contact.contact_id");
		$user =& new User($res->fetchRow(), "3");
		$oreon = new Oreon($user);
		
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
	$start_date = $_GET['start'];
	$end_date = $_GET['end'];
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
	$DBRESULT = & $pearDB->query($request);
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