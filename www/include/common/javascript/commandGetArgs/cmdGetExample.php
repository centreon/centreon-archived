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

	# return argument for specific command in txt format
	# use by ajax

	require_once("@CENTREON_ETC@/centreon.conf.php");
	require_once($centreon_path."www/class/centreonDB.php");	

	function myDecodeService($arg)	{
		$arg = str_replace('#BR#', "\\n", $arg);
		$arg = str_replace('#T#', "\\t", $arg);
		$arg = str_replace('#R#', "\\r", $arg);
		$arg = str_replace('#S#', "/", $arg);
		$arg = str_replace('#BS#', "\\", $arg);
		return html_entity_decode($arg, ENT_QUOTES);
	}	
	
	header('Content-type: text/html; charset=iso-8859-1');

	$pearDB = new CentreonDB();

	if (isset($_POST["index"])){
		$DBRESULT =& $pearDB->query("SELECT `command_example` FROM `command` WHERE `command_id` = '". $_POST["index"] ."'");
		if (PEAR::isError($DBRESULT))
			print "Mysql Error : ".$DBRESULT->getMessage();
		while ($arg =& $DBRESULT->fetchRow())
			echo utf8_encode(myDecodeService($arg["command_example"]));
		unset($arg);
		unset($DBRESULT);
		$pearDB->disconnect();
	}	
?>