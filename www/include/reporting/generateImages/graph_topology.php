<?php
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Service Level » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	require_once 'Image/GraphViz.php';
	require_once 'DB.php';
				
	include("../../../oreon.conf.php");
	require_once ("../../../$classdir/Session.class.php");
	require_once ("../../../$classdir/Oreon.class.php");
		
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $conf_oreon['user'],
	    'password' => $conf_oreon['password'],
	    'hostspec' => $conf_oreon['host'],
	    'database' => $conf_oreon['db'],
	);
	
	$options = array(
	    'debug'       => 2,
	    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
	);
	
	$db =& DB::connect($dsn, $options);
	if (PEAR::isError($db)) die($db->getMessage());
	    
	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	
	$session =& $db->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if ($session->numRows()){
		
		Session::start();
		$oreon =& $_SESSION["oreon"];
		
		$str = "SELECT host_id,host_name FROM `host` WHERE host_activate = '1'";	
		$res2 =& $db->query($str);
		$host_data_id = array();
		$host_data_name = array();
		while ($res2->fetchInto($host)){
			$host_data_id[$host["host_name"]] = $host["host_id"];
			$host_data_name[$host["host_id"]] = $host["host_name"];
		}
		
		$graph = new Image_GraphViz(TRUE, array("bgcolor" => "#BBFFBB"));
		
		foreach ($oreon->status_graph_host as $key => $h){
			$color = $oreon->optGen["color_".strtolower($h["status"])];
			$graph->addNode($h["host_name"],array('label' => $h["host_name"], "fillcolor"=>$color, "style"=>"filled", "fontsize"=>"6", "fontname"=>"Verdana")); // "margin"=>"0.04" 
			$res =& $db->query("SELECT * FROM host_hostparent_relation WHERE host_host_id = '".$host_data_id[$h["host_name"]]."'");
			while ($res->fetchInto($host_parents))
				$graph->addEdge(array($host_data_name[$host_parents["host_parent_hp_id"]] => $h["host_name"]), array('color' => '#000000'));
		}
	 	$graph->image("gif");
	}
 ?>
 