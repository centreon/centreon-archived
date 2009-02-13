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
 
	header('Content-Type: text/xml');
	header('Cache-Control: no-cache');
	
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";	
	require_once $centreon_path."/www/class/centreonXML.class.php";
		
	$pearDB = new CentreonDB();
	
	$DBRESULT =& $pearDB->query("SELECT `host_id`, `host_name` FROM `host` WHERE `host_register` = '0' ORDER BY `host_name`");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	
	/*
	 *  The first element of the select is empty
	 */
	$buffer = new CentreonXML();
	$buffer->startElement("template_data");
	$buffer->startElement("template");
	$buffer->writeElement("tp_id", "0");
	$buffer->writeElement("tp_alias", "empty");
	$buffer->endElement();	
	
	/*
	 *  Now we fill out the select with templates id and names
	 */
	while ($h =& $DBRESULT->fetchRow()){
		if ($h['host_id'] != $_GET['host_id']) {
			$buffer->startElement("template");
			$buffer->writeElement("tp_id", $h['host_id']);
			$buffer->writeElement("tp_alias", $h['host_name']);
			$buffer->endElement();				
		}
	}
	$DBRESULT->free();
	$buffer->endElement();
	$buffer->output();
?>