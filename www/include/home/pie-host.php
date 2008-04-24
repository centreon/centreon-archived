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
 
	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];
	
	require_once("DB.php");
	include_once("/etc/centreon/centreon.conf.php");
		
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

	include_once($centreon_path . "www/include/common/common-Func-ACL.php");
	include_once($centreon_path . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();
	
	## calcul stat for resume
	$statistic_host = array("UP" => 0, "DOWN" => 0, "UNREACHABLE" => 0, "PENDING" => 0);

	include_once($centreon_path . "www/DBNDOConnect.php");

	## calcul stat for resume
	$statistic_host = array(0 => "UP", 1 => "DOWN", 2 => "UNREACHABLE",3 => "PENDING");
	
	/*
	 * LCA
	 */
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$_GET["sid"]."'");
	$user = $res1->fetchRow();
	$user_id = $user["user_id"];

	$res2 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user_id."'");
	$admin = $res2->fetchrow();
	
	global $is_admin;
	
	$is_admin = 0;
	$is_admin = $admin["contact_admin"];
	
	if (!$is_admin){
		$_POST["sid"] = $_GET["sid"];
		$lca =  getLCAHostByName($pearDB);
		$lcaSTR = getLCAHostStr($lca["LcaHost"]);
	}
	
	/* Get HostNDO status */
	if ($is_admin)
		$rq1 = 	" SELECT count(nhs.current_state) as cnt, nhs.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nhs.host_object_id AND no.is_active = 1 " .
				" GROUP BY nhs.current_state " .
				" ORDER by nhs.current_state";
	else
		$rq1 = 	" SELECT count(nhs.current_state) as cnt, nhs.current_state" .
				" FROM ".$ndo_base_prefix."hoststatus nhs, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nhs.host_object_id AND no.is_active = 1 " .
				" AND no.name1 IN ($lcaSTR)" .
				" GROUP BY nhs.current_state " .
				" ORDER by nhs.current_state";
	
	$DBRESULT_NDO1 =& $pearDBndo->query($rq1);
	if (PEAR::isError($DBRESULT_NDO1))
		print "DB Error : ".$DBRESULT_NDO1->getDebugInfo()."<br />";
	$data = array();
	$color = array();
	$counter = 0;
	while($DBRESULT_NDO1->fetchInto($ndo)){
		$data[] = $ndo["cnt"];
		$legend[] = $statistic_host[$ndo["current_state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic_host[$ndo["current_state"]])];	
		$counter += $ndo["cnt"];	
	}
	
	foreach ($data as $key => $value)
		$data[$key] = round($value / $counter * 100, 2);

	include_once($centreon_path.'/www/lib/ofc-library/open-flash-chart.php');
	$g = new graph();
	$g->bg_colour = '#F3F6F6';

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
	$g->title( _('Hosts'), '{font-size:18px; color: #424242}' );
	echo $g->render();
?>
