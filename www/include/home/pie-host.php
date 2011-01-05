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
	$oreon =& $_SESSION["centreon"];

	/**
	 * Initiate Language class
	 */
	$centreonLang = new CentreonLang($centreon_path, $oreon);
	$centreonLang->bindLang();

	/**
	 * Broker type
	 */
	$broker = "broker";

	/**
	 * Init DB connexions
	 */
	$pearDB 		= new CentreonDB();
	$pearDBO 		= new CentreonDB("centstorage");
	$pearDBndo 		= new CentreonDB("ndo");

	if ($broker == "broker") {
		$ndo_base_prefix = getNDOPrefix();
	}

	/**
	 * calcul stat for resume
	 */
	$statistic_host = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE",3 => "PENDING");

	/**
	 * Get DB informations for creating Flash
	 */
	if ($broker = "broker") {
		$rq1 = 	" SELECT count(DISTINCT name) cnt, state " .
				" FROM `hosts` " .
				$oreon->user->access->queryBuilder("WHERE", "name", $oreon->user->access->getHostsString("NAME", $pearDBndo)) .
				" GROUP BY state " .
				" ORDER by state";
		$DBRESULT =& $pearDBO->query($rq1);
	} else {
		$rq1 = 	" SELECT count(DISTINCT ".$ndo_base_prefix."objects.name1) cnt, ".$ndo_base_prefix."hoststatus.current_state state" .
				" FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."objects " .
				" WHERE ".$ndo_base_prefix."objects.object_id = ".$ndo_base_prefix."hoststatus.host_object_id " .
				" AND ".$ndo_base_prefix."objects.is_active = 1 " .
				$oreon->user->access->queryBuilder("AND", $ndo_base_prefix."objects.name1", $oreon->user->access->getHostsString("NAME", $pearDBndo)) .
				" GROUP BY ".$ndo_base_prefix."hoststatus.current_state " .
				" ORDER by ".$ndo_base_prefix."hoststatus.current_state";
		$DBRESULT =& $pearDBndo->query($rq1);
	}
	$data = array();
	$color = array();
	$legend = array();
	$counter = 0;
	while ($ndo =& $DBRESULT->fetchRow()){
		$data[] = $ndo["cnt"];
		$legend[] = $statistic_host[$ndo["current_state"]];
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