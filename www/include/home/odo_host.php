<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick
Facon, Romain Le Merlus, Julien Mathis

The Software is provided to you AS IS and WITH ALL FAULTS. OREON makes no representation
and gives no warranty whatsoever, whether express or implied, and without limitation, 
with regard to the quality, safety, contents, performance, merchantability, non-infringement
or suitability for any particular or intended purpose of the Software found on the OREON web
site. In no event will OREON be liable for any direct, indirect, punitive, special, incidental
or consequential damages however they may arise and even if OREON has been previously advised 
of the possibility of such damages.

For information : contact@oreon-project.org
*/

	require_once 'Image/Graph.php';
	require_once 'Image/Canvas.php';
	

	// create the graph
	$driver=& Image_Canvas::factory('png',array('width'=>300,'height'=>250,'antialias' => 'native'));
	$Graph = & Image_Graph::factory('graph', $driver);
	// add a TrueType font
	$Font =& $Graph->addNew('font', 'Arial');
	// set the font size to 11 pixels
	$Font->setSize(8);
	
	$Graph->setFont($Font);
	
	// create the plotarea
	$Graph->add(
	    Image_Graph::vertical(
	        Image_Graph::factory('title', array('Host Status Level', 10)),
	        Image_Graph::vertical(
	            $Plotarea = Image_Graph::factory('plotarea'),
	            $Legend = Image_Graph::factory('legend'),
	            80
	        ),
	        10
	    )
	);
	$Graph->setBackgroundColor('#fff9eb');
	$Legend->setPlotarea($Plotarea);
	$Legend->setAlignment(IMAGE_GRAPH_ALIGN_HORIZONTAL);
	
	/***************************Arrows************************/
	$Arrows = & Image_Graph::factory('dataset');
	
	$total = $_GET["u"] + $_GET["d"] + $_GET["un"] + $_GET["p"]; 
	$current_level = $_GET["u"] / $total * 100;
	$Arrows->addPoint('Hosts Status Level', round($current_level), 'OK');
	//$Arrows->setFontSize(10);
	
	/**************************PARAMATERS for PLOT*******************/
	// create the plot as odo chart using the dataset
	$Plot =& $Plotarea->addNew('Image_Graph_Plot_Odo',$Arrows);
	$Plot->setRange(0,100);
	$Plot->setAngles(135, 270);
	$Plot->setRadiusWidth(70);
	$Plot->setLineColor('gray');
	
	//for range and outline
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	$Plot->setArrowMarker($Marker);
	$Plotarea->hideAxis();
	
	/***************************Axis************************/
	// create a Y data value marker
	
	$Marker->setFillColor('transparent');
	$Marker->setBorderColor('transparent');
	$Marker->setFontSize(7);
	$Marker->setFontColor('black');
	
	// create a pin-point marker type
	$Plot->setTickLength(14);
	$Plot->setAxisTicks(5);
	/********************************color of arrows*************/
	$FillArray = & Image_Graph::factory('Image_Graph_Fill_Array');
	$FillArray->addColor('blue@0.6', 'Current Level');
	
	// create a line array
	$LineArray =& Image_Graph::factory('Image_Graph_Line_Array');
	$LineArray->addColor('blue', 'Current Level');
	$Plot->setArrowLineStyle($LineArray);
	$Plot->setArrowFillStyle($FillArray);
	
	/***************************MARKER OR ARROW************************/
	
	// create a Y data value marker
	$Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
	$Marker->setFillColor('black');
	$Marker->setBorderColor('blue');
	$Marker->setFontSize(9);
	$Marker->setFontColor('white');
	
	// create a pin-point marker type
	$PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(40, &$Marker));
	
	// and use the marker on the plot
	$Plot->setMarker($PointingMarker);
	/**************************RANGE*******************/
	
	// create the dataset
	$Plot->addRangeMarker(0, 80);
	$Plot->addRangeMarker(80, 90);
	$Plot->addRangeMarker(90, 100);
	
	// create a fillstyle for the ranges
	$FillRangeArray = & Image_Graph::factory('Image_Graph_Fill_Array');
	$FillRangeArray->addColor('#ff0000@0.5');
	$FillRangeArray->addColor('orange@0.7');
	$FillRangeArray->addColor('green@0.7');
	$Plot->setRangeMarkerFillStyle($FillRangeArray);
	
	// output the Graph
	$Graph->done();
?>