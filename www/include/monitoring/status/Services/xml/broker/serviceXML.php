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
	 * Require configuration.
	 */
	include_once "@CENTREON_ETC@/centreon.conf.php";

	/**
	 * Require Sepecific XML / Ajax Class
	 */
	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";
	include_once $centreon_path . "www/class/centreonInstance.class.php";
        include_once $centreon_path . "www/class/centreonCriticality.class.php";
        include_once $centreon_path . "www/class/centreonMedia.class.php";
        
	/**
	 * Require commonu Files.
	 */
	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/**
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

	/**
	 * Set Default Poller
	 */
	$obj->getDefaultFilters();

	/** **************************************************
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
        $criticality_id = $obj->checkArgument('criticality', $_GET, $obj->defaultCriticality);
        
	/** **************************************************
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);
    
    /** **************************************************
	 * Backup criticality id
	 */
	$obj->setCriticality($criticality_id);

	/**
	 * Graphs Tables
	 */
	$graphs = array();

	/** **************************************************
	 * Get Service status
	 */

	$instance_filter = "";
	if ($instance != -1) {
		$instance_filter = " AND h.instance_id = ".$instance." ";
	}

	$searchHost = "";
	if ($search_host) {
		$searchHost .= " AND h.name LIKE '%$search_host%' ";
	}

	$searchService = "";
	if ($search) {
		$searchService .= " AND s.description LIKE '%$search%' ";
	}

	$searchOutput = "";
	if ($search_output) {
		$searchOutput .= " AND s.output LIKE '%$search_output%' ";
	}

	$tabOrder = array();
        $tabOrder["criticality_id"]             = " ORDER BY isnull $order, criticality $order, h.name, s.description ";
	$tabOrder["host_name"] 			= " ORDER BY h.name ". $order.", s.description ";
	$tabOrder["service_description"]= " ORDER BY s.description ". $order.", h.name";
	$tabOrder["current_state"] 		= " ORDER BY s.state ". $order.", h.name, s.description";
	$tabOrder["last_state_change"] 	= " ORDER BY s.last_state_change ". $order.", h.name, s.description";
	$tabOrder["last_hard_state_change"] = " ORDER by s.last_hard_state_change ". $order.", h.name, s.description";
	$tabOrder["last_check"] 		= " ORDER BY s.last_check ". $order.", h.name, s.description";
	$tabOrder["current_attempt"] 	= " ORDER BY s.check_attempt ". $order.", h.name, s.description";
	$tabOrder["output"] 			= " ORDER BY s.output ". $order.", h.name, s.description";
	$tabOrder["default"] 			= $tabOrder['criticality_id'];

	$request = "SELECT SQL_CALC_FOUND_ROWS DISTINCT h.name, h.host_id, s.description, s.service_id, s.notes, s.notes_url, s.action_url, s.max_check_attempts,
				s.icon_image, s.display_name, s.process_perfdata, s.state, s.output as plugin_output,
				s.state_type, s.check_attempt as current_attempt, s.last_update as status_update_time, s.last_state_change,
				s.last_hard_state_change, s.last_check, s.next_check,
				s.notify, s.acknowledged, s.passive_checks, s.active_checks, s.event_handler_enabled, s.flapping,
				s.scheduled_downtime_depth, s.flap_detection, h.state as host_state, h.acknowledged AS h_acknowledged, h.scheduled_downtime_depth AS h_scheduled_downtime_depth,
				h.icon_image AS h_icon_images, h.display_name AS h_display_name, h.action_url AS h_action_url, h.notes_url AS h_notes_url, h.notes AS h_notes, h.address,
				h.passive_checks AS h_passive_checks, h.active_checks AS h_active_checks, i.name as instance_name, cv.value as criticality, cv.value IS NULL as isnull ";
	$request .= " FROM hosts h, instances i ";
	if (isset($hostgroups) && $hostgroups != 0) {
		$request .= ", hosts_hostgroups hg ";
	}
        if ($criticality_id) {
            $request .= ", customvariables cvs ";
        }
	if (!$obj->is_admin) {
		$request .= ", centreon_acl ";
	}
        $request .= ", services s LEFT JOIN customvariables cv ON (s.service_id = cv.service_id AND cv.host_id = s.host_id AND cv.name = 'CRITICALITY_LEVEL') ";
	$request .= " WHERE h.host_id = s.host_id
				  AND s.service_id IS NOT NULL
				  AND s.service_id != 0
				  AND s.enabled = 1
				  AND h.enabled = 1
				  AND h.instance_id = i.instance_id ";
        if ($criticality_id) {
            $request .= " AND s.service_id = cvs. service_id
                          AND cvs.host_id = h.host_id
                          AND cvs.name = 'CRITICALITY_ID'
                          AND cvs.value = '" . $obj->DBC->escape($criticality_id)."' ";
        }
        
	if ($searchHost) {
		$request .= $searchHost;
	}
	if ($searchService) {
		$request .= $searchService;
	}
	if ($searchOutput) {
		$request .= $searchOutput;
	}
	$request .= $instance_filter;

	if (preg_match("/^svc_unhandled/", $o)) {
		if (preg_match("/^svc_unhandled_(warning|critical|unknown)\$/", $o, $matches)) {
			if (isset($matches[1]) && $matches[1] == 'warning') {
				$request .= " AND s.state = '1' ";
			}
			if (isset($matches[1]) && $matches[1] == "critical") {
				$request .= " AND s.state = '2' ";
			} elseif (isset($matches[1]) && $matches[1] == "unknown") {
				$request .= " AND s.state = '3' ";
			} elseif (isset($matches[1]) && $matches[1] == "pending") {
				$request .= " AND s.state = '4' ";
			}else {
				$request .= " AND s.state != '0' ";
			}
		} else {
			$request .= " AND (s.state != 0 AND s.state != 4) ";
		}
		$request .= " AND s.state_type = 1";
		$request .= " AND s.acknowledged = 0";
		$request .= " AND s.scheduled_downtime_depth = 0";
		$request .= " AND h.acknowledged = 0 AND h.scheduled_downtime_depth = 0 ";
	} else if ($o == "svcpb") {
		$request .= " AND s.state != 0 AND s.state != 4 ";
	} else if ($o == "svc_ok") {
		$request .= " AND s.state = 0";
	} else if ($o == "svc_warning") {
		$request .= " AND s.state = 1";
	} else if ($o == "svc_critical") {
		$request .= " AND s.state = 2";
	} else if ($o == "svc_unknown") {
		$request .= " AND s.state = 3";
	} else if ($o == "svc_pending") {
		$request .= " AND s.state = 4";
	}
    $request .= " AND h.name NOT LIKE '_Module_%' ";

	/**
	 * HostGroup Filter
	 */
	if (isset($hostgroups) && $hostgroups != 0) {
		$request .= " AND hg.host_id = h.host_id AND hg.hostgroup_id IN (".$hostgroups. ") ";
	}

	/**
	 * ACL activation
	 */
	if (!$obj->is_admin) {
		$request .= " AND h.host_id = centreon_acl.host_id AND s.service_id = centreon_acl.service_id AND group_id IN (".$obj->grouplistStr.") ";
	}

	(isset($tabOrder[$sort_type])) ? $request .= $tabOrder[$sort_type] : $request .= $tabOrder["default"];
	$request .= " LIMIT ".($num * $limit).",".$limit;

	/** ***************************************************
	 * Get Pagination Rows
	 */
	$DBRESULT = $obj->DBC->query($request);
	$numRows = $obj->DBC->numberRows();

        /**
         * Get criticality ids
         */
        $critRes = $obj->DBC->query("SELECT value, service_id 
                                       FROM customvariables
                                       WHERE name = 'CRITICALITY_ID'
                                       AND service_id IS NOT NULL");
        $criticalityUsed = 0;
        $critCache = array();
        if ($critRes->numRows()) {
            $criticalityUsed = 1;
            while ($critRow = $critRes->fetchRow()) {
                $critCache[$critRow['service_id']] = $critRow['value'];
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

	while ($data = $DBRESULT->fetchRow()) {
		$passive = 0;
		$active = 1;
		$last_check = " ";
		$duration = " ";

		if ($data["last_state_change"] > 0 && time() > $data["last_state_change"]) {
			$duration = CentreonDuration::toString(time() - $data["last_state_change"]);
		} else if ($data["last_state_change"] > 0) {
			$duration = " - ";
		}

		$hard_duration = " N/S ";
		if (($data["last_hard_state_change"] > 0) && ($data["last_hard_state_change"] >= $data["last_state_change"])) {
			$hard_duration = CentreonDuration::toString(time() - $data["last_hard_state_change"]);
		}

		$class = null;
		if ($data["scheduled_downtime_depth"] > 0) {
			$class = "line_downtime";
		} else if ($data["state"] == 2) {
			$data["acknowledged"] == 1 ? $class = "line_ack" : $class = "list_down";
		} else {
			if ($data["acknowledged"] == 1)
				$class = "line_ack";
		}

		$obj->XML->startElement("l");
		$trClass = $obj->getNextLineClass();
        if (isset($class)) {
            $trClass = $class;
        }
        $obj->XML->writeAttribute("class", $trClass);
		$obj->XML->writeElement("o", $ct++);

		if ($host_prev == $data["name"]) {
			$obj->XML->writeElement("hc", "transparent");
			$obj->XML->startElement("hn");
			$obj->XML->writeAttribute("none", "1");
			$obj->XML->text($data["name"]);
			$obj->XML->endElement();
			$obj->XML->writeElement("hnl", urlencode($data["name"]));
			$obj->XML->writeElement("hid", $data["host_id"]);
		} else {
			$host_prev = $data["name"];
			if ($data["h_scheduled_downtime_depth"] == 0) {
				$obj->XML->writeElement("hc", $obj->colorHostInService[$data["host_state"]]);
			} else {
				$obj->XML->writeElement("hc", $obj->general_opt['color_downtime']);
			}

			$obj->XML->writeElement("hnl", urlencode($data["name"]));
			$obj->XML->startElement("hn");
			$obj->XML->writeAttribute("none", "0");
			$obj->XML->text($data["name"], true, false);
			$obj->XML->endElement();

			$hostNotesUrl = "none";
			if ($data["h_notes_url"]) {
				$hostNotesUrl = str_replace("\$HOSTNAME\$", $data["name"], $data["h_notes_url"]);
				$hostNotesUrl = str_replace("\$HOSTADDRESS\$", $data["address"], $hostNotesUrl);
				$hostNotesUrl = str_replace("\$INSTANCENAME\$", $data["instance_name"], $hostNotesUrl);
				$hostNotesUrl = str_replace("\$INSTANCEADDRESS\$",
				                            $instanceObj->getParam($data["instance_name"], "ns_ip_address"),
				                            $hostNotesUrl);
			}
			$obj->XML->writeElement("hnu", $hostNotesUrl);

			$hostActionUrl = "none";
		    if ($data["h_action_url"]) {
				$hostActionUrl = str_replace("\$HOSTNAME\$", $data["name"], $data["h_action_url"]);
				$hostActionUrl = str_replace("\$HOSTADDRESS\$", $data["address"], $hostActionUrl);
				$hostActionUrl = str_replace("\$INSTANCENAME\$", $data["instance_name"], $hostActionUrl);
				$hostActionUrl = str_replace("\$INSTANCEADDRESS\$",
				                            $instanceObj->getParam($data["instance_name"], "ns_ip_address"),
				                            $hostActionUrl);
			}
			$obj->XML->writeElement("hau", $hostActionUrl);

			$obj->XML->writeElement("hnn", 	$data["h_notes"]);
			$obj->XML->writeElement("hico", $data["h_icon_images"]);
			$obj->XML->writeElement("hip", 	$data["address"]);
			$obj->XML->writeElement("hdtm", $data["h_scheduled_downtime_depth"]);
			$obj->XML->writeElement("hdtmXml", "./include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?sid=".$obj->session_id."&hid=".$data['host_id']);
		    $obj->XML->writeElement("hdtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
		    $obj->XML->writeElement("hackXml", "./include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?sid=".$obj->session_id."&hid=".$data['host_id']);
		    $obj->XML->writeElement("hackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");
			$obj->XML->writeElement("hid", 	$data["host_id"]);
		}
		$obj->XML->writeElement("ppd", 	$data["process_perfdata"]);
		$obj->XML->writeElement("hs", 	$data["host_state"]);

		/*
		 * Add possibility to use display name
		 */
		if (isset($data["display_name"]) && $data["display_name"]) {
			$obj->XML->writeElement("sd", 	$data["display_name"], false);
		} else {
			$obj->XML->writeElement("sd", 	$data["description"], false);
		}
		$obj->XML->writeElement("sico", $data["icon_image"]);
		$obj->XML->writeElement("sdl", 	urlencode($data["description"]));
		$obj->XML->writeElement("svc_id", $data["service_id"]);
		$obj->XML->writeElement("sc", 	$obj->colorService[$data["state"]]);
		$obj->XML->writeElement("cs", 	_($obj->statusService[$data["state"]]), false);
		$obj->XML->writeElement("po", 	$data["plugin_output"]);
		$obj->XML->writeElement("ca", 	$data["current_attempt"]."/".$data["max_check_attempts"]." (".$obj->stateType[$data["state_type"]].")");
                if ($data['criticality'] && isset($critCache[$data['service_id']])) {
                    $obj->XML->writeElement("hci", 1); // has criticality
                    $critData = $criticality->getData($critCache[$data['service_id']]);
                    $obj->XML->writeElement("ci", $media->getFilename($critData['icon_id']));
                    $obj->XML->writeElement("cih", $critData['name']);
                } else {
                    $obj->XML->writeElement("hci", 0); // has no criticality
                }
		$obj->XML->writeElement("ne", 	$data["notify"]);
		$obj->XML->writeElement("pa", 	$data["acknowledged"]);
		$obj->XML->writeElement("pc", 	$data["passive_checks"]);
		$obj->XML->writeElement("ac", 	$data["active_checks"]);
		$obj->XML->writeElement("eh", 	$data["event_handler_enabled"]);
		$obj->XML->writeElement("is", 	$data["flapping"]);
		$obj->XML->writeElement("dtm",	$data["scheduled_downtime_depth"]);
		$obj->XML->writeElement("dtmXml", "./include/monitoring/downtime/xml/broker/makeXMLForDowntime.php?sid=".$obj->session_id."&hid=".$data['host_id']."&svc_id=".$data['service_id']);
		$obj->XML->writeElement("dtmXsl", "./include/monitoring/downtime/xsl/popupForDowntime.xsl");
		$obj->XML->writeElement("ackXml", "./include/monitoring/acknowlegement/xml/broker/makeXMLForAck.php?sid=".$obj->session_id."&hid=".$data['host_id']."&svc_id=".$data['service_id']);
		$obj->XML->writeElement("ackXsl", "./include/monitoring/acknowlegement/xsl/popupForAck.xsl");

		if ($data["notes_url"] != "") {
			$data["notes_url"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["notes_url"]);
			$data["notes_url"] = str_replace("\$HOSTNAME\$", $data["name"], $data["notes_url"]);
			if (isset($data["alias"]) && $data["alias"]) {
			    $data["notes_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes_url"]);
			}
			if (isset($data['address']) && $data['address']) {
                $data["notes_url"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["notes_url"]);
			}
			if (isset($data['instance_name']) && $data['instance_name']) {
                $data["notes_url"] = str_replace("\$INSTANCENAME\$", $data['instance_name'], $data['notes_url']);
                $data["notes_url"] = str_replace("\$INSTANCEADDRESS\$",
                                                 $instanceObj->getParam($data['instance_name'], 'ns_ip_address'),
                                                 $data["notes_url"]);
			}
			$obj->XML->writeElement("snu", $data["notes_url"]);
		} else {
			$obj->XML->writeElement("snu", 'none');
		}

	    if ($data["action_url"] != "") {
			$data["action_url"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["action_url"]);
			$data["action_url"] = str_replace("\$HOSTNAME\$", $data["name"], $data["action_url"]);
			if (isset($data["alias"]) && $data["alias"]) {
			    $data["action_url"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["action_url"]);
			}
			if (isset($data['address']) && $data['address']) {
                    $data["action_url"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["action_url"]);
			}
	        if (isset($data['instance_name']) && $data['instance_name']) {
                $data["action_url"] = str_replace("\$INSTANCENAME\$", $data['instance_name'], $data['action_url']);
                $data["action_url"] = str_replace("\$INSTANCEADDRESS\$",
                                                 $instanceObj->getParam($data['instance_name'], 'ns_ip_address'),
                                                 $data["action_url"]);
			}
			$obj->XML->writeElement("sau", $data["action_url"]);
		} else {
			$obj->XML->writeElement("sau", 'none');
		}

		if ($data["notes"] != "") {
			$data["notes"] = str_replace("\$SERVICEDESC\$", $data["description"], $data["notes"]);
			$data["notes"] = str_replace("\$HOSTNAME\$", $data["name"], $data["notes"]);
		    if (isset($data["alias"]) && $data["alias"]) {
			    $data["notes"] = str_replace("\$HOSTALIAS\$", $data["alias"], $data["notes"]);
			}
			if (isset($data['address']) && $data['address']) {
                $data["notes"] = str_replace("\$HOSTADDRESS\$", $data['address'], $data["notes"]);
			}
			$obj->XML->writeElement("sn", $data["notes"]);
		} else {
			$obj->XML->writeElement("sn", 'none');
		}

		$obj->XML->writeElement("fd", $data["flap_detection"]);
		$obj->XML->writeElement("ha", $data["h_acknowledged"]);
		$obj->XML->writeElement("hae", $data["h_active_checks"]);
		$obj->XML->writeElement("hpe", $data["h_passive_checks"]);
		$obj->XML->writeElement("nc", $obj->GMT->getDate($dateFormat, $data["next_check"]));
		if ($data["last_check"] != 0) {
			$obj->XML->writeElement("lc", $obj->GMT->getDate($dateFormat, $data["last_check"]));
		} else {
			$obj->XML->writeElement("lc", "N/A");
		}
		$obj->XML->writeElement("d", $duration);
		$obj->XML->writeElement("last_hard_state_change", $hard_duration);

		/**
		 * Get Service Graph index
		 */
		if (!isset($graphs[$data["host_id"]]) || !isset($graphs[$data["host_id"]][$data["service_id"]])) {
			$request2 = "SELECT service_id, id FROM index_data, metrics WHERE metrics.index_id = index_data.id AND host_id = '".$data["host_id"]."' AND service_id = '".$data["service_id"]."' AND index_data.hidden = '0'";
			$DBRESULT2 = $obj->DBC->query($request2);
			while ($dataG = $DBRESULT2->fetchRow()) {
				if (!isset($graphs[$data["host_id"]])) {
					$graphs[$data["host_id"]] = array();
				}
				$graphs[$data["host_id"]][$dataG["service_id"]] = $dataG["id"];
			}
			if (!isset($graphs[$data["host_id"]])) {
				$graphs[$data["host_id"]] = array();
			}
		}
		$obj->XML->writeElement("svc_index", (isset($graphs[$data["host_id"]][$data["service_id"]]) ? $graphs[$data["host_id"]][$data["service_id"]] : 0));

		$obj->XML->endElement();
	}
	$DBRESULT->free();
	unset($data);
	unset($host_status);

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