<?
$oreon = NULL;
require_once ("../../oreon.conf.php");
require_once ("../../DBconnect.php");

$res =& $pearDB->query("SELECT service_id FROM service");
while($res->fetchInto($service))	{
	$rq = "INSERT INTO `extended_service_information` ( `esi_id` , `service_service_id` , `esi_notes` , `esi_notes_url` , `esi_action_url` , `esi_icon_image` , `esi_icon_image_alt` ) ";
	$rq .= "VALUES (NULL , '".$service["service_id"]."', NULL , NULL , NULL , NULL , NULL);";
	$pearDB->query($rq);	
}
?>