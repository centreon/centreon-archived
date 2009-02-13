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
 * SVN : $Id: 
 * 
 */

	# if debug == 0 => Normal, debug == 1 => get use, debug == 2 => log in file (log.xml)
	$debugXML = 0;
	$buffer = '';

	include_once "@CENTREON_ETC@/centreon.conf.php";
	include_once $centreon_path . "www/class/other.class.php";
	include_once $centreon_path . "www/class/centreonXML.class.php";
	include_once $centreon_path . "www/class/centreonDb.class.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	include_once $centreon_path . "www/class/centreonGMT.class.php";
	
	$pearDB = new CentreonDB();
	$pearDBndo = new CentreonDB("ndo");
	
	$ndo_base_prefix = getNDOPrefix();

	/* security check 2/2*/
	if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
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
	
	$centreonGMT = new CentreonGMT();
	$centreonGMT->getMyGMTFromSession($sid);
	

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

	$tab_status_svc = array("0" => "OK", "1" => "WARNING", "2" => "CRITICAL", "3" => "UNKNOWN", "4" => "PENDING");
	$tab_status_host = array("0" => "UP", "1" => "DOWN", "2" => "UNREACHABLE");

	$state_type = array("1" => "HARD", "0" => "SOFT");

	/* 
	 * Get Host status 
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
			" ROUND(nss.percent_state_change) as percent_state_change," .
			" nss.notifications_enabled," .
			" nss.perfdata," .
			" nss.state_type," .
			" nss.execution_time," .
			" nss.event_handler_enabled" .
			" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
			" WHERE no.object_id = " . $svc_id .
			" AND no.object_id = nss.service_object_id " .
			" AND (no.name1 NOT LIKE '_Module_%'".
			" OR no.name1 LIKE '_Module_Meta')".
			" AND no.is_active = 1 AND no.objecttype_id = 2";
	
	/*
	 * Request
	 */
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	
	/*
	 * Init Buffer
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("reponse");	

	$class = "list_one";
	$en = array("0" => _("No"), "1" => _("Yes"));

	if ($ndo =& $DBRESULT_NDO1->fetchRow()){
		$buffer->writeElement("svc_name", $ndo["service_description"]);		
		$duration = "";
		if ($ndo["last_state_change"] > 0)
			$duration = Duration::toString(time() - $ndo["last_state_change"]);

		$last_notification = "N/A";
		if ($ndo["last_notification"] > 0)
			$last_notification = $ndo["last_notification"];
			
		$next_notification = "N/A";
		if ($ndo["next_notification"] > 0)
			$next_notification = $ndo["next_notification"];
		
		$buffer->writeElement("service_description", $ndo["service_description"]);	
		$buffer->writeElement("hostname", $ndo["hostname"]);
		$buffer->startElement("current_state");
		$buffer->writeAttribute("color", $tab_color_service[$ndo["current_state"]]);
		$buffer->text($tab_status_svc[$ndo["current_state"]]);
		$buffer->endElement();
		$buffer->writeElement("current_state_name", _("Host Status"));
		$buffer->startElement("plugin_output");
		$buffer->writeAttribute("name", _("Status Information"));
		$buffer->text($ndo["output"]);
		$buffer->endElement();
		$buffer->writeElement("performance_data", $ndo["perfdata"]);
		$buffer->writeElement("performance_data_name", _("Performance Data"));
		$buffer->writeElement("state_type", $state_type[$ndo["state_type"]]);
		$buffer->writeElement("state_type_name", _("State Type"));
		$buffer->writeElement("last_check", get_centreon_date($ndo["last_check"]));
		$buffer->writeElement("last_check_name", _("Last Check"));
		$buffer->writeElement("next_check", get_centreon_date($ndo["next_check"]));		
		$buffer->writeElement("next_check_name", _("Next Check"));
		$buffer->writeElement("check_latency", $ndo["latency"]);
		$buffer->writeElement("check_latency_name", _("Latency"));
		$buffer->writeElement("check_execution_time", $ndo["execution_time"]);
		$buffer->writeElement("check_execution_time_name", _("Execution Time"));
		$buffer->writeElement("last_state_change", get_centreon_date($ndo["last_state_change"]));
		$buffer->writeElement("last_state_change_name", _("Last State Change"));
		$buffer->writeElement("duration", $duration);
		$buffer->writeElement("duration_name", _("Current State Duration"));		
		$buffer->writeElement("last_notification", get_centreon_date($last_notification));
		$buffer->writeElement("last_notification_name", _("Last Notification"));	
		$buffer->writeElement("next_notification", get_centreon_date($next_notification));
		$buffer->writeElement("next_notification_name", _("Next Notification"));
		$buffer->writeElement("current_notification_number", $ndo["current_notification_number"]);
		$buffer->writeElement("current_notification_number_name", _("Current Notification Number"));
		$buffer->writeElement("percent_state_change", $ndo["percent_state_change"]);		
		$buffer->writeElement("percent_state_change_name", _("Percent State Change"));
		$buffer->writeElement("is_downtime", $en[$ndo["scheduled_downtime_depth"]]);
		$buffer->writeElement("is_downtime_name", _("In Scheduled Downtime?"));
		$buffer->writeElement("last_update", get_centreon_date( time()));
		$buffer->writeElement("last_update_name", _("Last Update"));
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

	/*
	 * Translations
	 */

	$buffer->writeElement("tr1", _("Check information"));
	$buffer->writeElement("tr2", _("Notification Information"));
	$buffer->writeelement("tr3", _("Last Status Change"));			
	/*
	 * End Buffer
	 */

	$buffer->endElement();

	header('Content-type: text/xml; charset=iso-8859-1');
	header('Cache-Control: no-cache, must-revalidate');	
	$buffer->output();
?>