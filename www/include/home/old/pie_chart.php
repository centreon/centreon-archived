<?php
/**
 * Usage example for Image_Graph.
 * 
 * Main purpose: 
 * Show pie chart
 * 
 * Other: 
 * None specific
 * 
 * $Id: plot_pie_rest.php,v 1.1 2005/10/13 20:18:27 nosey Exp $
 * 
 * @package Image_Graph
 * @author Jesper Veggerby <pear.nosey@veggerby.dk>
 */

	require_once 'Image/Graph.php';
	
	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	// create the graph
	$Graph =& Image_Graph::factory('graph', array(300, 250));
	// add a TrueType font
	$Font =& $Graph->addNew('font', 'Arial');
	// set the font size to 11 pixels
	$Font->setSize(7);
	$Graph->setFont($Font);
	
	// setup the plotarea, legend and their layout
	$Graph->add(
	   Image_Graph::vertical(
	      Image_Graph::factory('title', array('Services', 10)),        
	      Image_Graph::vertical(
	         $Plotarea = Image_Graph::factory('plotarea'),
	         $Legend = Image_Graph::factory('legend'),
	         80
	      ),
	      10
	   )
	);
	$Graph->setBackgroundColor('#fff9eb');
	$Legend->setPlotArea($Plotarea);
	
	$Plotarea->hideAxis();
	$Plotarea->setBackgroundColor('#fff9eb');
	
	// create the dataset
	
	$tab = array();
	foreach ($oreon->status_graph_service as $s){
		if (!isset($tab[strtolower($s["status"])]))
			$tab[strtolower($s["status"])] = 0;
		$tab[strtolower($s["status"])]++;
	}
	$tab2 = array();
	foreach ($tab as $key => $value){
		$tab2[$key . " - ". $value] = $value;
	}

	$Dataset =& Image_Graph::factory('dataset', array($tab2));
	
	// create the 1st plot as smoothed area chart using the 1st dataset
	$Plot =& $Plotarea->addNew('Image_Graph_Plot_Pie', $Dataset);
	
	$Plot->Radius = 2;
	    
	// set a line color
	$Plot->setLineColor('gray');
	
	// set a standard fill style
	
	
	
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$Plot->setFillStyle($FillArray);
	
	foreach ($tab as $key => $value){
		$FillArray->addColor($oreon->optGen["color_".$key]."@0.2");
	}

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