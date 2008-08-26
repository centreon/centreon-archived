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
 * This script exports Curves templates in a sql file.
 *
 * PHP version 5
 *
 * @package exportCurves.php
 * @author Damien Duponchelle
 * @version $Id: $
 * @copyright (c) 2007-2008 Centreon
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

	include_once("@CENTREON_ETC@/entreon.conf.php");	
	include_once($centreon_path."/www/DBconnect.php");

	header("Content-Type: application/text");
	header("Content-disposition: filename=Export-Curves.sql");

	if(isset($_GET['id']) == true) {
	$id = unserialize($_GET['id']);
	}
	
	if($id) {
	$condition = "WHERE ";
		for($i = 0; $i < sizeof($id);$i++) {
		$condition .= "compo_id = '".$id[$i]."'";
			if(isset($id[$i+1])) {
				$condition .= " OR ";		
			}
		}
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_components_template` $condition");	
	} else {
	$DBRESULT =& $pearDB->query("SELECT * FROM `giv_components_template`");		
	}
	
	$allDatas = array();
	while ($datas =& $DBRESULT->fetchRow()) {
		// DEBUG 
		/*
		print $datas["name"];
		print $datas["ds_order"];
		print $datas["ds_name"];
		print $datas["ds_color_line"];
		print $datas["ds_color_area"];
		print $datas["ds_filled"];
		print $datas["ds_max"];
		print $datas["ds_min"];
		print $datas["ds_average"];
		print $datas["ds_last"];
		print $datas["ds_tickness"];
		print $datas["ds_transparency"];
		print $datas["ds_invert"];
		print $datas["default_tpl1"];
		print $datas["comment"];
		*/
		$allDatas[] = $datas;
	}
	
	//print_r($allDatas);
	
	/*
	 * Curves SQL Export
	 */ 
	print "--\n";
	print "-- Curves SQL Export\n";
	print "--\n\n";
	
	foreach($allDatas as $template) {
		print "-- CURVE '".strtoupper($template["ds_name"])."'\n";
		print "INSERT INTO `giv_components_template` (";
		print "`name`, `ds_order`, `ds_name`, `ds_color_line`, `ds_color_area`, `ds_filled`, `ds_max`, `ds_min`, `ds_average`, `ds_last`, `ds_tickness`, `ds_transparency`, `ds_invert`, `default_tpl1`, `comment`) ";		
		print "VALUES (";
		print "'".$template["name"]."', ";
		print "'".$template["ds_order"]."', ";
		print "'".$template["ds_name"]."', ";
		print "'".$template["ds_color_line"]."', ";
		print "'".$template["ds_color_area"]."', ";
		print "'".$template["ds_filled"]."', ";
		print "'".$template["ds_max"]."', ";
		print "'".$template["ds_min"]."', ";
		print "'".$template["ds_average"]."', ";
		print "'".$template["ds_last"]."', ";
		print "'".$template["ds_tickness"]."', ";	
		print "'".$template["ds_transparency"]."', ";
		print "'".$template["ds_invert"]."', ";
		print "'".$template["default_tpl1"]."', ";
		print "'".$template["comment"]."');";
		print "\n\n";
	}	

?>