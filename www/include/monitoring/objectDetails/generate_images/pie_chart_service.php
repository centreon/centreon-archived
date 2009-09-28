<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
 */
 
	require_once ("../../../../class/Session.class.php");
	require_once ("../../../../class/centreon.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	// create the graph
	$Graph =& Image_Graph::factory('graph', array(300, 200));
	// add a TrueType font
	$Font =& $Graph->addNew('font', 'Arial');
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
	
	$value =& $_GET["value"];
	$tab2 = array();
	foreach ($value as $key => $v)	
		$tab2[strtolower($key) . " - ". $v] = $v;
	
	$Dataset =& Image_Graph::factory('dataset', array($value));
	
	// create the 1st plot as smoothed area chart using the 1st dataset
	$Plot =& $Plotarea->addNew('Image_Graph_Plot_Pie', $Dataset);
	
	$Plot->Radius = 2;
	    
	// set a line color
	$Plot->setLineColor('gray');
	
	// set a standard fill style
	
	$FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
	$Plot->setFillStyle($FillArray);
	
	foreach ($value as $key => $v)
		$FillArray->addColor($oreon->optGen["color_".strtolower($key)]."@0.2");

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