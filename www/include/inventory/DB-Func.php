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

	if (!isset($oreon))
		exit();

	function change_manufacturer($hosts = array(), $manufacturer_id = NULL)		{
		global $pearDB;
		if (!$manufacturer_id) 
			$manufacturer_id = "NULL";
		else 
			$manufacturer_id = "'".$manufacturer_id."'";
		foreach($hosts as $key=>$value)	{
			$DBRESULT =& $pearDB->query("UPDATE `inventory_index` SET `type_ressources` = ".$manufacturer_id." WHERE `host_id` = ".$key);
			if (PEAR::isError($DBRESULT))
				print $DBRESULT->getDebugInfo()."<br>";
		}
	}	
?>	