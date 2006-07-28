<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Inventory » is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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

	if (!isset($oreon))
		exit();

	function change_manufacturer($hosts = array(), $manufacturer_id = NULL)		{
		global $pearDB;
		if (!$manufacturer_id) $manufacturer_id = "NULL";
		else $manufacturer_id = "'".$manufacturer_id."'";
		foreach($hosts as $key=>$value)	{
			$str = "UPDATE `inventory_index` SET `type_ressources` = ".$manufacturer_id." WHERE `host_id` = ".$key;
			$pearDB->query($str);
		}
	}	
?>	