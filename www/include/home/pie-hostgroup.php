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
 * For information : contact@oreon-project.org
 */
 
	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	$oreonPath = '/srv/oreon/';

	require_once("DB.php");
	include_once($oreonPath . "etc/centreon.conf.php");
		
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

	include_once('/usr/local/centreon/www/lib/ofc-library/open-flash-chart.php' );
	include_once($oreonPath . "www/include/common/common-Func-ACL.php");
	include_once($oreonPath . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();	
	
	include_once($oreonPath . "www/DBNDOConnect.php");

	## calcul stat for resume
	$statistic = array(0 => "UP", 1 => "", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");
	
	$hg = array();
	$strnameY = "";
	$percentmax = 0;
	$bar_blue = new bar_3d( 75, '#125CEC' );
	$bar_blue->key( 'host UP (%)', 10 );
	
	$bar_red = new bar_3d( 75, '#EC5C12' );
	$bar_red->key( 'host Down (%)', 10 );
	
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	
	while($hg = $DBRESULT->fetchRow()){
		$counterTotal = 0;
		$counterUP = 0;
		$counterDown = 0;
		$DBRESULT2 =& $pearDB->query("SELECT host_name FROM host, hostgroup_relation WHERE  hostgroup_relation.hostgroup_hg_id = '".$hg["hg_id"]."' AND hostgroup_relation.host_host_id = host.host_id");
		while($h = $DBRESULT2->fetchRow()){
			$DBRESULT3 =& $pearDBndo->query("SELECT current_state FROM nagios_hoststatus, nagios_hosts WHERE nagios_hoststatus.host_object_id = nagios_hosts.host_object_id AND nagios_hosts.alias = '".$h["host_name"]."'");
			if (PEAR::isError($DBRESULT3))
				print "DB Error : ".$DBRESULT3->getDebugInfo()."<br />";
			while($stt = $DBRESULT3->fetchRow()){
				if ($stt["current_state"] == 1)
					$counterDown++;
				if ($stt["current_state"] == 0)
					$counterUP++;
				$counterTotal++;
			}
		}
		if ($counterTotal){
			$percentU = $counterUP / $counterTotal * 100;
			$percentD = $counterDown / $counterTotal * 100;
			$hostgroupU[$hg["hg_name"]] = $percentU;
			$bar_blue->data[] = $percentU;
			$bar_red->data[] = $percentD;
			if ($percentU > $percentmax)
				$percentmax = $percentU;
			if ($strnameY)
				$strnameY .= ", ";
			$strnameY .= $hg["hg_name"];
		}
	}

	/*
	 *  create the dataset
	 */
	
	//
	// create a 2nd set of bars:
	//
	
	
	
	// create the graph object:
	$g = new graph();
	$g->bg_colour = '#F3F6F6';
	$g->title( _('Status of Host Groups'), '{font-size:18px; color: #424242; margin: 5px; background-color: #F3F6F6; padding:5px; padding-left: 20px; padding-right: 20px;}' );
	
	//$g->set_data( $data_1 );
	//$g->bar_3D( 75, '#D54C78', '2006', 10 );
	
	//$g->set_data( $data_2 );
	//$g->bar_3D( 75, '#3334AD', '2007', 10 );
	
	$g->data_sets[] = $bar_blue;
	$g->data_sets[] = $bar_red;
	
	$g->set_x_axis_3d( 12 );
	$g->x_axis_colour( '#909090', '#ADB5C7' );
	$g->y_axis_colour( '#909090', '#ADB5C7' );
	
	$g->set_tool_tip( _(' Availability of hosts from the group ') . '#x_label# : #val# %' );
	
	$g->set_x_labels(array($strnameY));
	$g->set_y_max( $percentmax );
	$g->y_label_steps( 5 );
	$g->set_y_legend( _('Availability'), 12, '#424242' );
	echo $g->render();



?>
