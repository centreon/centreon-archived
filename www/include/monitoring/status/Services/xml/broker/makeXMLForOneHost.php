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

	/**
	 * Require Centreon Config file
	 */
	include_once "/etc/centreon/centreon.conf.php";

	/**
	 * Include Monitoring Classes
	 */
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	include_once $centreon_path . "www/class/centreonLang.class.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);

	/**
	 * Manage Session
	 */
	CentreonSession::start();
	$centreon = $_SESSION['centreon'];

	/**
	 * Check Security
	 */
	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		print "Bad Session ID";
		exit();
	}

	/** **************************************************
	 * Enable Lang Object
	 */
	$centreonlang = new CentreonLang($centreon_path, $centreon);
	$centreonlang->bindLang();

	/** **************************************************
	 * Check Arguments From GET tab
	 */
	$host_id 		= $obj->checkArgument("host_id", $_GET, 0);
	$enable 		= $obj->checkArgument("enable", $_GET, "");
	$disable 		= $obj->checkArgument("disable", $_GET, "disable");
	$dateFormat		= $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");

	/** ***************************************************
	 * Get Host status
	 */
	$rq1 =  " SELECT state," .
			" address," .
			" name," .
			" perfdata," .
			" check_attempt," .
			" state_type," .
			" last_check, " .
			" next_check, " .
			" latency," .
			" execution_time," .
			" last_state_change," .
			" last_notification," .
			" next_host_notification," .
			" last_hard_state_change," .
			" last_hard_state," .
			" last_time_up," .
			" last_time_down," .
			" last_time_unreachable," .
			" notification_number," .
			" scheduled_downtime_depth," .
			" output," .
			" ROUND(percent_state_change) as percent_state_change," .
			" notify," .
			" event_handler_enabled," .
			" icon_image" .
			" FROM hosts " .
			" WHERE host_id = " . $host_id .
			" LIMIT 1";
	/*
	 * Request
	 */
	$DBRESULT = $obj->DBC->query($rq1);

	/*
	 * Start Buffer
	 */
	$obj->XML->startElement("reponse");
	if ($data = $DBRESULT->fetchRow()) {
		$duration = "";
		if ($data["last_state_change"] > 0) {
			$duration = CentreonDuration::toString(time() - $data["last_state_change"]);
		}

		if ($data["icon_image"] == "")
			$data["icon_image"] = "./img/icones/16x16/server_network.gif";
		else
			$data["icon_image"] = "./img/media/" . $data["icon_image"];

		$last_notification = "N/A";
		if ($data["last_notification"] > 0) {
			$last_notification = $data["last_notification"];
		}

		$next_notification = "N/A";
		if ($data["next_host_notification"] > 0) {
			$next_notification = $data["next_host_notification"];
		}

		$obj->XML->writeElement("hostname", $data["name"], false);
		$obj->XML->writeElement("address", $data["address"]);
		$obj->XML->startElement("state");
		$obj->XML->writeAttribute("color", $obj->colorHost[$data["state"]]);
		$obj->XML->text(_($obj->statusHost[$data["state"]]), false);
		$obj->XML->endElement();
		$obj->XML->writeElement("current_state_name", _("Host Status"), 0);
		$obj->XML->startElement("plugin_output");
		$obj->XML->writeAttribute("name", _("Status Information"));
		$obj->XML->text($data["output"], 0);
		$obj->XML->endElement();
		$obj->XML->writeElement("performance_data", $data["perfdata"]);
		$obj->XML->writeElement("performance_data_name", _("Performance Data"), 0);
		$obj->XML->startElement("current_attempt");
		$obj->XML->writeAttribute("name", _("Current Attempt"));
		$obj->XML->text($data["check_attempt"]);
		$obj->XML->endElement();
		$obj->XML->writeElement("state_type", $obj->stateTypeFull[$data["state_type"]]);
		$obj->XML->writeElement("state_type_name", _("State Type"), 0);
		$obj->XML->writeElement("last_check", $obj->GMT->getDate($dateFormat, $data["last_check"]));
		$obj->XML->writeElement("last_check_name", _("Last Check"), 0);
		$obj->XML->writeElement("next_check", $obj->GMT->getDate($dateFormat, $data["next_check"]));
		$obj->XML->writeElement("next_check_name", _("Next Check"), 0);
		$obj->XML->writeElement("check_latency", $data["latency"]);
		$obj->XML->writeElement("check_latency_name", _("Latency"), 0);
		$obj->XML->writeElement("check_execution_time", $data["execution_time"]);
		$obj->XML->writeElement("check_execution_time_name", _("Execution Time"), 0);
		$obj->XML->writeElement("last_state_change", $obj->GMT->getDate($dateFormat, $data["last_state_change"]));
		$obj->XML->writeElement("last_state_change_name", _("Last State Change"), 0);
		$obj->XML->writeElement("duration", $duration);
		$obj->XML->writeElement("duration_name", _("Current State Duration"), 0);
		$obj->XML->writeElement("last_notification", $obj->GMT->getDate($dateFormat, $last_notification));
		$obj->XML->writeElement("last_notification_name", _("Last Notification"), 0);
		$obj->XML->writeElement("next_notification", $obj->GMT->getDate($dateFormat, $next_notification));
		$obj->XML->writeElement("next_notification_name", _("Next Notification"), 0);
		$obj->XML->writeElement("current_notification_number", $data["notification_number"]);
		$obj->XML->writeElement("current_notification_number_name", _("Current Notification Number"), 0);
		$obj->XML->writeElement("percent_state_change", $data["percent_state_change"]);
		$obj->XML->writeElement("percent_state_change_name", _("Percent State Change"), 0);
		$obj->XML->writeElement("is_downtime", ($data["scheduled_downtime_depth"] > 0 ? $obj->en[1] : $obj->en[0]));
		$obj->XML->writeElement("is_downtime_name", _("In Scheduled Downtime?"), 0);
		$obj->XML->writeElement("last_update", $obj->GMT->getDate($dateFormat, time()));
		$obj->XML->writeElement("last_update_name", _("Last Update"), 0);
		$obj->XML->writeElement("ico", $data["icon_image"]);

		$obj->XML->startElement("last_time_up");
		$obj->XML->writeAttribute("name", _("Last time up"));
		$obj->XML->text($obj->GMT->getDate($dateFormat, $data["last_time_up"]));
		$obj->XML->endElement();

		$obj->XML->startElement("last_time_down");
		$obj->XML->writeAttribute("name", _("Last time down"));
		$obj->XML->text($obj->GMT->getDate($dateFormat, $data["last_time_down"]));
		$obj->XML->endElement();

		$obj->XML->startElement("last_time_unreachable");
		$obj->XML->writeAttribute("name", _("Last time unreachable"));
		$obj->XML->text($obj->GMT->getDate($dateFormat, $data["last_time_unreachable"]));
		$obj->XML->endElement();
	} else {
		$obj->XML->writeElement("infos", "none");
	}
	$DBRESULT->free();

	/*
	 * Translations
	 */
	$obj->XML->writeElement("tr1", _("Check information"), 0);
	$obj->XML->writeElement("tr2", _("Notification information"), 0);
	$obj->XML->writeElement("tr3", _("Last Status Change"), 0);

	/*
	 * End buffer
	 */
	$obj->XML->endElement();


	/*
	 * Send Header
	 */
	$obj->header();

	/*
	 * Send XML
	 */
	$obj->XML->output();
?>