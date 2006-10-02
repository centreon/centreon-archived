<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf - Cedrick Facon

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
	$Graph =& Image_Graph::factory('graph', array(300, 300));
	// add a TrueType font
	$Font =& $Graph->addNew('font', 'Arial');
	// set the font size to 11 pixels
	$Font->setSize(7);
	$Graph->setFont($Font);
	
	// setup the plotarea, legend and their layout
	$Graph->add(
	   Image_Graph::vertical(
	      Image_Graph::factory('title', array('Service '.$_GET["service_name"] . " on Host " .$_GET["host_name"], 10)),        
       
	      Image_Graph::vertical(
	         $Plotarea = Image_Graph::factory('plotarea'),
	         $Legend = Image_Graph::factory('legend'),
	         80
	      ),10));
	      
	$Graph->setBackgroundColor('#FFFFFF');
	$Legend->setPlotArea($Plotarea);
	
	$Plotarea->hideAxis();
	$Plotarea->setBackgroundColor('#FFFFFF');
	
	$value = NULL;
	$value =& $_GET["value"];
	
	$Dataset =& Image_Graph::factory('dataset', array($value));

	// create the 1st plot as smoothed area chart using the 1st dataset
	$Plot =& $Plotarea->addNew('Image_Graph_Plot_Pie', $Dataset);
	
	$Plot->Radius = 2;
	    

// set a line color
$Plot->setLineColor('gray');

// set a standard fill style
$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
$Plot->setFillStyle($FillArray);


$FillArray->addColor($oreon->optGen["color_ok"] . ' @0.2', 'Ok');
$FillArray->addColor($oreon->optGen["color_warning"] . '@0.2', 'Warning');
$FillArray->addColor($oreon->optGen["color_critical"] . '@0.2', 'Critical');
$FillArray->addColor($oreon->optGen["color_unknown"] . '@0.2', 'Unknow');
$FillArray->addColor($oreon->optGen["color_pending"] . '@0.2', 'Pending');



$FillArray->addColor('black@0.2', 'rest'); 	


	// set a standard fill style
	

/*	
	foreach ($value as $key => $v)
		$FillArray->addColor($oreon->optGen["color_".strtolower($key)]."@0.2");
*/
		$FillArray->addColor("40@0.2");
		$FillArray->addColor("60@0.2");

	$Plot->explode(4);
	
	
	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
	// fill it with white
	$Marker->setFillColor('white');
	// and use black border
	$Marker->setBorderColor('black');
	// and format it using a data preprocessor
	$Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));
	$Marker->setFontSize(7);
	
	// create a pin-point marker type
	$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
	// and use the marker on the plot
	$Plot->setMarker($PointingMarker);
	
	// output the Graph
	$Graph->done();
?>