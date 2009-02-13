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
 
	include_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "/www/class/centreonDB.class.php";	
	require_once $centreon_path . "/www/class/centreonXML.class.php";
	/* 
	 * start init db
	 */	
	$pearDB = new CentreonDB();
		
	$buffer = new CentreonXML();
	$buffer->startElement("traps");		

	if (isset($_POST["mnftr_id"])){ 
		$traps = array();
		if ($_POST["mnftr_id"])
			$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps WHERE manufacturer_id = " . $_POST["mnftr_id"]. " ORDER BY traps_name");
		else
			$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
		

		while ($trap =& $DBRESULT->fetchRow()){
				$buffer->startElement("trap");
				$buffer->writeElement("id", $trap["traps_id"]);
				$buffer->writeElement("name", $trap["traps_name"]);
				$buffer->endElement();							
		}
		$DBRESULT->free();
	} else{
		$buffer->writeElement("error", "mnftr_id not found");		
	}
	$buffer->endElement();	
	header('Content-Type: text/xml');
	$buffer->output();
?>