<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	include_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."www/class/Session.class.php";
	require_once $centreon_path."www/class/Oreon.class.php";

	Session::start();
	$oreon =& $_SESSION["oreon"];
	

	// -----------------------------------------------------
	$value =& $_GET["value"];
	foreach ($value as $key => $val)	{
		if ($val)
			if (!isset($oreon->optGen["color_".strtolower($key)])) {
				$color[] = $oreon->optGen["color_undetermined"];
				$data[] = $val;
				$legend[] = "";
			} else {
				$color[] = $oreon->optGen["color_".strtolower($key)];		
				$data[] = $val;
				$legend[] = "";
			}
	}
	include_once($centreon_path . '/www/lib/ofc-library/open-flash-chart.php' );

	$g = new graph();
	$g->bg_colour = '#F3F6F6';
	//
	// PIE chart, 60% alpha
	//
	$g->pie(60,'#505050','#000000');
	//
	// pass in two arrays, one of data, the other data labels
	//
	
	$g->pie_values( $data, $legend );
	//
	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	//
	
	$g->pie_slice_colours($color);

	$g->set_tool_tip( '#val#%' );
	if (isset($_GET["service_name"]) && isset($_GET["host_name"]))
		$g->title( $_GET["service_name"] . " on " . $_GET["host_name"], '{font-size:15px; color: #424242}' );
	else if (isset($_GET["host_name"]))
		$g->title( $_GET["host_name"], '{font-size:18px; color: #424242}' );
	echo $g->render();

?>