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
	
	include_once($oreonPath . "www/DBndoConnect.php");

	## calcul stat for resume
	$statistic = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");
	
	/* Get HostNDO status */
	/* Get ServiceNDO status */
	$rq1 = "SELECT count(nss.current_state) as cnt, nss.current_state" .
			" FROM ".$ndo_base_prefix."_servicestatus nss, ".$ndo_base_prefix."_objects no" .
			" WHERE no.object_id = nss.service_object_id".
			" AND no.name1 not like 'OSL_Module'".
			" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$data = array();
	$color = array();
	while($DBRESULT_NDO1->fetchInto($ndo)){
		$data[] = $ndo["cnt"];
		$legend[] = $statistic[$ndo["current_state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic[$ndo["current_state"]])];		
	}
	/*
	 *  create the dataset
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
	
	$g->pie_values($data, $legend);
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
