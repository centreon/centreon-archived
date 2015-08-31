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

 	/**
	 * Include configuration files
	 */
 	include_once "@CENTREON_ETC@/centreon.conf.php";

 	/**
 	 * Require Classes
 	 */
	require_once $centreon_path . "www/class/centreonSession.class.php";
	require_once $centreon_path . "www/class/centreon.class.php";
	require_once $centreon_path . "www/class/centreonLang.class.php";
	require_once $centreon_path . "www/class/centreonDB.class.php";
	require_once $centreon_path.'/www/lib/ofc-library/open-flash-chart.php';

	/**
	 * Include functions
	 */
	require_once $centreon_path . "www/include/common/common-Func.php" ;

	/**
	 * Get Session informations
	 */
	CentreonSession::start();
	$oreon = $_SESSION["centreon"];

	/**
	 * Initiate Language class
	 */
	$centreonLang = new CentreonLang($centreon_path, $oreon);
	$centreonLang->bindLang();

	/**
	 * Init DB connexions
	 */
	$pearDB 		= new CentreonDB();
	$pearDBO 		= new CentreonDB("centstorage");
	if ($oreon->broker->getBroker() == "ndo") {
		$pearDBndo 	= new CentreonDB("ndo");
		$ndo_base_prefix = getNDOPrefix();
	}

	/**
	 * calcul stat for resume
	 */
	$statistic_host = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");

    /**
	 * Init LCA
	 */
	$sid = $_GET['sid'];
	$res1 = $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user = $res1->fetchRow();
	$user_id = $user["user_id"];

	global $is_admin;

	$is_admin =  $oreon->user->admin;
	$grouplistStr = $oreon->user->access->getAccessGroupsString();

	/**
	 * Get DB informations for creating Flash
	 */
	if ($oreon->broker->getBroker() == "broker") {
		if (!$is_admin) {
			$rq2 = 	" SELECT count(DISTINCT services.state, services.host_id, services.service_id) count, services.state state" .
					" FROM services, hosts, centreon_acl " .
					" WHERE services.host_id = hosts.host_id ".
					" AND hosts.name NOT LIKE '_Module_%' ".
					" AND services.host_id = centreon_acl.host_id ".
					" AND services.service_id = centreon_acl.service_id " .
			        " AND hosts.enabled = 1 " .
					" AND services.enabled = 1 " .
					" AND centreon_acl.group_id IN (".$grouplistStr.") ".
					" GROUP BY services.state ORDER by services.state";
		} else {
			$rq2 = 	" SELECT count(DISTINCT services.state, services.host_id, services.service_id) count, services.state state" .
					" FROM services, hosts " .
					" WHERE services.host_id = hosts.host_id ".
					" AND hosts.name NOT LIKE '_Module_%' ".
			        " AND hosts.enabled = 1 ".
					" AND services.enabled = 1 ".
					" GROUP BY services.state ORDER by services.state";
		}
		$DBRESULT = $pearDBO->query($rq2);
	} else {
		if (!$is_admin) {
			$rq2 = 	" SELECT count(DISTINCT nss.current_state, no.name1, no.name2) count, nss.current_state state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".
					" AND no.name1 = centreon_acl.host_name ".
					" AND no.name2 = centreon_acl.service_description " .
					" AND centreon_acl.group_id IN (".$grouplistStr.") ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER BY nss.current_state";
		} else {
			$rq2 = 	" SELECT count(DISTINCT nss.current_state, no.name1, no.name2) count, nss.current_state state" .
					" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
					" WHERE no.object_id = nss.service_object_id".
					" AND no.name1 NOT LIKE '_Module_%' ".
					" AND no.is_active = 1 GROUP BY nss.current_state ORDER BY nss.current_state";
		}
		$DBRESULT = $pearDBndo->query($rq2);
	}

	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	$info = array();
	$color = array();
	$legend = array();
	$counter = 0;
	while ($data = $DBRESULT->fetchRow()) {
		if ($oreon->broker->getBroker() == "broker") {
			$info[] = $data["count"];
			$counter += $data["count"];
		} else {
			$info[] = $data["count"];
			$counter += $data["count"];
		}
		$legend[] = $statistic_host[$data["state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic_host[$data["state"]])];
	}
	$DBRESULT->free();

	/**
	 *  create the dataset
	 */
	foreach ($info as $key => $value) {
		$value = round($value / $counter * 100, 2);
	  	$value = str_replace(",", ".", $value);
	  	$data[$key] = $value;
	}

	/**
	 * Create Graphs
	 */
	$g = new graph();
	$g->bg_colour = '#FFFFFF';
	//
	// PIE chart, 60% alpha
	//
	$g->pie(60,'#505050','#000000');
	//
	// pass in two arrays, one of data, the other data labels
	//

	$g->pie_values($data, $legend);
	//
	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	//
	$g->set_tool_tip( '#key# : #val# %' );
	$g->pie_slice_colours($color);
	$g->set_tool_tip( '#val#%' );
	$g->title( sprintf(_('Availability: %s%%')."\n\n", $data[0]), '{font-size: 16px;}' );

	/**
	 * Send HTTP Headers
	 */
	header("Cache-Control: cache, must-revalidate");
    header("Pragma: public");

	echo $g->render();
?>