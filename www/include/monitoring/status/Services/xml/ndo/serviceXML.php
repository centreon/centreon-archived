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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";

	include_once $centreon_path . "www/class/centreonXMLBGRequest.class.php";

	include_once $centreon_path . "www/include/monitoring/status/Common/common-Func.php";
	include_once $centreon_path . "www/include/common/common-Func.php";

	/*
	 * Create XML Request Objects
	 */
	$obj = new CentreonXMLBGRequest($_GET["sid"], 1, 1, 0, 1);
	CentreonSession::start();

	if (isset($obj->session_id) && CentreonSession::checkSession($obj->session_id, $obj->DB)) {
		;
	} else {
		print "Bad Session ID";
		exit();
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
	$sort_type 	= $obj->checkArgument("sort_type", $_GET, "host_name");
	$order 		= $obj->checkArgument("order", $_GET, "ASC");
	$dateFormat = $obj->checkArgument("date_time_format_status", $_GET, "d/m/Y H:i:s");
	$search_type_host = $obj->checkArgument("search_type_host", $_GET, 1);
	$search_type_service = $obj->checkArgument("search_type_service", $_GET, 1);

	/*
	 * Backup poller selection
	 */
	$obj->setInstanceHistory($instance);

	$service = array();
	$host_status = array();
	$service_status = array();
	$host_services = array();
	$metaService_status = array();
	$tab_host_service = array();

	/* *************************************************
	 * Get Host status
	 */
	$rq1 = 	"SELECT DISTINCT no.name1 as host_name, nh.address, nh.alias as host_alias, " .
			" nhs.current_state, nhs.problem_has_been_acknowledged, nhs.scheduled_downtime_depth, " .
			" nhs.passive_checks_enabled, nhs.active_checks_enabled, no.object_id, nh.action_url," .
			" nh.notes_url, nh.notes, nh.icon_image, nh.address " .
			" FROM ".$obj->ndoPrefix."hoststatus nhs, ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."hosts nh ";
	if ($hostgroups) {
		$rq1 .= ", ".$obj->ndoPrefix."hostgroup_members hm ";
	}

	$rq1 .= " WHERE no.object_id = nhs.host_object_id " .
			" 	AND nh.host_object_id = no.object_id " .
			" 	AND no.objecttype_id = 1 " .
			" 	AND no.object_id = nh.host_object_id";

	if ($hostgroups && $o != "meta") {
		$rq1 .= " AND nh.host_object_id = hm.host_object_id AND hm.hostgroup_id IN ";
		$rq1 .= " (SELECT hostgroup_id FROM ".$obj->ndoPrefix."hostgroups WHERE alias LIKE '".$hostgroups."')";
	}

	$rq1 .= (preg_match("/^svc_unhandled/", $o)) ? " AND nhs.problem_has_been_acknowledged = 0 AND nhs.scheduled_downtime_depth = 0 " : "";
	$rq1 .= ($o == "meta") ?" AND no.name1 = '_Module_Meta'" : " AND no.name1 != '_Module_Meta'";
	$rq1 .= ($instance != -1) ? " AND no.instance_id = ".$instance : "" ;

	$DBRESULT =& $obj->DBNdo->query($rq1);
	$hostHGString = "";
	while ($ndo =& $DBRESULT->fetchRow()) {
		/*
		 * HG List
		 */
		if ($hostHGString != "")
			$hostHGString .= ',';
		$hostHGString .= "'".$ndo["host_name"]."'";
		/*
		 * Generate Host data list
		 */
		$host_status[$ndo["host_name"]] = $ndo;
	}
	$DBRESULT->free();

	/* **************************************************
	 * Get Service status
	 */

	$tabOrder = array();
	$tabOrder["host_name"] 			= " ORDER BY host_name ". $order.", service_description";
	$tabOrder["service_description"]= " ORDER BY service_description ". $order.", host_name";
	$tabOrder["current_state"] 		= " ORDER BY nss.current_state ". $order.", host_name, service_description";
	$tabOrder["last_state_change"] 	= " ORDER BY nss.last_state_change ". $order.", host_name, service_description";
	$tabOrder["last_hard_state_change"] = " ORDER by nss.last_hard_state_change ". $order.",no.name1,no.name2";
	$tabOrder["last_check"] 		= " ORDER BY nss.last_check ". $order.", host_name, service_description";
	$tabOrder["current_attempt"] 	= " ORDER BY nss.current_check_attempt ". $order.", host_name, service_description";
	$tabOrder["default"] 			= " ORDER BY host_name ". $order ;

	if (isset($tabOrder[$sort_type])) {
		$rq_sorte = $tabOrder[$sort_type];
	} else {
		$rq_sorte = $tabOrder["default"];
	}

	$rq_limit = " LIMIT ".($num * $limit).",".$limit;

	$ArgNeeded = "A.host_name, A.object_id, A.service_description, A.notes, A.notes_url, A.action_url, A.max_check_attempts, A.icon_image,
				  A.display_name, nss.process_performance_data, nss.current_state, nss.output as plugin_output,
				  nss.state_type as state_type, nss.current_check_attempt as current_attempt, nss.status_update_time as status_update_time,
				  unix_timestamp(nss.last_state_change) as last_state_change, unix_timestamp(nss.last_hard_state_change) as last_hard_state_change,
				  unix_timestamp(nss.last_check) as last_check, unix_timestamp(nss.next_check) as next_check, nss.notifications_enabled,
				  nss.problem_has_been_acknowledged, nss.passive_checks_enabled, nss.active_checks_enabled, nss.event_handler_enabled, nss.is_flapping,
				  nss.scheduled_downtime_depth, nss.flap_detection_enabled";

	$ACLDBName = "";
	if (!$obj->is_admin)
		$ACLDBName = ", centreon_acl ";

	$ACLCondition = "";
	if (!$obj->is_admin)
		$ACLCondition = " AND no.name1 = centreon_acl.host_name AND no.name2 = centreon_acl.service_description AND group_id IN ($obj->grouplistStr)";

	/*
	 * Prepare current_state condition
	 */
	$rq_state = "";

	$instance_filter = "";
	if ($instance != -1) {
		$instance_filter = " AND no.instance_id = ".$instance." ";
	}

	if ($o == "svcpb")
		$rq_state = " AND nss.current_state != 0";
	if ($o == "svc_ok")
		$rq_state = " AND nss.current_state = 0 ";
	if ($o == "svc_warning")
		$rq_state = " AND nss.current_state = 1 ";
	if ($o == "svc_critical")
		$rq_state = " AND nss.current_state = 2 ";
	if ($o == "svc_unknown")
		$rq_state = " AND nss.current_state = 3 ";


	if (preg_match("/^svc_unhandled/", $o)) {
		if (preg_match("/^svc_unhandled_(warning|critical|unknown)\$/", $o, $matches)) {
			if (isset($matches[1]) && $matches[1] == 'warning') {
				$rq_state .= " AND nss.current_state = '1' ";
			}
			if (isset($matches[1]) && $matches[1] == "critical") {
				$rq_state .= " AND nss.current_state = '2' ";
			} elseif (isset($matches[1]) && $matches[1] == "unknown") {
				$rq_state .= " AND nss.current_state = '3' ";
			} else {
				$rq_state .= " AND nss.current_state != '0' ";
			}
		} else {
			$rq_state .= " AND nss.current_state != '0'";
		}
		$rq_state .= " AND nss.state_type = 1";
		$rq_state .= " AND nss.problem_has_been_acknowledged = 0";
		$rq_state .= " AND nss.scheduled_downtime_depth = 0";
	}

	$searchHost = "";
	if ($search_host) {
		if ($search  && $search_host)
			$searchHost .= " AND (";
		else
			$searchHost .= " AND ";
		$searchHost .= "no.name1 LIKE '%$search_host%' ";
	}


	$searchService = "";
	if ($search) {
		if ($search) {
			$searchService .= " AND ";
		}
		$searchService .= " no.name2 LIKE '%$search%' ";
	}
	if ($search_host && $search) {
		$searchService .= ")";
	}

	$searchOutput = "";
	if ($search_output) {
		$searchOutput .= " AND nss.output LIKE '%$search_output%' ";
	}



	$hgCondition = "";
	if ($hostgroups) {
		if ($hostHGString != "") {
		    $hgCondition = " AND no.name1 IN ($hostHGString) ";
		} else {
			$hgCondition = " AND no.name1 IN ('') ";
		}
	}

	$rq3 = 	"SELECT 1 FROM ".$obj->ndoPrefix."servicestatus WHERE no.object_id = ns.service_object_id  ";

	$rq1 = 	"SELECT $ArgNeeded " .
		 	"FROM (";

	$rq2 = 	"SELECT DISTINCT no.name1 as host_name, no.object_id, no.name2 as service_description, " .
			"ns.notes, ns.notes_url, ns.icon_image, ns.action_url, ns.max_check_attempts, ns.display_name FROM  ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."services ns $ACLDBName" .
			"WHERE no.object_id = ns.service_object_id " .
			"	AND no.name1 NOT LIKE '_Module_%' " .
			"	$hgCondition $searchHost $searchService $instance_filter $ACLCondition " .
			"	AND objecttype_id = 2 " .
			"	AND EXISTS ($rq3)" .
			"	) A, " .
		 	"".$obj->ndoPrefix."servicestatus nss WHERE A.object_id = nss.service_object_id $searchOutput $rq_state $rq_sorte $rq_limit ";

	$finalRequest = $rq1 . $rq2;

	/* ***************************************************
	 * Get Pagination Rows
	 */

	/*
	 * Get Pagination Rows
	 */
	if ($hostgroups) {
		$hgCondition = " AND ns.host_object_id = hm.host_object_id AND hm.host_object_id = ns.host_object_id AND hm.hostgroup_id = hg.hostgroup_id AND hg.alias LIKE '".$hostgroups."' ";
	} else {
		$hgCondition = "";
	}

	if ($obj->is_admin) {
		$rq = " SELECT count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2))) " .
						 " FROM ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."services ns ".(isset($hgCondition) && $hgCondition != "" ? ", nagios_hostgroup_members hm, nagios_hostgroups hg " : " ");
		if ($o == "svc_unhandled") {
            $rq .= ", " . $obj->ndoPrefix."hoststatus hs, ".$obj->ndoPrefix."services s ";
		}
        $rq .= " WHERE no.object_id = ns.service_object_id AND no.object_id = nss.service_object_id $rq_state $instance_filter AND no.name1 NOT LIKE '_Module_%' $hgCondition $searchHost $searchService ";
        if ($o == "svc_unhandled") {
            $rq .= " AND nss.service_object_id  = s.service_object_id
            	    AND s.host_object_id = hs.host_object_id
            	    AND hs.scheduled_downtime_depth = 0
            	    AND hs.problem_has_been_acknowledged = 0";
        }
	} else {
		$rq = " SELECT count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2))) " .
						 " FROM ".$obj->ndoPrefix."objects no, ".$obj->ndoPrefix."servicestatus nss, ".$obj->ndoPrefix."services ns, centreon_acl ".(isset($hgCondition) && $hgCondition != "" ? ", nagios_hostgroup_members hm, nagios_hostgroups hg " : " ");
	    if ($o == "svc_unhandled") {
            $rq .= ", " . $obj->ndoPrefix."hoststatus hs, ".$obj->ndoPrefix."services s ";
		}
		$rq .= " WHERE no.object_id = ns.service_object_id AND no.object_id = nss.service_object_id $rq_state $instance_filter AND no.name1 NOT LIKE '_Module_%' $hgCondition $searchHost $searchService $ACLCondition";
        if ($o == "svc_unhandled") {
            $rq .= " AND nss.service_object_id  = s.service_object_id
            	    AND s.host_object_id = hs.host_object_id
            	    AND hs.scheduled_downtime_depth = 0
            	    AND hs.problem_has_been_acknowledged = 0";
        }
	}

	$DBRESULT =& $obj->DBNdo->query($rq);
	$data =& $DBRESULT->fetchRow();
	$numRows =& $data["count(DISTINCT UPPER(CONCAT(no.name1,';', no.name2)))"];
	$DBRESULT->free();
	unset($data);

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
	$obj->XML->writeElement("host_currently_downtime", _("Host is currently on downtime"));
	$obj->XML->writeElement("problem_ack", _("Problem has been acknowledged"));
	$obj->XML->writeElement("host_passive_mode", _("This host is only checked in passive mode"));
	$obj->XML->writeElement("host_never_checked", _("This host is never checked"));
	$obj->XML->writeElement("service_currently_downtime", _("Service is currently on Downtime"));
	$obj->XML->writeElement("service_passive_mode", _("This service is only checked in passive mode"));
	$obj->XML->writeElement("service_not_active_not_passive", _("This service is neither active nor passive"));
	$obj->XML->writeElement("service_flapping", _("This Service is flapping"));
	$obj->XML->writeElement("notif_disabled", _("Notification is disabled"));
	$obj->XML->endElement();

	$host_prev = "";
	$ct = 0;
	$flag = 0;

	$DBRESULT =& $obj->DBNdo->query($finalRequest);
	while ($ndo =& $DBRESULT->fetchRow()) {
		if (isset($host_status[$ndo["host_name"]])) {

			$passive = 0;
			$active = 1;
			$last_check = " ";
			$duration = " ";

			if ($ndo["last_state_change"] > 0 && time() > $ndo["last_state_change"]) {
				$duration = CentreonDuration::toString(time() - $ndo["last_state_change"]);
			} else if ($ndo["last_state_change"] > 0) {
				$duration = " - ";
			}

			$hard_duration = " N/S ";
			if (($ndo["last_hard_state_change"] > 0) && ($ndo["last_hard_state_change"] >= $ndo["last_state_change"])) {
				$hard_duration = CentreonDuration::toString(time() - $ndo["last_hard_state_change"]);
			}

			if ($ndo["scheduled_downtime_depth"] > 0) {
				$class = "line_downtime";
			} else if ($ndo["current_state"] == 2) {
				$ndo["problem_has_been_acknowledged"] == 1 ? $class = "line_ack" : $class = "list_down";
			} else {
				if ($ndo["problem_has_been_acknowledged"] == 1)
					$class = "line_ack";
			}

			$obj->XML->startElement("l");
			$obj->XML->writeAttribute("class", $obj->getNextLineClass());
			$obj->XML->writeElement("o", $ct++);

			if ($host_prev == $ndo["host_name"]) {
				$obj->XML->writeElement("hc", "transparent");
				$obj->XML->startElement("hn");
				$obj->XML->writeAttribute("none", "1");
				$obj->XML->text($ndo["host_name"]);
				$obj->XML->endElement();
				$obj->XML->writeElement("hnl", urlencode($ndo["host_name"]));
				$obj->XML->writeElement("hid", $host_status[$ndo["host_name"]]["object_id"]);
			} else {
				$host_prev = $ndo["host_name"];
				if ($host_status[$ndo["host_name"]]["scheduled_downtime_depth"] == 0) {
					$obj->XML->writeElement("hc", $obj->colorHostInService[$host_status[$ndo["host_name"]]["current_state"]]);
				} else {
					$obj->XML->writeElement("hc", $obj->general_opt['color_downtime']);
				}

				$obj->XML->writeElement("hnl", urlencode($ndo["host_name"]));
				$obj->XML->startElement("hn");
				$obj->XML->writeAttribute("none", "0");
				$obj->XML->text($ndo["host_name"], true, false);
				$obj->XML->endElement();
				$obj->XML->writeElement("hau", $host_status[$ndo["host_name"]]["action_url"]);

				if ($host_status[$ndo["host_name"]]["notes_url"]) {
					$obj->XML->writeElement("hnu", str_replace("\$HOSTNAME\$", $ndo["host_name"], $host_status[$ndo["host_name"]]["notes_url"]));
				} else
					$obj->XML->writeElement("hnu", "none");

				$obj->XML->writeElement("hnn", $host_status[$ndo["host_name"]]["notes"]);
				$obj->XML->writeElement("hico", $host_status[$ndo["host_name"]]["icon_image"]);
				$obj->XML->writeElement("hip", $host_status[$ndo["host_name"]]["address"]);
				$obj->XML->writeElement("hdtm", $host_status[$ndo["host_name"]]["scheduled_downtime_depth"]);
				$obj->XML->writeElement("hid", $host_status[$ndo["host_name"]]["object_id"]);
			}

			$obj->XML->writeElement("ppd", 	$ndo["process_performance_data"]);
			$obj->XML->writeElement("hs", 	$host_status[$ndo["host_name"]]["current_state"]);

			/*
			 * Add possibility to use display name
			 */
			if (isset($ndo["display_name"]) && $ndo["display_name"]) {
				$obj->XML->writeElement("sd", 	$ndo["display_name"], false);
			} else {
				$obj->XML->writeElement("sd", 	$ndo["service_description"], false);
			}
			$obj->XML->writeElement("sico", $ndo["icon_image"]);
			$obj->XML->writeElement("sdl", 	urlencode($ndo["service_description"]));
			$obj->XML->writeElement("svc_id", $ndo["object_id"]);
			$obj->XML->writeElement("sc", 	$obj->colorService[$ndo["current_state"]]);
			$obj->XML->writeElement("cs", 	_($obj->statusService[$ndo["current_state"]]), false);
			$obj->XML->writeElement("po", 	$ndo["plugin_output"], false);
			$obj->XML->writeElement("ca", 	$ndo["current_attempt"]."/".$ndo["max_check_attempts"]." (".$obj->stateType[$ndo["state_type"]].")");
			$obj->XML->writeElement("ne", 	$ndo["notifications_enabled"]);
			$obj->XML->writeElement("pa", 	$ndo["problem_has_been_acknowledged"]);
			$obj->XML->writeElement("pc", 	$ndo["passive_checks_enabled"]);
			$obj->XML->writeElement("ac", 	$ndo["active_checks_enabled"]);
			$obj->XML->writeElement("eh", 	$ndo["event_handler_enabled"]);
			$obj->XML->writeElement("is", 	$ndo["is_flapping"]);
			$obj->XML->writeElement("dtm",	$ndo["scheduled_downtime_depth"]);

			if ($ndo["notes_url"] != "") {
				$ndo["notes_url"] = str_replace("\$SERVICEDESC\$", $ndo["service_description"], $ndo["notes_url"]);
				$ndo["notes_url"] = str_replace("\$HOSTNAME\$", $ndo["host_name"], $ndo["notes_url"]);
				if (isset($host_status[$ndo["host_name"]]['host_alias']) && $host_status[$ndo["host_name"]]['host_alias']) {
				    $ndo["notes_url"] = str_replace("\$HOSTALIAS\$", $host_status[$ndo["host_name"]]['host_alias'], $ndo["notes_url"]);
				}
				if (isset($host_status[$ndo["host_name"]]['address']) && $host_status[$ndo["host_name"]]['address']) {
                    $ndo["notes_url"] = str_replace("\$HOSTADDRESS\$", $host_status[$ndo["host_name"]]['address'], $ndo["notes_url"]);
				}
				$obj->XML->writeElement("snu", $ndo["notes_url"]);
			} else {
				$obj->XML->writeElement("snu", 'none');
			}

			if ($ndo["notes"] != "") {
				$ndo["notes"] = str_replace("\$SERVICEDESC\$", $ndo["service_description"], $ndo["notes"]);
				$ndo["notes"] = str_replace("\$HOSTNAME\$", $ndo["host_name"], $ndo["notes"]);
			    if (isset($host_status[$ndo["host_name"]]['host_alias']) && $host_status[$ndo["host_name"]]['host_alias']) {
				    $ndo["notes"] = str_replace("\$HOSTALIAS\$", $host_status[$ndo["host_name"]]['host_alias'], $ndo["notes"]);
				}
				if (isset($host_status[$ndo["host_name"]]['address']) && $host_status[$ndo["host_name"]]['address']) {
                    $ndo["notes"] = str_replace("\$HOSTADDRESS\$", $host_status[$ndo["host_name"]]['address'], $ndo["notes"]);
				}
				$obj->XML->writeElement("sn", $ndo["notes"]);
			} else {
				$obj->XML->writeElement("sn", 'none');
			}

			$obj->XML->writeElement("fd", $ndo["flap_detection_enabled"]);
			$obj->XML->writeElement("ha", $host_status[$ndo["host_name"]]["problem_has_been_acknowledged"]);
			$obj->XML->writeElement("hae", $host_status[$ndo["host_name"]]["active_checks_enabled"]);
			$obj->XML->writeElement("hpe", $host_status[$ndo["host_name"]]["passive_checks_enabled"]);
			$obj->XML->writeElement("nc", $obj->GMT->getDate($dateFormat, $ndo["next_check"]));
			if ($ndo["last_check"] != 0) {
				$obj->XML->writeElement("lc", $obj->GMT->getDate($dateFormat, $ndo["last_check"]));
			} else {
				$obj->XML->writeElement("lc", "N/A");
			}
			$obj->XML->writeElement("d", $duration);
			$obj->XML->writeElement("last_hard_state_change", $hard_duration);
			$obj->XML->writeElement("svc_index", getMyIndexGraph4Service($ndo["host_name"], $ndo["service_description"], $obj->DBC));
			$obj->XML->endElement();
		}
	}
	$DBRESULT->free();
	unset($ndo);
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