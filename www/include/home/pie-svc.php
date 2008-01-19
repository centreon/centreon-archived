<?php

	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];


	// create the dataset
	$data = array();
	$color = array();
	
	foreach ($oreon->status_graph_service as $key => $value){
		if ($value != 0){
			$data[] = $value;
			$legend[] = $key;
			$color[] = $oreon->optGen["color_".strtolower($key)];		
		}			
	}

	include_once( '/usr/local/centreon/www/lib/ofc-library/open-flash-chart.php' );
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
	$g->title( 'Services', '{font-size:18px; color: #d01f3c}' );
	echo $g->render();

?>
