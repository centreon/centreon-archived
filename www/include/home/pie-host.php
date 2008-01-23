<?php

	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	$ndo_base_prefix = "nagios";
	$oreonPath = '/srv/oreon/';

	## pearDB init
	require_once 'DB.php';	

	include_once($oreonPath . "etc/centreon.conf.php");
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");

	/* Connect to oreon DB */
	
	$dsn = array('phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['host'],
			     'database' => $conf_oreon['db'],);	
	$options = array('debug'=> 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);	

	$pearDB =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDB)) die("Connecting problems with oreon database : " . $pearDB->getMessage());
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);
	
	## calcul stat for resume
	$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);

	include_once($oreonPath . "www/DBndoConnect.php");

	## calcul stat for resume
	$statistic_host = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE",3 => "PENDING");
	
	/* Get HostNDO status */
	$rq1 = "SELECT count(nhs.current_state) as cnt, nhs.current_state" .
			" FROM ".$ndo_base_prefix."_hoststatus nhs, ".$ndo_base_prefix."_objects no" .
			" WHERE no.object_id = nhs.host_object_id AND no.is_active = 1 GROUP BY nhs.current_state ORDER by nhs.current_state";

	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br>";
	$data = array();
	$color = array();
	while($DBRESULT_NDO1->fetchInto($ndo)){
		$data[] = $ndo["cnt"];
		$legend[] = $statistic_host[$ndo["current_state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic_host[$ndo["current_state"]])];		
	}

/*
	// create the dataset
	$data = array();
	$color = array();
	
	foreach ($oreon->status_graph_host as $key => $value){
		if ($value != 0){
			$data[] = $value;
			$legend[] = $key;
			$color[] = $oreon->optGen["color_".strtolower($key)];		
		}			
	}
*/
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
	$g->title( 'Hosts', '{font-size:18px; color: #d01f3c}' );
	echo $g->render();

?>
