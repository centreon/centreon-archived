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
 
require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path."/www/DBconnect.php";

if (isset($_GET['div']) && isset($_GET['uid'])) {
	$my_div = $_GET['div'];
	$my_uid = $_GET['uid'];
	$query = "SELECT cp_value FROM contact_param WHERE cp_contact_id = '".$my_uid."' AND cp_key = '_Div_".$my_div."' LIMIT 1";
	$DBRESULT =& $pearDB->query($query);
	if ($DBRESULT->numRows()) {		
		$row =& $DBRESULT->fetchRow();
		if ($row['cp_value'] == "1")
			$update_val = "0"; 
		else
			$update_val = "1";
		$query2 = "UPDATE contact_param set cp_value = '".$update_val."' WHERE cp_contact_id = '".$my_uid."' AND cp_key = '_Div_".$my_div."'";		
	}
	else
		$query2 = "INSERT INTO contact_param (cp_key, cp_value, cp_contact_id) VALUES ('_Div_".$my_div."', '0', '".$my_uid."')";
	$pearDB->query($query2);		
}
?>