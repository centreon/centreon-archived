<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	require_once ("../../../../class/centreonSession.class.php");
	require_once ("../../../../class/centreon.class.php");

	CentreonSession::start();
	$oreon = $_SESSION["centreon"];

	// create the graph
	$Graph = Image_Graph::factory('graph', array(300, 200));
	// add a TrueType font
	$Font = $Graph->addNew('font', 'Arial');
	// set the font size to 11 pixels
	$Font->setSize(7);
	$Graph->setFont($Font);

	// setup the plotarea, legend and their layout
	$Graph->add(
	   Image_Graph::vertical(
	      Image_Graph::factory('title', array('Host '.$_GET["host_name"].' Services ', 10)),
	      Image_Graph::vertical(
	         $Plotarea = Image_Graph::factory('plotarea'),
	         $Legend = Image_Graph::factory('legend'),
	         80
	      ),10));

	$Graph->setBackgroundColor('#F2F2F2');
	$Legend->setPlotArea($Plotarea);

	$Plotarea->hideAxis();
	$Plotarea->setBackgroundColor('#F2F2F2');

	$value = $_GET["value"];
	$tab2 = array();
	foreach ($value as $key => $v)
		$tab2[strtolower($key) . " - ". $v] = $v;

	$Dataset = Image_Graph::factory('dataset', array($value));

	// create the 1st plot as smoothed area chart using the 1st dataset
	$Plot = $Plotarea->addNew('Image_Graph_Plot_Pie', $Dataset);

	$Plot->Radius = 2;

	// set a line color
	$Plot->setLineColor('gray');

	// set a standard fill style

	$FillArray = Image_Graph::factory('Image_Graph_Fill_Array');
	$Plot->setFillStyle($FillArray);

	foreach ($value as $key => $v)
		$FillArray->addColor($oreon->optGen["color_".strtolower($key)]."@0.2");

	$Plot->explode(4);


	// create a Y data value marker
	$Marker = $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_PCT_Y_TOTAL);
	// fill it with white
	$Marker->setFillColor('white');
	// and use black border
	$Marker->setBorderColor('black');
	// and format it using a data preprocessor
	$Marker->setDataPreprocessor(Image_Graph::factory('Image_Graph_DataPreprocessor_Formatted', '%0.1f%%'));
	$Marker->setFontSize(7);

	// create a pin-point marker type
	$PointingMarker = $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
	// and use the marker on the plot
	$Plot->setMarker($PointingMarker);

	// output the Graph
	$Graph->done();
?>