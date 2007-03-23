<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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

	require_once 'Image/Graph.php';
	require_once ("../../../class/Session.class.php");
	require_once ("../../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	// create the graph
	$Graph =& Image_Graph::factory('graph', array(400, 300)); 
	// add a TrueType font
	$Font =& $Graph->addNew('font', 'Arial');
	// set the font size to 11 pixels
	$Font->setSize(7);
	$Graph->setFont($Font);
	$Graph->setBackgroundColor('#F2F2F2');

	$Graph->add(
	    Image_Graph::vertical(
	        Image_Graph::factory('title', array($_GET["host_name"], 12)),
	        Image_Graph::horizontal(               
	            $Plotarea = Image_Graph::factory('plotarea'),           
	   	         $Legend = Image_Graph::factory('legend'),
	         90
	        ),           
	        5            
	    )
	); 

	$value = NULL;
	$value =& $_GET["value"];

	$i=0;
	foreach($value as $key => $val)	{
		$Dataset[$i] =& Image_Graph::factory('dataset');
		$Dataset[$i++]->addPoint($key, $val);
	}
	$Dataset[$i] =& Image_Graph::factory('dataset');
	$Dataset[$i]->addPoint('', 0);

	$Plot =& $Plotarea->addNew('bar', array(&$Dataset, 'stacked'));

	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$FillArray->addColor($oreon->optGen["color_up"] . ' @0.2', 'Up');
	$FillArray->addColor($oreon->optGen["color_down"] . '@0.2', 'Down');
	$FillArray->addColor($oreon->optGen["color_unreachable"] . '@0.2', 'Unreachable');
	$FillArray->addColor($oreon->optGen["color_pending"] . '@0.2', 'Pending');
	$Plot->setFillStyle($FillArray);
	$Plot->setBackgroundColor('#F2F2F2');
	
	$Graph->done(); 

/*
	$Plot->setFillStyle($FillArray); 
	// set a standard fill style
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$Plot->setFillStyle($FillArray);

	$FillArray->addColor($oreon->optGen["color_up"] . ' @0.2', 'Up');
	$FillArray->addColor($oreon->optGen["color_down"] . '@0.2', 'Down');
	$FillArray->addColor($oreon->optGen["color_unreachable"] . '@0.2', 'Unreachable');
	$FillArray->addColor($oreon->optGen["color_pending"] . '@0.2', 'Pending');
	
	$FillArray->addColor('black@0.2', 'rest'); 	
	// set a standard fill style
	
	$FillArray->addColor("40@0.2");
	$FillArray->addColor("60@0.2");

	// output the Graph
	$Graph->done();
*/
?>