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
 * SVN : $URL
 * SVN : $Id: csv_HostLogs.php 7139 2008-11-24 17:19:45Z jmathis $
 * 
 */

	include_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path . "www/class/centreonDB.class.php";
	include_once($centreon_path . "www/include/common/common-Func.php");
	include_once($centreon_path . "www/include/reporting/dashboard/common-Func.php");
	require_once $centreon_path . "www/class/other.class.php";
	require_once $centreon_path . "www/class/User.class.php";
	require_once $centreon_path . "www/class/Oreon.class.php";	
	include_once($centreon_path . "www/include/reporting/dashboard/DB-Func.php");
		
	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");
	$pearDBO = new CentreonDB("centstorage");
	
	/*
	 * Checking session
	 */
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
	echo _("UNDETERMINED").";".$hostStats["UNDETERMINED_T"].";".$hostStats["UNDETERMINED_TP"].";\n";	
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