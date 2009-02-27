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
 * SVN : $Id: csv_ServiceGroupLogs.php 7139 2008-11-24 17:19:45Z jmathis $
 * 
 */
 
	include_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path . "www/class/centreonDB.class.php";
	include_once($centreon_path . "www/include/common/common-Func.php");
	include_once($centreon_path . "www/include/reporting/dashboard/common-Func.php");
	require_once $centreon_path . "www/class/User.class.php";
	require_once $centreon_path . "www/class/Oreon.class.php";
	require_once $centreon_path."www/class/other.class.php";	
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
	isset ($_GET["servicegroup"]) ? $id = $_GET["servicegroup"] : $id = "NULL";
	isset ($_POST["servicegroup"]) ? $id = $_POST["servicegroup"] : $id = $id;
	/*
	 * Getting time interval to report
	 */
	$dates = getPeriodToReport();
	$start_date = $_GET['start'];
	$end_date = $_GET['end'];
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
							  ";".$tab["UNDETERMINED_TP"]. "%;;\n"; 
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