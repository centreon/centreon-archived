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
		$pearDBndo	= new CentreonDB("ndo");
		$ndo_base_prefix = getNDOPrefix();
	}

	/**
	 * calcul stat for resume
	 */
	$statistic_host = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE",3 => "PENDING");

	/**
	 * Get DB informations for creating Flash
	 */
	if ($oreon->broker->getBroker() == "broker") {
		$rq1 = 	" SELECT count(DISTINCT name) cnt, state " .
				" FROM `hosts` " .
				" WHERE enabled = 1 " .
		        $oreon->user->access->queryBuilder("AND", "name", $oreon->user->access->getHostsString("NAME", $pearDBO)) .
		        " AND name NOT LIKE '_Module_%' " .
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
	$color = array();
	$legend = array();
	$counter = 0;
	while ($ndo = $DBRESULT->fetchRow()){
		$data[] = $ndo["cnt"];
		$legend[] = $statistic_host[$ndo["state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic_host[$ndo["state"]])];
		$counter += $ndo["cnt"];
	}
	$DBRESULT->free();

	foreach ($data as $key => $value) {
		$value = round($value / $counter * 100, 2);
	  	$value = str_replace(",", ".", $value);
	  	$data[$key] = $value;
	}

	/**
	 * Create Graphs
	 */
	$g = new graph();
	$g->bg_colour = '#FFFFFF';

	// PIE chart, 60% alpha
	$g->pie(60,'#505050','#000000');

	// pass in two arrays, one of data, the other data labels
	$g->pie_values( $data, $legend );

	//
	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	//
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