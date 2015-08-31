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

	$debug = 0;

	include_once "@CENTREON_ETC@/centreon.conf.php";

	require_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
    require_once $centreon_path . 'www/class/centreonLang.class.php';
	include_once $centreon_path . "www/include/common/common-Func.php";

	session_start();
    if (!isset($_SESSION['centreon'])) {
		exit();
	}
    $centreon = $_SESSION['centreon'];

	$centreonLang = new CentreonLang($centreon_path, $centreon);
	$centreonLang->bindLang();

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
		$rq2 = 	" SELECT COUNT(DISTINCT CONCAT(no.name1,';', no.name2)) as count, nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	} else {
		$rq2 = 	" SELECT count(nss.current_state) AS count, nss.current_state" .
				" FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.is_active = 1 GROUP BY nss.current_state";
	}
	$serviceCounter = 0;
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 6=>0, 7=>0, 8=>0);
	$DBRESULT = $obj->DBNdo->query($rq2);
	while ($ndo = $DBRESULT->fetchRow()) {
		$svc_stat[$ndo["current_state"]] = $ndo["count"];
		$serviceCounter += $svc_stat[$ndo["current_state"]];
	}
	$DBRESULT->free();
	unset($ndo);

	/* ********************************************
	 *  Get Real non-ok Status
	 */
	if (!$obj->is_admin) {
        $rq3 =  "SELECT COUNT(DISTINCT CONCAT(no.name1,';', no.name2)) as number, nss.state_type, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth, nss.current_state " .
		    	"FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no, " . $obj->ndoPrefix."hoststatus nhs, " . $obj->ndoPrefix."services ns, centreon_acl " .
    			"WHERE no.object_id = nss.service_object_id " .
				"	AND no.name1 NOT LIKE '_Module_%' " .
				"	AND no.is_active = 1 " .
				"	AND nss.scheduled_downtime_depth = '0' " .
				"	AND nss.problem_has_been_acknowledged = '0' " .
				"	AND nss.current_state != '0' " .
        		"   AND no.object_id = ns.service_object_id " .
	            "   AND ns.host_object_id = nhs.host_object_id " .
	            "   AND nhs.current_state = '0' ".
            	"   AND no.name1 = centreon_acl.host_name ".
				"   AND no.name2 = centreon_acl.service_description " .
        		"   AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				"	GROUP BY nss.current_state, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth";
	} else {
	    $rq3 =  "SELECT COUNT(DISTINCT CONCAT(no.name1,';', no.name2)) as number, nss.state_type, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth, nss.current_state " .
		    	"FROM ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."objects no, " . $obj->ndoPrefix."hoststatus nhs, " . $obj->ndoPrefix."services ns ".
    			"WHERE no.object_id = nss.service_object_id " .
				"	AND no.name1 NOT LIKE '_Module_%' " .
				"	AND no.is_active = 1 " .
				"	AND nss.scheduled_downtime_depth = '0' " .
				"	AND nss.problem_has_been_acknowledged = '0' " .
				"	AND nss.current_state != '0' " .
	            "   AND no.object_id = ns.service_object_id " .
	            "   AND ns.host_object_id = nhs.host_object_id " .
	            "   AND nhs.current_state = '0' ".
				"	GROUP BY nss.current_state, nss.problem_has_been_acknowledged, nss.scheduled_downtime_depth";
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
        $pollersWithLatency = array();

	/*
	 * Get minimum check interval
	 */
	$request = "SELECT MIN(check_interval) FROM ".$obj->ndoPrefix."services WHERE active_checks_enabled = 1";
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

	$inactivInstance = "";

	if ($pollerList != "") {
		$request = 	"SELECT UNIX_TIMESTAMP(`status_update_time`) AS last_update, `is_currently_running`, instance_name, ".$obj->ndoPrefix."instances.instance_id " .
					"FROM `".$obj->ndoPrefix."programstatus`, ".$obj->ndoPrefix."instances " .
					"WHERE ".$obj->ndoPrefix."programstatus.instance_id = ".$obj->ndoPrefix."instances.instance_id AND ".$obj->ndoPrefix."instances.instance_name IN ($pollerList)";
		$DBRESULT = $obj->DBNdo->query($request);
		while ($ndo = $DBRESULT->fetchRow()) {
			/*
			 * Running
			 */
			if ($status != 2 && ($ndo["is_currently_running"] == 0 || (time() - $ndo["last_update"] >= $timeUnit * 5))) {
				$status = 1;
				if ($pollerListInError != "") {
					$pollerListInError .= ", ";
				}
				$pollerListInError .= $ndo["instance_name"];
			}
			if ($ndo["is_currently_running"] == 0 || (time() - $ndo["last_update"] >= $timeUnit * 2)) {
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
	
		$request = 	"SELECT stat_value, i.instance_id, instance_name " .
					"FROM `nagios_stats` ns, instance i " .
					"WHERE ns.stat_label = 'Service Check Latency' " .
					"	AND ns.stat_key LIKE 'Average' " .
					"	AND ns.instance_id = i.instance_id" .
					"	AND i.instance_name IN ($pollerList)";
		$DBRESULT = $obj->DBC->query($request);
		while ($ndo = $DBRESULT->fetchRow()) {
			if (!$latency && $ndo["stat_value"] >= 60) {
				$latency = 1;
                                $pollersWithLatency[$ndo['instance_id']] = $ndo['instance_name'];
			}
			if ($ndo["stat_value"] >= 120) {
				$latency = 2;
                                $pollersWithLatency[$ndo['instance_id']] = $ndo['instance_name'];
			}
		}
		$DBRESULT->free();
		unset($ndo);
	} else {
		$pollerListInError = "";
		$inactivInstance = "";
	}
	
	/* ********************************************
	 * Error Messages
	 */
	if ($status != 0) {
		$errorPstt = "$error";
	} else {
		$errorPstt = _("OK: all pollers are running");
	}

	if ($latency && count($pollersWithLatency)) {
            $errorLtc = sprintf(_("Latency detected on %s; check configuration for better optimisation"), implode(',', $pollersWithLatency));
	} else {
            $errorLtc = _("OK: no latency detected on your platform");
	}

	if ($activity != 0) {
		$errorAct = _("Some database poller updates are not active; check your monitoring platform");
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