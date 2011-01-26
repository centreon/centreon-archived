<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/TopCounter/statusCounter.php $
 * SVN : $Id: statusCounter.php 11397 2010-12-16 10:44:35Z jmathis $
 *
 */

	ini_set("display_errors", "On");

	$debug = 0;

	include_once "@CENTREON_ETC@/centreon.conf.php";
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
	$DBRESULT =& $obj->DB->query($request);
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
	$rq1 = 	" SELECT count(DISTINCT name), state " .
			" FROM hosts ";
	if (!$obj->is_admin) {
		$rq1 .= " , centreon_acl ";
	}
	$rq1 .= " WHERE name NOT LIKE '_Module_%' " .
			$obj->access->queryBuilder("AND", "host_id", "centreon_acl.host_id") .
			$obj->access->queryBuilder("AND", "centreon_acl.group_id", $obj->grouplistStr) .
			" GROUP BY state";

	$hostCounter = 0;
	$host_stat = array(0 => 0, 1 => 0, 2 => 0, 3 => 0);
	$DBRESULT =& $obj->DBC->query($rq1);
	while ($data =& $DBRESULT->fetchRow()) {
		$host_stat[$data["state"]] = $data["count(DISTINCT name)"];
		$hostCounter += $host_stat[$data["state"]];
	}
	$DBRESULT->free();
	unset($data);

	/* *********************************************
	 * Get Service stats
	 */
	if (!$obj->is_admin) {
		$rq2 = 	" SELECT count(services.state), services.state" .
				" FROM services, hosts, centreon_acl " .
				" WHERE hosts.name NOT LIKE '_Module_%' ".
				" AND hosts.host_id = services.host_id".
				" AND services.host_id = centreon_acl.host_id ".
				" AND service_id = centreon_acl.service_id " .
				" AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				" GROUP BY services.state";
	} else {
		$rq2 = 	" SELECT count(services.state), services.state" .
				" FROM services, hosts" .
				" WHERE hosts.name NOT LIKE '_Module_%' ".
				" AND hosts.host_id = services.host_id".
				" GROUP BY services.state";
	}
	$serviceCounter = 0;
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0, 6=>0, 7=>0, 8=>0);
	$DBRESULT =& $obj->DBC->query($rq2);
	while ($data =& $DBRESULT->fetchRow()) {
		$svc_stat[$data["state"]] = $data["count(services.state)"];
		$serviceCounter += $svc_stat[$data["state"]];
	}
	$DBRESULT->free();
	unset($ndo);

	/* ********************************************
	 *  Get Real non-ok Status
	 */
	if (!$obj->is_admin) {
        $rq3 =  "SELECT COUNT(DISTINCT CONCAT(s.service_id,';', s.host_id)) as number, s.state_type, s.acknowledged, s.scheduled_downtime_depth, s.state " .
		    	"FROM services s, `hosts` h, centreon_acl " .
    			"WHERE s.host_id IS NOT NULL " .
				"	AND s.scheduled_downtime_depth = '0' " .
				"	AND s.acknowledged = '0' " .
				"	AND s.state <> '0' " .
            	"   AND s.host_id = centreon_acl.host_id ".
				"   AND s.service_id = centreon_acl.service_id " .
        		"   AND centreon_acl.group_id IN (".$obj->grouplistStr.") ".
				"	AND h.state = '0' " .
				" GROUP BY s.state, s.acknowledged, s.scheduled_downtime_depth";
	} else {
	    $rq3 =  "SELECT COUNT(DISTINCT CONCAT(s.service_id,';', s.host_id)) as number, s.state_type, s.acknowledged, s.scheduled_downtime_depth, s.state " .
		    	"FROM services s, `hosts` h " .
    			"WHERE s.host_id IS NOT NULL " .
				"	AND s.scheduled_downtime_depth = '0' " .
				"	AND s.acknowledged = '0' " .
				"	AND s.state <> '0' " .
				"	AND h.state = '0' " .
				" GROUP BY s.state, s.acknowledged, s.scheduled_downtime_depth";
	}
	$DBRESULT =& $obj->DBC->query($rq3);
	while ($ndo =& $DBRESULT->fetchRow()) {
		$svc_stat[$ndo["state"] + 5] = $ndo["number"];
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
	$request = "SELECT MIN(check_interval) FROM services";
	$DBRESULT =& $obj->DBC->query($request);
	if (isset($DBRESULT) && $DBRESULT->numRows()) {
		$data =& $DBRESULT->fetchRow();
		$minInterval = $data["MIN(check_interval)"];
	} else {
		$minInterval = 5;
	}

	/*
	 * Get minimin interval lenght
	 */
	$request = "SELECT MIN(interval_length) FROM cfg_nagios";
	$DBRESULT =& $obj->DB->query($request);
	$data =& $DBRESULT->fetchRow();
	$intervalLength = $data["MIN(interval_length)"];

	/* *****************************************************
	 * Unit Time
	 */
	$timeUnit = $minInterval * $intervalLength;

	$request = 	"SELECT `last_alive` AS last_update, `running`, name, instance_id " .
				"FROM instances " .
				"WHERE name IN ($pollerList)";
	$DBRESULT =& $obj->DBC->query($request);
	$inactivInstance = "";
	while ($ndo =& $DBRESULT->fetchRow()) {
		/*
		 * Running
		 */
		if ($status != 2 && $ndo["running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit / 5)) {
			$status = 1;
			if ($pollerListInError != "") {
				$pollerListInError .= ", ";
			}
			$pollerListInError .= $ndo["name"];
		}
		if ($ndo["running"] == 0 && (time() - $ndo["last_update"] >= $timeUnit / 2)) {
			$status = 2;
			if ($pollerListInError != "") {
				$pollerListInError .= ", ";
			}
			$pollerListInError .= $ndo["name"];
		}
		/*
		 * Activity
		 */
		if ($activity != 2 && (time() - $ndo["last_update"] >= $timeUnit * 4)) {
			$activity = 2;
			if ($inactivInstance != "") {
            	$inactivInstance .= ",";
            }
            $inactivInstance .= $ndo["name"]." [".(time() - $ndo["last_update"])."s / ".($timeUnit * 2)."s]";
		} else if ((time() - $ndo["last_update"] >= $timeUnit * 2)) {
			$activity = 1;
			if ($inactivInstance != "") {
            	$inactivInstance .= ",";
            }
            $inactivInstance .= $ndo["name"]." [".(time() - $ndo["last_update"])."s / ".($timeUnit * 2)."s]";
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
	$DBRESULT =& $obj->DBC->query($request);
	while ($ndo =& $DBRESULT->fetchRow()) {
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
		$errorPstt = _("OK : all pollers are running");
	}

	if ($latency != 0) {
		$errorLtc = _("Latency detected on your platform; check configuration for better optimisation");
	} else {
		$errorLtc = _("OK : no latency detected on your platform");
	}

	if ($activity != 0) {
		$errorAct = _("Some database poller updates are not active; check your nagios platform");
	} else {
		$errorAct = _("OK : all database poller updates are active");
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