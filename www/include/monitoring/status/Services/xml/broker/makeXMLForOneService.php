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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/include/monitoring/status/Services/xml/ndo/makeXMLForOneService.php $
 * SVN : $Id: makeXMLForOneService.php 11464 2011-01-07 14:53:51Z jmathis $
 *
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";
	//include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/class/centreonDuration.class.php";
	include_once $centreon_path . "www/class/centreonGMT.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonDB.class.php";
	include_once $centreon_path . "www/class/centreonSession.class.php";
	include_once $centreon_path . "www/class/centreon.class.php";
	include_once $centreon_path . "www/class/centreonLang.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	session_start();
	$oreon = $_SESSION['centreon'];

	$centreonlang = new CentreonLang($centreon_path, $oreon);
	$centreonlang->bindLang();

	/*
	 * Call DB connector
	 */
	$pearDB 	= new CentreonDB();
	$pearDBndo 	= new CentreonDB("ndo");

	$ndo_base_prefix = getNDOPrefix();

	/* security check 2/2*/
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid, ENT_QUOTES, "UTF-8");
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if (!$session =& $res->fetchRow())
			get_error('bad session id');
	} else
		get_error('need session identifiant !');

	(isset($_GET["svc_id"]) && !check_injection($_GET["svc_id"])) ? $svc_id = htmlentities($_GET["svc_id"]) : $svc_id = "0";
	(isset($_GET["enable"]) && !check_injection($_GET["enable"])) ? $enable = htmlentities($_GET["enable"]) : $enable = "enable";
	(isset($_GET["disable"]) && !check_injection($_GET["disable"])) ? $disable = htmlentities($_GET["disable"]) : $disable = "disable";
	(isset($_GET["date_time_format_status"]) && !check_injection($_GET["date_time_format_status"])) ? $date_time_format_status = htmlentities($_GET["date_time_format_status"]) : $date_time_format_status = "d/m/Y H:i:s";

	function get_centreon_date($date){
		global $date_time_format_status, $centreonGMT;
		if ($date > 0)
			return $centreonGMT->getDate($date_time_format_status, $date);
		else
			return "N/A";
	}

	/*
	 * Init GMT class
	 */
	$centreonGMT = new CentreonGMT($pearDB);
	$centreonGMT->getMyGMTFromSession($sid, $pearDB);

	/*
	 * Get General Options
	 */
	$general_opt = getStatusColor($pearDB);

	$tab_color_service = array();
	$tab_color_service[0] = $general_opt["color_ok"];
	$tab_color_service[1] = $general_opt["color_warning"];
	$tab_color_service[2] = $general_opt["color_critical"];
	$tab_color_service[3] = $general_opt["color_unknown"];
	$tab_color_service[4] = $general_opt["color_pending"];

	$tab_color_host = array();
	$tab_color_host[0] = $general_opt["color_up"];
	$tab_color_host[1] = $general_opt["color_down"];
	$tab_color_host[2] = $general_opt["color_unreachable"];

	$tab_status_svc = array("0" => _("OK"),
							"1" => _("WARNING"),
							"2" => _("CRITICAL"),
							"3" => _("UNKNOWN"),
							"4" => _("PENDING"));

	$tab_status_host = array("0" => _("UP"),
							 "1" => _("DOWN"),
							 "2" => _("UNREACHABLE"));

	$state_type = array("1" => "HARD", "0" => "SOFT");

	/*
	 * Get Service status
	 */
	$rq1 = "SELECT nss.current_state," .
			" no.name1 as hostname," .
			" no.name2 as service_description," .
			" unix_timestamp(nss.last_check) as last_check," .
			" unix_timestamp(nss.next_check) as next_check," .
			" unix_timestamp(nss.last_state_change) as last_state_change," .
			" unix_timestamp(nss.last_notification) as last_notification," .
			" unix_timestamp(nss.next_notification) as next_notification," .
			" unix_timestamp(nss.last_hard_state_change) as last_hard_state_change," .
			" nss.last_hard_state," .
			" nss.latency," .
			" unix_timestamp(nss.last_time_ok) as last_time_ok," .
			" unix_timestamp(nss.last_time_critical) as last_time_critical," .
			" unix_timestamp(nss.last_time_unknown) as last_time_unknown," .
			" unix_timestamp(nss.last_time_warning) as last_time_warning," .
			" nss.current_notification_number," .
			" nss.scheduled_downtime_depth," .
			" nss.output," .
			" nss.long_output," .
			" ROUND(nss.percent_state_change) as percent_state_change," .
			" nss.notifications_enabled," .
			" nss.perfdata," .
			" nss.state_type," .
			" nss.execution_time," .
			" nss.event_handler_enabled, " .
			" ns.icon_image " .
			" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, ".$ndo_base_prefix."services ns " .
			" WHERE no.object_id = " . $svc_id .
			" AND no.object_id = nss.service_object_id AND ns.service_object_id = no.object_id " .
			" AND no.is_active = 1 AND no.objecttype_id = 2";

	/*
	 * Init Buffer
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("reponse");

	$class = "list_one";
	$en = array("0" => _("No"), "1" => _("Yes"));

	/*
	 * Request
	 */
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if ($ndo =& $DBRESULT_NDO1->fetchRow()){

		$buffer->writeElement("svc_name", $ndo["service_description"], false);

		if ($ndo["icon_image"] == "")
			$icon_image = "./img/icones/16x16/gear.gif";
		else
			$icon_image = "./img/media/" . $ndo["icon_image"];
		$ndo["icon_image"] = $icon_image;

		$duration = "";
		if ($ndo["last_state_change"] > 0)
			$duration = CentreonDuration::toString(time() - $ndo["last_state_change"]);

		$last_notification = "N/A";
		if ($ndo["last_notification"] > 0)
			$last_notification = $ndo["last_notification"];

		$next_notification = "N/A";
		if ($ndo["next_notification"] > 0)
			$next_notification = $ndo["next_notification"];

		$buffer->writeElement("service_description", $ndo["service_description"], false);
		$buffer->writeElement("hostname", $ndo["hostname"], false);
		$buffer->startElement("current_state");
		$buffer->writeAttribute("color", $tab_color_service[$ndo["current_state"]]);
		$buffer->text(_($tab_status_svc[$ndo["current_state"]]), false);
		$buffer->endElement();
		$buffer->writeElement("current_state_name", _("Host Status"), 0);
		$buffer->startElement("plugin_output");
		$buffer->writeAttribute("name", _("Status Information"));
		$buffer->text($ndo["output"], 0, 0);
		$buffer->endElement();

		/*
		 * Long Output
		 */
		$buffer->writeElement("long_name", _("Extended Status Information"), 0);
       	$lo_array = preg_split('/<br \/>|<br>|\\\n|\x0A|\x0D\x0A/', $ndo["long_output"]);
        foreach ($lo_array as $val) {
        	if ($val != "") {
				$buffer->startElement("long_output_data");
	            $buffer->writeElement("lo_data", $val);
	            $buffer->endElement();
	        }
        }

		$tab_perf = split(" ", $ndo["perfdata"]);
		foreach ($tab_perf as $val) {
			$buffer->startElement("performance_data");
			$buffer->writeElement("perf_data", $val);
			$buffer->endElement();
		}
		$buffer->writeElement("performance_data_name", _("Performance Data"), 0);
		$buffer->writeElement("state_type", $state_type[$ndo["state_type"]]);
		$buffer->writeElement("state_type_name", _("State Type"), 0);
		$buffer->writeElement("last_check", get_centreon_date($ndo["last_check"]));
		$buffer->writeElement("last_check_name", _("Last Check"), 0);
		$buffer->writeElement("next_check", get_centreon_date($ndo["next_check"]));
		$buffer->writeElement("next_check_name", _("Next Check"), 0);
		$buffer->writeElement("check_latency", $ndo["latency"]);
		$buffer->writeElement("check_latency_name", _("Latency"), 0);
		$buffer->writeElement("check_execution_time", $ndo["execution_time"]);
		$buffer->writeElement("check_execution_time_name", _("Execution Time"), 0);
		$buffer->writeElement("last_state_change", get_centreon_date($ndo["last_state_change"]));
		$buffer->writeElement("last_state_change_name", _("Last State Change"), 0);
		$buffer->writeElement("duration", $duration);
		$buffer->writeElement("duration_name", _("Current State Duration"), 0);
		$buffer->writeElement("last_notification", get_centreon_date($last_notification));
		$buffer->writeElement("last_notification_name", _("Last Notification"), 0);
		$buffer->writeElement("next_notification", get_centreon_date($next_notification));
		$buffer->writeElement("next_notification_name", _("Next Notification"), 0);
		$buffer->writeElement("current_notification_number", $ndo["current_notification_number"]);
		$buffer->writeElement("current_notification_number_name", _("Current Notification Number"), 0);
		$buffer->writeElement("percent_state_change", $ndo["percent_state_change"]);
		$buffer->writeElement("percent_state_change_name", _("Percent State Change"), 0);
		$buffer->writeElement("is_downtime", ($ndo["scheduled_downtime_depth"] ? $en["1"] : $en["0"]));
		$buffer->writeElement("is_downtime_name", _("In Scheduled Downtime?"), 0);
		$buffer->writeElement("last_update", get_centreon_date( time()));
		$buffer->writeElement("last_update_name", _("Last Update"), 0);
		$buffer->writeElement("ico", $ndo["icon_image"]);

		$buffer->startElement("last_time_ok");
		$buffer->writeAttribute("name", _("Last ok time"));
		$buffer->text(get_centreon_date( $ndo["last_time_ok"]));
		$buffer->endElement();

		$buffer->startElement("last_time_warning");
		$buffer->writeAttribute("name", _("Last warning time"));
		$buffer->text(get_centreon_date( $ndo["last_time_warning"]));
		$buffer->endElement();

		$buffer->startElement("last_time_unknown");
		$buffer->writeAttribute("name", _("Last unknown time"));
		$buffer->text(get_centreon_date( $ndo["last_time_unknown"]));
		$buffer->endElement();

		$buffer->startElement("last_time_critical");
		$buffer->writeAttribute("name", _("Last critical time"));
		$buffer->text(get_centreon_date( $ndo["last_time_critical"]));
		$buffer->endElement();
	} else
		$buffer->writeElement("infos", "none");

	unset($ndo);

	/*
	 * Translations
	 */

	$buffer->writeElement("tr1", _("Check information"), 0);
	$buffer->writeElement("tr2", _("Notification Information"), 0);
	$buffer->writeElement("tr3", _("Last Status Change"), 0);
	/*
	 * End Buffer
	 */

	$buffer->endElement();

	header('Content-type: text/xml;  charset=utf-8');
	header('Cache-Control: no-cache, must-revalidate');
	$buffer->output();
?>