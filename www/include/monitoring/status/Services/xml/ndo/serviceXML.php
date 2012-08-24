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

	include_once "@CENTREON_ETC@/centreon.conf.php";

	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
        include_once $centreon_path . "www/class/centreonInstance.class.php";
        include_once $centreon_path . "www/class/centreonCriticality.class.php";
        include_once $centreon_path . "www/class/centreonMedia.class.php";
        
	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
	CentreonSession::start();

        $criticality = new CentreonCriticality($obj->DB);
	$instanceObj = new CentreonInstance($obj->DB);
        $media = new CentreonMedia($obj->DB);

	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		print "Bad Session ID";
		exit();
	}

	$ar_host_cache = array();
	$ar_service_cache = array();

	function get_service_config_type($object_id, $attribute_name) {
		global $ar_service_cache;

		if (isset($ar_service_cache[$object_id . ":0"])) {
			return $ar_service_cache[$object_id . ":0"][$attribute_name];
		}
		if (isset($ar_service_cache[$object_id . ":1"])) {
			return $ar_service_cache[$object_id . ":1"][$attribute_name];
		}
		return null;
	}

	function get_host_config_type($object_id, $attribute_name) {
		global $ar_host_cache;

		if (isset($ar_host_cache[$object_id . ":1"])) {
			return $ar_host_cache[$object_id . ":1"][$attribute_name];
		}
		if (isset($ar_host_cache[$object_id . ":0"])) {
			return $ar_host_cache[$object_id . ":0"][$attribute_name];
		}
		return null;
	}

	/*
	 * Set Default Poller
	 */
	$obj->getDefaultFilters();

	/* **************************************************
	 * Check Arguments From GET tab
	 */
	$o 			= $obj->checkArgument("o", $_GET, "h");
	$p 			= $obj->checkArgument("p", $_GET, "2");
	$nc 		= $obj->checkArgument("nc", $_GET, "0");
	$num 		= $obj->checkArgument("num", $_GET, 0);
	$limit 		= $obj->checkArgument("limit", $_GET, 20);
	$instance 	= $obj->checkArgument("instance", $_GET, $obj->defaultPoller);
	$hostgroups = $obj->checkArgument("hostgroups", $_GET, $obj->defaultHostgroups);
	$search 	= $obj->checkArgument("search", $_GET, "");
	$search_host	= $obj->checkArgument("search_host", $_GET, "");
	$search_output 	= $obj->checkArgument("search_output", $_GET, "");
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "criticality_id");        
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");
	$search_type_host = $obj->checkArgument("search_type_host", $_GET, 1);
	$search_type_service = $obj->checkArgument("search_type_service", $_GET, 1);
        $criticality_id = $obj->checkArgument('criticality', $_GET, 0);
        
	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);

        
	$selected = "no_s.name1 as host_name, " . $obj->ndoPrefix . "instances.instance_name as instance_name, no_h.object_id as host_object_id, nhs.scheduled_downtime_depth as host_scheduled_downtime_depth, nhs.current_state as host_current_state, nhs.problem_has_been_acknowledged as host_problem_has_been_acknowledged, nhs.passive_checks_enabled as host_passive_checks_enabled, nhs.active_checks_enabled as host_active_checks_enabled";
	$selected .= ", no_s.name2 as service_description, no_s.object_id as service_object_id, nss.process_performance_data as service_process_performance_data, nss.current_state as service_current_state, nss.output as service_output, nss.state_type as service_state_type, nss.current_check_attempt as service_current_check_attempt, nss.status_update_time as service_status_update_time, unix_timestamp(nss.last_state_change) as service_last_state_change, unix_timestamp(nss.last_hard_state_change) as service_last_hard_state_change, unix_timestamp(nss.last_check) as service_last_check, unix_timestamp(nss.next_check) as service_next_check, nss.notifications_enabled as service_notifications_enabled, nss.problem_has_been_acknowledged as service_problem_has_been_acknowledged, nss.passive_checks_enabled as service_passive_checks_enabled, nss.active_checks_enabled as service_active_checks_enabled, nss.event_handler_enabled as service_event_handler_enabled, nss.is_flapping as service_is_flapping, nss.scheduled_downtime_depth as service_scheduled_downtime_depth, nss.flap_detection_enabled as service_flap_detection_enabled";
        $selected .= ", cv.varvalue as criticality, cv.varvalue IS NULL as isnull ";
	$from = $obj->ndoPrefix . "objects as no_h, " .                 
                $obj->ndoPrefix . "hoststatus as nhs, " . 
                $obj->ndoPrefix . "servicestatus as nss, " . 
                $obj->ndoPrefix . "instances, ";
        if ($criticality_id) {
            $from .= $obj->ndoPrefix . "customvariablestatus cvs, ";
        }
        $from .= $obj->ndoPrefix . "objects as no_s LEFT JOIN ".
                 $obj->ndoPrefix . "customvariablestatus cv ON (no_s.object_id = cv.object_id AND cv.varname = 'CRITICALITY_LEVEL') ";

    /* Il faut gerer les hostsgroup */
	$where_hg = "";
	if ($hostgroups && $o != "meta") {
		$from .= ", " . $obj->ndoPrefix . "hostgroup_members as hm, " . $obj->ndoPrefix . "hostgroups as nhg";
		$where_hg = "nhs.host_object_id = hm.host_object_id AND hm.hostgroup_id = nhg.hostgroup_id AND nhg.alias LIKE '".$hostgroups."' AND ";
	}

	$where_unhandled_host = "";
	if (preg_match("/^svc_unhandled/", $o)) {
		$where_unhandled_host = "nhs.problem_has_been_acknowledged = 0 AND nhs.scheduled_downtime_depth = 0 AND ";
	}

	$where_host_meta = "";
	if ($o == "meta") {
		$where_host_meta = "no_h.name1 = '_Module_Meta' AND ";
	} else {
		$where_host_meta = "no_h.name1 NOT LIKE '_Module_%' AND ";
	}

    $where_host_poller = "";
	if ($instance != -1) {
		$where_host_poller = "no_h.instance_id = " . $instance .  " AND ";
	}

	$where_host_host_filter = "";
	if ($search_host) {
		$where_host_host_filter = "no_h.name1 LIKE '%$search_host%' AND ";
	}

	/* Les ACLs */
	$where_acl = "";
	$where_acl_append = "";
	if (!$obj->is_admin) {
		$where_acl = "EXISTS(SELECT 1 FROM centreon_acl WHERE no_s.name1 = centreon_acl.host_name AND no_s.name2 = centreon_acl.service_description AND group_id IN (" . $obj->grouplistStr . "))";
		$where_acl_append = " AND ";
	}

	/* Les etats. On touche pas vraiment */

	$rq_state = "";
	if ($o == "svcpb")
		$rq_state = " AND nss.current_state != 0";
	if ($o == "svc_ok")
		$rq_state = " AND nss.current_state = 0";
	if ($o == "svc_warning")
		$rq_state = " AND nss.current_state = 1";
	if ($o == "svc_critical")
		$rq_state = " AND nss.current_state = 2";
	if ($o == "svc_unknown")
		$rq_state = " AND nss.current_state = 3";
    if ($o == "svc_pending")
		$rq_state = " AND nss.current_state = 4";

	if (preg_match("/^svc_unhandled/", $o)) {
		if (preg_match("/^svc_unhandled_(warning|critical|unknown)\$/", $o, $matches)) {
			if (isset($matches[1]) && $matches[1] == 'warning') {
				$rq_state .= " AND nss.current_state = '1'";
			}
			if (isset($matches[1]) && $matches[1] == "critical") {
				$rq_state .= " AND nss.current_state = '2'";
			} elseif (isset($matches[1]) && $matches[1] == "unknown") {
				$rq_state .= " AND nss.current_state = '3'";
			} elseif (isset($matches[1]) && $matches[1] == "pending") {
				$rq_state .= " AND nss.current_state = '4'";
			} else {
				$rq_state .= " AND nss.current_state != '0'";
			}
		} else {
			$rq_state .= " AND nss.current_state != '0'";
		}
		$rq_state .= " AND nss.state_type = 1";
		$rq_state .= " AND nss.problem_has_been_acknowledged = 0";
		$rq_state .= " AND nss.scheduled_downtime_depth = 0";
	}

	/* filter services */

	$where_service_service = "";
	$where_service_service_append = "";
	if ($search) {
		$where_service_service = "no_s.name2 LIKE '%$search%'";
		$where_service_service_append = " AND ";
	}

	$where_service_output = "";
	$where_service_output_append = "";
	if ($search_output) {
		$where_service_output = "nss.output LIKE '%$search_output%'";
		$where_service_output_append = " AND ";
	}

	/************************/

	$where = " no_h.objecttype_id = 1 
                   AND no_h.object_id = nhs.host_object_id 
                   AND " . $where_unhandled_host . $where_host_poller . $where_host_meta . $where_hg . $where_host_host_filter . "no_h.name1 = no_s.name1 AND no_h.instance_id = no_s.instance_id AND no_s.objecttype_id = 2 AND nss.service_object_id = no_s.object_id AND " . $obj->ndoPrefix . "instances.instance_id = no_s.instance_id" . $where_acl_append . $where_acl . $rq_state . $where_service_service_append . $where_service_service . $where_service_output_append . $where_service_output;

        if ($criticality_id) {
            $where .= " AND no_s.object_id = cvs.object_id 
                        AND cvs.varname = 'CRITICALITY_ID' 
                        AND cvs.varvalue = '".$obj->DBNdo->escape($criticality_id)."' ";
        }
        
	/* LIMIT, ORDER */

	$rq_limit = " LIMIT ".($num * $limit).",".$limit;

	$tabOrder = array();
        $tabOrder["criticality_id"]             = " ORDER BY isnull $order, criticality $order, host_name, service_description";
	$tabOrder["host_name"] 			= " ORDER BY host_name ". $order.", service_description";
	$tabOrder["service_description"]        = " ORDER BY service_description ". $order.", host_name";
	$tabOrder["current_state"] 		= " ORDER BY nss.current_state ". $order.", host_name, service_description";
	$tabOrder["last_state_change"]          = " ORDER BY nss.last_state_change ". $order.", host_name, service_description";
	$tabOrder["last_hard_state_change"]     = " ORDER by nss.last_hard_state_change ". $order.", host_name, service_description";
	$tabOrder["last_check"] 		= " ORDER BY nss.last_check ". $order.", host_name, service_description";
	$tabOrder["current_attempt"]            = " ORDER BY nss.current_check_attempt ". $order.", host_name, service_description";
	$tabOrder["default"] 			= $tabOrder["criticality_id"];
	if (isset($tabOrder[$sort_type])) {
		$rq_sorte = $tabOrder[$sort_type];
	} else {
		$rq_sorte = $tabOrder["default"];
	}

	/**************************/

	$finalRequest = "SELECT " . $selected . " FROM " . $from . " WHERE " . $where . $rq_sorte . $rq_limit;
	$finalRequestCount = "SELECT COUNT(*) as total FROM " . $from . " WHERE " . $where;

	$DBRESULT = $obj->DBNdo->query($finalRequestCount);
	$data = $DBRESULT->fetchRow();
	$numRows = $data['total'];
	$DBRESULT->free();

	#$DBRESULT = $obj->DBNdo->query($finalRequest);
	$all_ndo = $obj->DBNdo->getAll($finalRequest);

	$ar_host_object_id = array();
	$ar_service_object_id = array();
	foreach ($all_ndo as $ndo) {
		$ar_host_object_id[$ndo['host_object_id']] = 1;
		$ar_service_object_id[$ndo['service_object_id']] = 1;
	}

	/* to get from nagios_hosts */
	/*  nh.notes_url as host_notes_url, nh.address as host_address, nh.action_url as host_action_url, nh.notes as host_notes, nh.icon_image as host_icon_image, nh.alias as host_alias */
	if (count($ar_host_object_id)) {
		$DBRESULT = $obj->DBNdo->query("SELECT nh.host_object_id as host_object_id, nh.notes_url as host_notes_url, nh.address as host_address, nh.action_url as host_action_url, nh.notes as host_notes, nh.icon_image as host_icon_image, nh.alias as host_alias, nh.config_type as config_type FROM " . $obj->ndoPrefix . "hosts as nh WHERE nh.host_object_id IN (" . implode(",", array_keys($ar_host_object_id)) . ")");
		while ($host = $DBRESULT->fetchRow()) {
			$ar_host_cache[$host['host_object_id'] . ":" . $host['config_type']] = $host;
		}
		$DBRESULT->free();
	}

	/* to get from nagios_services */
        /*  ns.notes as service_notes, ns.notes_url as service_notes_url, ns.action_url as service_action_url, ns.max_check_attempts as service_max_check_attempts, ns.icon_image as service_icon_image, ns.display_name as service_display_name */
	if (count($ar_service_object_id)) {
		$DBRESULT = $obj->DBNdo->query("SELECT ns.service_object_id as service_object_id, ns.notes as service_notes, ns.notes_url as service_notes_url, ns.action_url as service_action_url, ns.max_check_attempts as service_max_check_attempts, ns.icon_image as service_icon_image, ns.display_name as service_display_name, ns.config_type as config_type FROM " . $obj->ndoPrefix . "services as ns WHERE ns.service_object_id IN (" . implode(",", array_keys($ar_service_object_id)) . ")");
		while ($service = $DBRESULT->fetchRow()) {
			$ar_service_cache[$service['service_object_id'] . ":" . $service['config_type']] = $service;
		}
		$DBRESULT->free();
	}

        /**
         * Get criticality ids
         */
        $critRes = $obj->DBNdo->query("SELECT varvalue, object_id 
                                    FROM nagios_customvariablestatus
                                    WHERE varname = 'CRITICALITY_ID'");
        $criticalityUsed = 0;
        $critCache = array();
        if ($critRes->numRows()) {
            $criticalityUsed = 1;
            while ($critRow = $critRes->fetchRow()) {
                $critCache[$critRow['object_id']] = $critRow['varvalue'];
            }
        }
        
	/* ***************************************************
	 * Create Buffer
	 */
	$obj->XML->startElement("reponse");
	$obj->XML->startElement("i");
	$obj->XML->writeElement("numrows", $numRows);
	$obj->XML->writeElement("num", $num);
	$obj->XML->writeElement("limit", $limit);
	$obj->XML->writeElement("p", $p);
	$obj->XML->writeElement("nc", $nc);
	$obj->XML->writeElement("o", $o);
	$obj->XML->writeElement("hard_state_label", _("Hard State Duration"));
	$obj->XML->writeElement("http_link", _("HTTP Link"));
	$obj->XML->writeElement("http_action_link", _("HTTP Action Link"));
	$obj->XML->writeElement("host_currently_downtime", _("Host is currently on downtime"));
	$obj->XML->writeElement("problem_ack", _("Problem has been acknowledged"));
	$obj->XML->writeElement("host_passive_mode", _("This host is only checked in passive mode"));
	$obj->XML->writeElement("host_never_checked", _("This host is never checked"));
	$obj->XML->writeElement("service_currently_downtime", _("Service is currently on Downtime"));
	$obj->XML->writeElement("service_passive_mode", _("This service is only checked in passive mode"));
	$obj->XML->writeElement("service_not_active_not_passive", _("This service is neither active nor passive"));
	$obj->XML->writeElement("service_flapping", _("This Service is flapping"));
	$obj->XML->writeElement("notif_disabled", _("Notification is disabled"));
        $obj->XML->writeElement("use_criticality", $criticalityUsed);
	$obj->XML->endElement();

	$host_prev = "";
	$ct = 0;
	$flag = 0;        
        
	foreach ($all_ndo as $ndo) {
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";

		if ($ndo["service_last_state_change"] > 0 && time() > $ndo["service_last_state_change"]) {
			$duration = CentreonDuration::toString(time() - $ndo["service_last_state_change"]);
		} else if ($ndo["service_last_state_change"] > 0) {
			$duration = " - ";
		}

		$hard_duration = " N/S ";
		if (($ndo["service_last_hard_state_change"] > 0) && ($ndo["service_last_hard_state_change"] >= $ndo["service_last_state_change"])) {
			$hard_duration = CentreonDuration::toString(time() - $ndo["service_last_hard_state_change"]);
		}

		$class = null;
		if ($ndo["service_scheduled_downtime_depth"] > 0) {
			$class = "line_downtime";
		} else if ($ndo["service_current_state"] == 2) {
			$ndo["service_problem_has_been_acknowledged"] == 1 ? $class = "line_ack" : $class = "list_down";
		} else {
			if ($ndo["service_problem_has_been_acknowledged"] == 1)
				$class = "line_ack";
		}

		$obj->XML->startElement("l");
		$trClass = $obj->getNextLineClass();
		if (isset($class)) {
			$trClass = $class;
		}
		$obj->XML->writeAttribute("class", $trClass);
		$obj->XML->writeElement("o", $ct++);

		if ($host_prev == $ndo["host_name"]) {
			$obj->XML->writeElement("hc", "transparent");
			$obj->XML->startElement("hn");
			$obj->XML->writeAttribute("none", "1");
			$obj->XML->text($ndo["host_name"]);
			$obj->XML->endElement();
			$obj->XML->writeElement("hnl", urlencode($ndo["host_name"]));
			$obj->XML->writeElement("hid", $ndo["host_object_id"]);
		} else {
			$host_prev = $ndo["host_name"];
			if ($ndo["host_scheduled_downtime_depth"] == 0) {
				$obj->XML->writeElement("hc", $obj->colorHostInService[$ndo["host_current_state"]]);
			} else {
				$obj->XML->writeElement("hc", $obj->general_opt['color_downtime']);
			}

			$obj->XML->writeElement("hnl", urlencode($ndo["host_name"]));
			$obj->XML->startElement("hn");
			$obj->XML->writeAttribute("none", "0");
			$obj->XML->text($ndo["host_name"], true, false);
			$obj->XML->endElement();

			$hostNotesUrl = "none";
			if (get_host_config_type($ndo['host_object_id'], "host_notes_url")) {
				$hostNotesUrl = str_replace("\$HOSTNAME\$", $ndo["host_name"], get_host_config_type($ndo['host_object_id'], "host_notes_url"));
				$hostNotesUrl = str_replace("\$HOSTADDRESS\$", get_host_config_type($ndo['host_object_id'], "host_address"), $hostNotesUrl);
				$hostNotesUrl = str_replace("\$INSTANCENAME\$", $ndo["instance_name"], $hostNotesUrl);
				$hostNotesUrl = str_replace("\$INSTANCEADDRESS\$",
											 $instanceObj->getParam($ndo["instance_name"], "ns_ip_address"),
											 $hostNotesUrl);
			}
			$obj->XML->writeElement("hnu", $hostNotesUrl);

			$hostActionUrl = "none";
			if (get_host_config_type($ndo['host_object_id'], "host_action_url")) {
				$hostActionUrl = str_replace("\$HOSTNAME\$", $ndo["host_name"], get_host_config_type($ndo['host_object_id'], "host_action_url"));
				$hostActionUrl = str_replace("\$HOSTADDRESS\$", get_host_config_type($ndo['host_object_id'], "host_address"), $hostActionUrl);
				$hostActionUrl = str_replace("\$INSTANCENAME\$", $ndo["instance_name"], $hostActionUrl);
				$hostActionUrl = str_replace("\$INSTANCEADDRESS\$",
											 $instanceObj->getParam($ndo["instance_name"], "ns_ip_address"),
											 $hostActionUrl);
			}
			$obj->XML->writeElement("hau", $hostActionUrl);

			$obj->XML->writeElement("hnn", get_host_config_type($ndo['host_object_id'], "host_notes"));
			$obj->XML->writeElement("hico", get_host_config_type($ndo['host_object_id'], "host_icon_image"));
			$obj->XML->writeElement("hip", get_host_config_type($ndo['host_object_id'], "host_address"));
			$obj->XML->writeElement("hdtm", $ndo["host_scheduled_downtime_depth"]);
			$obj->XML->writeElement("hdtmXml", "./include/monitoring/downtime/xml/ndo/makeXMLForDowntime.php?sid=".$obj->session_id."&hid=".$ndo["host_object_id"]);
			$obj->XML->writeElement("hdtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
			$obj->XML->writeElement("hackXml", "./include/monitoring/acknowlegement/xml/ndo/makeXMLForAck.php?sid=".$obj->session_id."&hid=".$ndo["host_object_id"]);
			$obj->XML->writeElement("hackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");
			$obj->XML->writeElement("hid", $ndo["host_object_id"]);
		}

		$obj->XML->writeElement("ppd", $ndo["service_process_performance_data"]);
		$obj->XML->writeElement("hs", $ndo["host_current_state"]);

		/*
		 * Add possibility to use display name
		 */
		if (get_service_config_type($ndo['service_object_id'], "service_display_name")) {
			$obj->XML->writeElement("sd", get_service_config_type($ndo['service_object_id'], "service_display_name"), false);
		} else {
			$obj->XML->writeElement("sd", $ndo["service_description"], false);
		}
		$obj->XML->writeElement("sico", get_service_config_type($ndo['service_object_id'], "service_icon_image"));
		$obj->XML->writeElement("sdl", 	urlencode($ndo["service_description"]));
		$obj->XML->writeElement("svc_id", $ndo["service_object_id"]);
		$obj->XML->writeElement("sc", 	$obj->colorService[$ndo["service_current_state"]]);
		$obj->XML->writeElement("cs", 	_($obj->statusService[$ndo["service_current_state"]]), false);
		$obj->XML->writeElement("po", 	$ndo["service_output"]);
		$obj->XML->writeElement("ca", 	$ndo["service_current_check_attempt"]."/".get_service_config_type($ndo['service_object_id'], "service_max_check_attempts")." (".$obj->stateType[$ndo["service_state_type"]].")");
                if ($ndo['criticality'] && isset($critCache[$ndo['service_object_id']])) {
                    $obj->XML->writeElement("hci", 1); // has criticality
                    $critData = $criticality->getData($critCache[$ndo['service_object_id']]);                    
                    $obj->XML->writeElement("ci", $media->getFilename($critData['icon_id']));
                    $obj->XML->writeElement("cih", $critData['name']);
                } else {
                    $obj->XML->writeElement("hci", 0); // has no criticality
                }
		$obj->XML->writeElement("ne", 	$ndo["service_notifications_enabled"]);
		$obj->XML->writeElement("pa", 	$ndo["service_problem_has_been_acknowledged"]);
		$obj->XML->writeElement("pc", 	$ndo["service_passive_checks_enabled"]);
		$obj->XML->writeElement("ac", 	$ndo["service_active_checks_enabled"]);
		$obj->XML->writeElement("eh", 	$ndo["service_event_handler_enabled"]);
		$obj->XML->writeElement("is", 	$ndo["service_is_flapping"]);
		$obj->XML->writeElement("dtm",	$ndo["service_scheduled_downtime_depth"]);
		$obj->XML->writeElement("dtmXml", "./include/monitoring/downtime/xml/ndo/makeXMLForDowntime.php?sid=".$obj->session_id."&hid=".$ndo["host_object_id"]."&svc_id=".$ndo["service_object_id"]);
		$obj->XML->writeElement("dtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
		$obj->XML->writeElement("ackXml", "./include/monitoring/acknowlegement/xml/ndo/makeXMLForAck.php?sid=".$obj->session_id."&hid=".$ndo["host_object_id"]."&svc_id=".$ndo["service_object_id"]);
		$obj->XML->writeElement("ackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");

		if (get_service_config_type($ndo['service_object_id'], "service_notes_url") != "") {
			$service_notes_url = get_service_config_type($ndo['service_object_id'], "service_notes_url");
			$service_notes_url = str_replace("\$SERVICEDESC\$", $ndo["service_description"], $service_notes_url);
			$service_notes_url = str_replace("\$HOSTNAME\$", $ndo["host_name"], $service_notes_url);
			if (get_host_config_type($ndo['host_object_id'], "host_alias")) {
				$service_notes_url = str_replace("\$HOSTALIAS\$", get_host_config_type($ndo['host_object_id'], "host_alias"), $service_notes_url);
			}
			if (get_host_config_type($ndo['host_object_id'], "host_address")) {
				$service_notes_url = str_replace("\$HOSTADDRESS\$", get_host_config_type($ndo['host_object_id'], "host_address"), $service_notes_url);
			}
			if (isset($ndo['instance_name']) && $ndo['instance_name']) {
				$service_notes_url = str_replace("\$INSTANCENAME\$", $ndo['instance_name'], $service_notes_url);
				$service_notes_url = str_replace("\$INSTANCEADDRESS\$",
												$instanceObj->getParam($ndo['instance_name'], 'ns_ip_address'),
												$service_notes_url);
			}
			$obj->XML->writeElement("snu", $service_notes_url);
		} else {
			$obj->XML->writeElement("snu", 'none');
		}

		if (get_service_config_type($ndo['service_object_id'], "service_action_url") != "") {
			$service_action_url = get_service_config_type($ndo['service_object_id'], "service_action_url");
			$service_action_url = str_replace("\$SERVICEDESC\$", $ndo["service_description"], $service_action_url);
			$service_action_url = str_replace("\$HOSTNAME\$", $ndo["host_name"], $service_action_url);
			if (get_host_config_type($ndo['host_object_id'], "host_alias")) {
				$service_action_url = str_replace("\$HOSTALIAS\$", get_host_config_type($ndo['host_object_id'], "host_alias"), $service_action_url);
			}
			if (get_host_config_type($ndo['host_object_id'], "host_address")) {
				$service_action_url = str_replace("\$HOSTADDRESS\$", get_host_config_type($ndo['host_object_id'], "host_address"), $service_action_url);
			}
			if (isset($ndo['instance_name']) && $ndo['instance_name']) {
				$service_action_url = str_replace("\$INSTANCENAME\$", $ndo['instance_name'], $service_action_url);
				$service_action_url = str_replace("\$INSTANCEADDRESS\$",
												 $instanceObj->getParam($ndo['instance_name'], 'ns_ip_address'),
												 $service_action_url);
			}
			$obj->XML->writeElement("sau", $service_action_url);
		} else {
			$obj->XML->writeElement("sau", 'none');
		}


		if (get_service_config_type($ndo['service_object_id'], "service_notes") != "") {
			$service_notes = get_service_config_type($ndo['service_object_id'], "service_notes");
			$service_notes = str_replace("\$SERVICEDESC\$", $ndo["service_description"], $service_notes);
			$service_notes = str_replace("\$HOSTNAME\$", $ndo["host_name"], $service_notes);
			if (get_host_config_type($ndo['host_object_id'], "host_alias")) {
				$service_notes = str_replace("\$HOSTALIAS\$", get_host_config_type($ndo['host_object_id'], "host_alias"), $service_notes);
			}
			if (get_host_config_type($ndo['host_object_id'], "host_address")) {
				$service_notes = str_replace("\$HOSTADDRESS\$", get_host_config_type($ndo['host_object_id'], "host_address"), $service_notes);
			}
			$obj->XML->writeElement("sn", $service_notes);
		} else {
			$obj->XML->writeElement("sn", 'none');
		}

		$obj->XML->writeElement("fd", $ndo["service_flap_detection_enabled"]);
		$obj->XML->writeElement("ha", $ndo["host_problem_has_been_acknowledged"]);
		$obj->XML->writeElement("hae", $ndo["host_active_checks_enabled"]);
		$obj->XML->writeElement("hpe", $ndo["host_passive_checks_enabled"]);
		$obj->XML->writeElement("nc", $obj->GMT->getDate($dateFormat, $ndo["service_next_check"]));
		if ($ndo["service_last_check"] != 0) {
			$obj->XML->writeElement("lc", $obj->GMT->getDate($dateFormat, $ndo["service_last_check"]));
		} else {
			$obj->XML->writeElement("lc", "N/A");
		}
		$obj->XML->writeElement("d", $duration);
		$obj->XML->writeElement("last_hard_state_change", $hard_duration);
		$obj->XML->writeElement("svc_index", getMyIndexGraph4Service($ndo["host_name"], $ndo["service_description"], $obj->DBC));
		$obj->XML->endElement();
	}

	if (!$ct) {
		$obj->XML->writeElement("infos", "none");
	}

	$obj->XML->writeElement("sid", $obj->session_id);
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
