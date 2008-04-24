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
 
		
	require_once 'DB.php';

	require_once 'Image/Graph.php';
	require_once 'Image/Canvas.php';
	
	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");
	
	Session::start();
	$oreon =& $_SESSION["oreon"];
	
	/* Connect to perfparse DB */
	
	include("../../oreon.conf.php");
	
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
	if (PEAR::isError($db))
	    die($db->getMessage());
	
	$db->setFetchMode(DB_FETCHMODE_ASSOC);
	
	$DBRESULT =& $db->query("SELECT * FROM `session` WHERE session_id = '".$_GET["session_id"]."'");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	if (!$session->numRows())
		exit;
	else {	
		$Canvas =& Image_Canvas::factory('png',
		    array(
		        'width' => 300,
		        'height' => 300,
		        'antialias' => 'native'
		    )
		);
		
		// create the graph
		$Canvas=& Image_Canvas::factory('png',array('width'=>298,'height'=>250,'antialias' => 'native'));
		$Graph =& Image_Graph::factory('graph', $Canvas);
		// add a TrueType font
		$Font =& $Graph->addNew('font', 'Arial');
		// set the font size to 11 pixels
		$Font->setSize(7);
		
		$Graph->setFont($Font);
		$Graph->setBackgroundColor('#fff9eb');
		
		$Graph->add(
		    Image_Graph::vertical(
		        Image_Graph::factory('title', array('HostGroup Status', 10)),        
		        Image_Graph::vertical(
		            $Plotarea = Image_Graph::factory('Image_Graph_Plotarea_Radar'),
		            $Legend = Image_Graph::factory('legend', array('test', 9)),
		            90
		        ),
		        5
		    )
		);   
		 
		$Legend->setPlotarea($Plotarea);                
		    
		$Plotarea->addNew('Image_Graph_Grid_Polar', IMAGE_GRAPH_AXIS_Y);
		$Plotarea->setBackgroundColor('#fff9eb');

		// create the dataset		
		$DS1 =& Image_Graph::factory('dataset');

		$DBRESULT =& $db->query("SELECT * FROM `hostgroup` WHERE hg_activate = '1'");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		else { 
			$tab_hg = array();
			while ($hg =& $DBRESULT->fetchRow()){
				$tab_hg[$hg["hg_name"]] = array();
				$DBRESULT2 =& $db->query("SELECT host_host_id, host_name FROM hostgroup_relation, host WHERE hostgroup_relation.hostgroup_hg_id = '".$hg["hg_id"]."' AND host.host_id = hostgroup_relation.host_host_id");
				if (PEAR::isError($DBRESULT2))
					print "DB Error : ".$DBRESULT2->getDebugInfo()."<br />";
				for ($total = 0, $up = 0; $rH =& $DBRESULT2->fetchRow(); $total++)
					if (!strcmp($oreon->status_graph_host[$rH["host_name"]]["status"], "UP"))
						$up++;
				if ($total)
					$tab_hg[$hg["hg_name"]] = $up / $total * 100;
			}
		}		
		foreach ($tab_hg as $key => $hg)
			$DS1->addPoint($key, $hg);
		
		$Plot1 =& $Plotarea->addNew('Image_Graph_Plot_Radar', $DS1);

		$Plot1->setTitle('HostGroup Status (%)');	
		$Plot1->setLineColor('blue@0.4');    
		$Plot1->setFillColor('blue@0.2');
		
		// output the Graph
		$Graph->done();
	}
?> 		
		