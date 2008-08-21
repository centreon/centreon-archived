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

/**
 * This script drawing pie chart of host or service on the reporting interface.
 *
 * PHP version 5
 *
 * @package pie-charts.php
 * @author Damien Duponchelle dduponchelle@merethis.com
 * @version $Id: $
 * @copyright (c) 2007-2008 Centreon
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

	// Including files and dependences
	include_once '@CENTREON_ETC@/centreon.conf.php';
	require_once $centreon_path.'www/class/Session.class.php';
	require_once $centreon_path.'www/class/Oreon.class.php';

	// Session Variables
	Session::start();
	$oreon =& $_SESSION["oreon"];
	$value =& $_GET["value"];

	if(isset($value) == true) {
		foreach ($value as $key => $val) {
			if (isset($val) == true) {
				if (isset($oreon->optGen["color_".strtolower($key)]) == false) {
						$color[] = $oreon->optGen["color_unknown"];
						$data[] = $val;
						$legend[] = "Undetermined";
					} else {
						$color[] = $oreon->optGen["color_".strtolower($key)];		
						$data[] = $val;
						$legend[] = $key;
				}
			}
		}
	}
	
	include_once($centreon_path . '/www/lib/ofc-library/open-flash-chart.php' );

	// Declaring a new pie chart with a white background
	$g = new graph();
	$g->bg_colour = '#F3F6F6'; // Declaring white background for the pie chart.
	$g->pie(60,'#505050','#000000'); // Initializing properties with a grey color and 60% (alpha)
   
    // Insert datas on the pie chart
    if(isset($data) == true && isset($legend) == true) {	
	$g->pie_values($data,$legend);
    }

	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	$g->pie_slice_colours($color);
	$g->set_tool_tip( '#val#%' );
	
	// Initializing title of pie chart.
	if (isset($_GET["service_name"]) == true && isset($_GET["host_name"]) == true) {
		// Title for a service displayed like "Service on Host".
		$g->title( $_GET["service_name"] . " on " . $_GET["host_name"], '{font-size:15px; color: #424242}' );
	} else if (isset($_GET["host_name"]) == true) {
		// Title for a host.		
		$g->title( $_GET["host_name"], '{font-size:18px; color: #424242}' );
	}
	
	// Apply the current pie chart
	echo $g->render();
?>
