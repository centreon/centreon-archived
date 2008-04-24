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
 
$oreon = NULL;
require_once ("../../oreon.conf.php");
require_once ("../../DBconnect.php");

$res =& $pearDB->query("SELECT service_id FROM service");
while($res->fetchInto($service))	{
	$cmd = "SELECT esi_id FROM extended_service_information where service_service_id = ".$service["service_id"]." ";	
	$result =& $pearDB->query($cmd);
	if ($result->numRows() == 0){
		$rq = "INSERT INTO `extended_service_information` ( `esi_id` , `service_service_id` , `esi_notes` , `esi_notes_url` , `esi_action_url` , `esi_icon_image` , `esi_icon_image_alt` ) ";
		$rq .= "VALUES (NULL , '".$service["service_id"]."', NULL , NULL , NULL , NULL , NULL);";
		$pearDB->query($rq);
	}
	$result->free();
}
$res->free();
?>