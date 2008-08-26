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

/*
 * This script exports templates in a sql file.
 *
 * PHP version 5
 *
 * @package exportTemplates.php
 * @author Damien Duponchelle
 * @version $Id: $
 * @copyright (c) 2007-2008 Centreon
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

	include_once("@CENTREON_ETC@/centreon.conf.php");	
	include_once($centreon_path."/www/DBconnect.php");

	header("Content-Type: application/text");
	header("Content-disposition: filename=Export-Templates.sql");

	if(isset($_GET['id']) == true) {
	$id = unserialize($_GET['id']);
	}
	
	if($id) {
	$condition = "WHERE ";
		for($i = 0; $i < sizeof($id);$i++) {
		$condition .= "graph_id = '".$id[$i]."'";
			if(isset($id[$i+1])) {
				$condition .= " OR ";		
			}
		}
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template` $condition");	
	} else {
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_graphs_template`");		
	}
	
	$allDatas = array();
	while ($datas =& $DBRESULT->fetchRow()) {
		$allDatas[] = $datas;
	}
	
	/*
	 * Templates SQL Export
	 */ 
	print "--\n";
	print "-- Templates SQL Export\n";
	print "--\n\n";
	
	foreach($allDatas as $template) {
		print "-- GRAPHS '".strtoupper($template["name"])."'\n";
		print "INSERT INTO `giv_graphs_template` (";
		print "`name`, `vertical_label`, `width`, `height`, `base`, `lower_limit`, `upper_limit`, `bg_grid_color`, `bg_color`, `police_color`, `grid_main_color`, `grid_sec_color`, `contour_cub_color`, `col_arrow`, `col_top`, `col_bot`, `default_tpl1`, `stacked`, `split_component`, `comment`) ";		
		print "VALUES (";
		print "'".$template["name"]."', ";
		print "'".$template["vertical_label"]."', ";
		print "'".$template["width"]."', ";
		print "'".$template["height"]."', ";
		print "'".$template["base"]."', ";
		print "'".$template["lower_limit"]."', ";
		print "'".$template["upper_limit"]."', ";
		print "'".$template["bg_grid_color"]."', ";
		print "'".$template["bg_color"]."', ";
		print "'".$template["police_color"]."', ";
		print "'".$template["grid_main_color"]."', ";	
		print "'".$template["grid_sec_color"]."', ";
		print "'".$template["contour_cub_color"]."', ";
		print "'".$template["col_arrow"]."', ";
		print "'".$template["col_top"]."', ";
		print "'".$template["col_bot"]."', ";
		print "'".$template["default_tpl1"]."', ";	
		print "'".$template["stacked"]."', ";	
		print "'".$template["split_component"]."', ";					
		print "'".$template["comment"]."');";
		print "\n\n";
	}
	
?>