<?php
/*
 * Copyright 2005-2011 MERETHIS
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

	$debug = 0;

	//include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once "/etc/centreon/centreon.conf.php";
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";

	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest((isset($_POST["sid"]) ? $_POST["sid"] : $_GET["sid"]), 1, 1, 0, $debug);

	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		$obj->reloadSession();
	} else {
		print "Bad Session ID";
		exit();
	}

	/* *********************************************
	 * Get active poller only
	 */
	$pollerList = "";
	$request = "SELECT name FROM nagios_server WHERE ns_activate = '1'";
	$DBRESULT = $obj->DB->query($request);
	while ($d = $DBRESULT->fetchRow()) {
		if ($pollerList != "") {
			$pollerList .= ", ";
		}
		$pollerList .= "'".$d["name"]."'";
	}
	$DBRESULT->free();

	/* *********************************************
	 * Get Host stats
	 */
	$rq1 = 	" SELECT count(DISTINCT ".$obj->ndoPrefix."objects.name1), ".$obj->ndoPrefix."hoststatus.current_state" .
			" FROM ".$obj->ndoPrefix."hoststatus, ".$obj->ndoPrefix."objects";
	if (!$obj->is_admin) {
		$rq1 .= " , centreon_acl ";
	}
	$rq1 .= " WHERE ".$obj->ndoPrefix."objects.object_id = ".$obj->ndoPrefix."hoststatus.host_object_id " .
			" AND ".$obj->ndoPrefix."objects.is_active = 1 " .
			$obj->access->queryBuilder("AND", $obj->ndoPrefix."objects.name1", "centreon_acl.host_name") .
			$obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr) .
			" AND " . $obj->ndoPrefix. "objects.name1 NOT LIKE '_Module_%' " .
			" GROUP BY ".$obj->ndoPrefix."hoststatus.current_state";
	$hostCounter = 0;
	$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
	$DBRESULT = $obj->DBNdo->query($rq1);
	while ($ndo = $DBRESULT->fetchRow()) {
		$host_stat[$ndo["current_state"]] = $ndo["count(DISTINCT ".$obj->ndoPrefix."objects.name1)"];
		$hostCounter += $host_stat[$ndo["current_state"]];
	}
	$DBRESULT->free();
	unset($ndo);

	/* *********************************************
	 * Get Service stats
	 */
	if (!$obj->is_admin) {
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	} else {
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	}
	$serviceCounter = 0;
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 6=>0, 7=>0, 8=>0);
	$DBRESULT = $obj->DBNdo->query($rq2);
	while ($ndo = $DBRESULT->fetchRow()) {
		$svc_stat[$ndo["current_state"]] = $ndo["count(nss.current_state)"];
		$serviceCounter += $svc_stat[$ndo["current_state"]];
	}
	$DBRESULT->free();
	unset($ndo);

	/* ********************************************
	 *  Get Real non-ok Status
	 */
	if (!$obj->is_admin) {
        $rq3 =  "SELECT COUNT(DISTINCT CONCAT(no.name1,';', no.name2)) as number, nss.state_type, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth, nss.current_state " .
		    	"FROM nagios_servicestatus nss, nagios_objects no, centreon_acl " .
    			"WHERE no.object_id = nss.service_object_id " .
				"	AND no.name1 NOT LIKE '_Module_%' " .
				"	AND no.is_active = 1 " .
				"	AND nss.scheduled_downtime_depth = '0' " .
				"	AND nss.problem_has_been_acknowledged = '0' " .
				"	AND nss.current_state != '0' " .
            	"   AND no.name1 = centreon_acl.host_name ".
				"   AND no.name2 = centreon_acl.service_description " .
        		"   AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				"	AND (" .
				"		SELECT 1 " .
				"		FROM nagios_hoststatus nhs, nagios_objects no2 " .
				"		WHERE nhs.host_object_id = no2.object_id " .
				"			AND no2.name1 = no.name1 " .
				"			AND nhs.current_state = '0') " .
				"		GROUP BY nss.current_state, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth";
	} else {
	    $rq3 =  "SELECT COUNT(DISTINCT CONCAT(no.name1,';', no.name2)) as number, nss.state_type, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth, nss.current_state " .
		    	"FROM nagios_servicestatus nss, nagios_objects no " .
    			"WHERE no.object_id = nss.service_object_id " .
				"	AND no.name1 NOT LIKE '_Module_%' " .
				"	AND no.is_active = 1 " .
				"	AND nss.scheduled_downtime_depth = '0' " .
				"	AND nss.problem_has_been_acknowledged = '0' " .
				"	AND nss.current_state != '0' " .
				"	AND (" .
				"		SELECT 1 " .
				"		FROM nagios_hoststatus nhs, nagios_objects no2 " .
				"		WHERE nhs.host_object_id = no2.object_id " .
				"			AND no2.name1 = no.name1 " .
				"			AND nhs.current_state = '0') " .
				"		GROUP BY nss.current_state, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth";
	}

	$DBRESULT = $obj->DBNdo->query($rq3);
	while ($ndo = $DBRESULT->fetchRow()) {
		$svc_stat[$ndo["current_state"] + 5] = $ndo["number"];
	}
	$DBRESULT->free();
	unset($ndo);

	/* ********************************************
	 * Check Poller Status
	 */
	$status = 0;
	$latency = 0;
	$activity = 0;
	$error = "";
	$pollerListInError = "";

	/*
	 * Get minimum check interval
	 */
	$request = "SELECT MIN(check_interval) FROM ".$obj->ndoPrefix."services";
	$DBRESULT = $obj->DBNdo->query($request);
	if (isset($DBRESULT) && $DBRESULT->numRows()) {
		$data = $DBRESULT->fetchRow();
		$minInterval = $data["MIN(check_interval)"];
	} else {
		$minInterval = 5;
	}

	/*
	 * Get minimin interval lenght
	 */
	$request = "SELECT MIN(interval_length) FROM cfg_nagios";
	$DBRESULT = $obj->DB->query($request);
	$data = $DBRESULT->fetchRow();
	$intervalLength = $data["MIN(interval_length)"];

	/* *****************************************************
	 * Unit Time
	 */
	$timeUnit = $minInterval * $intervalLength;

	$request = 	"SELECT UNIX_TIMESTAMP(`status_update_time`) AS last_update, `is_currently_running`, instance_name, ".$obj->ndoPrefix."instances.instance_id " .
				"FROM `".$obj->ndoPrefix."programstatus`, ".$obj->ndoPrefix."instances " .
				"WHERE ".$obj->ndoPrefix."programstatus.instance_id = ".$obj->ndoPrefix."instances.instance_id AND ".$obj->ndoPrefix."instances.instance_name IN ($pollerList)";
	$DBRESULT = $obj->DBNdo->query($request);
	while ($ndo = $DBRESULT->fetchRow()) {
		/*
		 * Running
		 */
		if ($status != 2 && $ndo["is_currently_running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit / 5)) {
			$status = 1;
			if ($pollerListInError != "") {
				$pollerListInError .= ", ";
			}
			$pollerListInError .= $ndo["instance_name"];
		}
		if ($ndo["is_currently_running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit / 4)) {
			$status = 2;
			if ($pollerListInError != "") {
				$pollerListInError .= ", ";
			}
			$pollerListInError .= $ndo["instance_name"];
		}
		/*
		 * Activity
		 */
		if ($activity != 2 && (time() - $ndo["last_update"] >= $timeUnit * 4)) {
			$activity = 2;
			if ($inactivInstance != "") {
            	$inactivInstance .= ",";
            }
            $inactivInstance .= $ndo["instance_name"]." [".(time() - $ndo["last_update"])."s / ".($timeUnit * 2)."s]";
		} else if ((time() - $ndo["last_update"] >= $timeUnit * 2)) {
			$activity = 1;
			if ($inactivInstance != "") {
            	$inactivInstance .= ",";
            }
            $inactivInstance .= $ndo["instance_name"]." [".(time() - $ndo["last_update"])."s / ".($timeUnit * 2)."s]";
		}

	}
	$DBRESULT->free();
	$error = "Pollers $pollerListInError not running.";

	$request = 	"SELECT stat_value, i.instance_id, name " .
				"FROM `nagios_stats` ns, instances i " .
				"WHERE ns.stat_label = 'Service Check Latency' " .
				"	AND ns.stat_key LIKE 'Average' " .
				"	AND ns.instance_id = i.instance_id" .
				"	AND i.name IN ($pollerList)";
	$DBRESULT = $obj->DBC->query($request);
	while ($ndo = $DBRESULT->fetchRow()) {
		if ($latency != 2 && $ndo["stat_value"] >= 60) {
			$latency = 1;
		}
		if ($ndo["stat_value"] >= 120) {
			$latency = 2;
		}
	}
	$DBRESULT->free();
	unset($ndo);

	/* ********************************************
	 * Error Messages
	 */
	if ($status != 0) {
		$errorPstt = "$error";
	} else {
		$errorPstt = _("OK: all pollers are running");
	}

	if ($latency != 0) {
		$errorLtc = _("Latency detected on your platform; check configuration for better optimisation");
	} else {
		$errorLtc = _("OK: no latency detected on your platform");
	}

	if ($activity != 0) {
		$errorAct = _("Some database poller updates are not active; check your nagios platform");
	} else {
		$errorAct = _("OK: all database poller updates are active");
	}

	/* *********************************************
	 * Create Buffer
	 */
	$obj->XML = new CentreonXML();
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("infos");
	$obj->XML->writeElement("filetime", time());
	$obj->XML->endElement();
	$obj->XML->startElement("s");
	$obj->XML->writeElement("th", $hostCounter);
	$obj->XML->writeElement("ts", $serviceCounter);
	$obj->XML->writeElement("o", $svc_stat["0"]);
	$obj->XML->writeElement("w", $svc_stat["1"]);
	$obj->XML->writeElement("wU", $svc_stat["6"]);
	$obj->XML->writeElement("c", $svc_stat["2"]);
	$obj->XML->writeElement("cU", $svc_stat["7"]);
	$obj->XML->writeElement("un1", $svc_stat["3"]);
	$obj->XML->writeElement("un1U", $svc_stat["8"]);
	$obj->XML->writeElement("p1", $svc_stat["4"]);
	$obj->XML->writeElement("up", $host_stat["0"]);
	$obj->XML->writeElement("d", $host_stat["1"]);
	$obj->XML->writeElement("un2", $host_stat["2"]);
	$obj->XML->writeElement("p2", $host_stat["3"]);
	$obj->XML->endElement();
	$obj->XML->startElement("m");
	$obj->XML->writeElement("pstt", $status);
	$obj->XML->writeElement("ltc", $latency);
	$obj->XML->writeElement("act", $activity);
	$obj->XML->writeElement("errorPstt", $errorPstt);
	$obj->XML->writeElement("errorLtc", $errorLtc);
	$obj->XML->writeElement("errorAct", $errorAct);
	$obj->XML->endElement();
	$obj->XML->endElement();

	/*
	 * Send headers
	 */
	$obj->header();

	/*
	 * Display XML data
	 */
	$obj->XML->output();

?>