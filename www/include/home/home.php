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

	if (!isset($centreon)) {
		exit();
	}

	/**
	 * Including files and dependences
	 */
	require_once "./include/monitoring/common-Func.php";
	require_once "./class/centreonDB.class.php";

	/**
	 * Include General Functions
	 */
	include_once $centreon_path . "www/include/common/common-Func.php";

	$broker = "broker";

	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo = new CentreonDb("ndo");
		$ndo_base_prefix = getNDOPrefix();
		if ($err_msg = table_not_exists("centreon_acl")) {
			print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
		}
	}

	/**
	 * Tab status
	 */
	$tabSatusHost 		= array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE", 4 => "PENDING");
	$tabSatusService 	= array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");

	/**
	 * Directory of Home pages
	 */
	$path = "./include/home/";

	/**
	 * Displaying a Smarty Template
	 */
	$template = new Smarty();
	$template = initSmartyTpl($path, $template, "./");
	$template->assign("session", session_id());
	$template->assign("host_label", _("Hosts"));
	$template->assign("svc_label", _("Services"));

	/**
	 * Status informations
	 */
	// HOSTS
	if ($oreon->broker->getBroker() == "broker") {
		$rq1 = 	" SELECT count(DISTINCT name) cnt, state " .
			" FROM `hosts` " .
		    " WHERE hosts.enabled = 1 " .
			$oreon->user->access->queryBuilder("AND", "name", $oreon->user->access->getHostsString("NAME", $pearDBO)) .
			" AND hosts.name NOT LIKE '_Module_%' ".
			" GROUP BY state " .
			" ORDER BY state";
		$DBRESULT = $pearDBO->query($rq1);
	} else {
		$rq1 = 	" SELECT count(DISTINCT o.name1) cnt, hs.current_state state" .
				" FROM ".$ndo_base_prefix."hoststatus hs, ".$ndo_base_prefix."objects o " .
				" WHERE o.object_id = hs.host_object_id " .
				" AND o.is_active = 1 " .
				" AND o.name1 NOT LIKE '_Module_%' " .
				$oreon->user->access->queryBuilder("AND", "o.name1", $oreon->user->access->getHostsString("NAME", $pearDBndo)) .
				" GROUP BY hs.current_state " .
				" ORDER BY hs.current_state";
		$DBRESULT = $pearDBndo->query($rq1);
	}

	$data = array();
	$statHosts = _("Hosts");
	while ($ndo = $DBRESULT->fetchRow()){
		$data[] = $ndo["cnt"];
		if ($statHosts !=  _("Hosts")) {
			$statHosts .= " - ";
		}
        
        if (isset($tabSatusHost[$ndo["state"]])) {
            $statHosts .=  " " . _($tabSatusHost[$ndo["state"]]);
            if (isset($ndo["cnt"])) {
                $statHosts .= ": ".$ndo["cnt"];
            }
        }
	}
	$DBRESULT->free();

	// SERVICES
	if ($oreon->broker->getBroker() == "broker") {
		if (!$is_admin) {
			$rq2 = 	" SELECT count(DISTINCT services.state, services.host_id, services.service_id) count, services.state state" .
					" FROM services, hosts, centreon_acl " .
					" WHERE services.host_id = hosts.host_id ".
					" AND hosts.name NOT LIKE '_Module_%' ".
					" AND services.host_id = centreon_acl.host_id ".
					" AND services.service_id = centreon_acl.service_id " .
					" AND hosts.enabled = 1 " .
			        " AND services.enabled = 1 ".
					" AND centreon_acl.group_id IN (".$oreon->user->access->getResourceGroupsString().") ".
					" GROUP BY services.state ORDER by services.state";
		} else {
			$rq2 = 	" SELECT count(DISTINCT services.state, services.host_id, services.service_id) count, services.state state" .
					" FROM services, hosts " .
					" WHERE services.host_id = hosts.host_id ".
					" AND hosts.name NOT LIKE '_Module_%' ".
			        " AND hosts.enabled = 1 " .
			        " AND services.enabled = 1 " .
					" GROUP BY services.state ORDER by services.state";
		}
		$DBRESULT = $pearDBO->query($rq2);
	} else {
		if (!$centreon->user->admin) {
			$rq2 = 	" SELECT count(DISTINCT nss.current_state, no.name1, no.name2) count, nss.current_state state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".
					" AND no.name1 = centreon_acl.host_name ".
					" AND no.name2 = centreon_acl.service_description " .
					" AND centreon_acl.group_id IN (".$centreon->user->access->getAccessGroupsString().") ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
		} else {
			$rq2 = 	" SELECT count(DISTINCT nss.current_state, no.name1, no.name2) count, nss.current_state state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
		}
		$DBRESULT = $pearDBndo->query($rq2);
	}
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	$data = array();
	$statServices = _("Services");
	while ($ndo = $DBRESULT->fetchRow()){
		$data[] = $ndo["count"];
		if ($statServices !=  _("Services")) {
			$statServices .= " - ";
		}
        
        if (isset($tabSatusService[$ndo["state"]])) {
            $statServices .= " " . _($tabSatusService[$ndo["state"]]);
            if (isset($ndo["count"])) {
                $statServices .= ": ".$ndo["count"];
            }
        }
        
        
	}
	$DBRESULT->free();

	/**
	 * Send data to Templates
	 */
	$template->assign("statServices", $statServices);
	$template->assign("statHosts", $statHosts);

	/**
	 * Display Templates
	 */
	$template->display("home.ihtml");
?>