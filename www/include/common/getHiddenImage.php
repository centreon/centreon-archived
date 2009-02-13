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

	require_once ("@CENTREON_ETC@/centreon.conf.php");		
	require_once ("../../$classdir/Session.class.php");
	require_once ("../../$classdir/Oreon.class.php");
	require_once ("../../$classdir/centreonDB.class.php");
	
	$pearDB = new CentreonDB();
	Session::start();
	$oreon =& $_SESSION["oreon"];
	
	$session =& $pearDB->query("SELECT * FROM `session` WHERE `session_id` = '".session_id()."'");
	if (!$session->numRows())
		exit;
	
	$logos_path = "";
	
	if (isset($_GET["logo"]) && $_GET["logo"] && ($_GET["logo"] == "1")){
		$logos_path = $oreon->optGen["nagios_path_img"];
	}

	if (isset($_GET["path"]) && $_GET["path"] && is_file($logos_path . $_GET["path"])){
		$fd = fopen($logos_path . $_GET["path"], "r");
		$buffer = NULL;
		while (!feof($fd))
		    $buffer .= fgets($fd, 4096);
		fclose ($fd);
		print $buffer;
	}
?>