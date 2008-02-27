<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!$oreon)
		exit();

	$res =& $pearDB->query("SELECT * FROM inventory_manufacturer");
	if (PEAR::isError($res))
			print $res->getDebugInfo()."<br />";	
	$manu_array = array();
	$manu_array[0] = "Server";
	while ($r =& $res->fetchRow())
		$manu_array[$r["id"]] = $r["alias"];

	$res =& $pearDB->query("SELECT host_id, type_ressources FROM inventory_index");
	while ($r =& $res->fetchRow()){
		$resHost =& $pearDB->query("SELECT host_name FROM host WHERE host_id = '".$r["host_id"]."'");
		$rH =& $resHost->fetchRow();
		if (isset($manu_array[$r["type_ressources"]]))
			print $rH["host_name"] . " - " . $manu_array[$r["type_ressources"]] . "<br />";
		else
			print $rH["host_name"] . " - " . "Not defined" . "<br />";
	}


		
?>