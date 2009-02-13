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

	if (!isset($oreon))
		exit();

	function getMyHostRow($host_id = NULL, $rowdata)	{
		if (!$host_id) 
			exit();
		global $pearDB;
		while(1)	{
			$DBRESULT =& $pearDB->query("SELECT host_".$rowdata.", host_template_model_htm_id FROM host WHERE host_id = '".$host_id."' LIMIT 1");
			$row =& $DBRESULT->fetchRow();
			if ($row["host_".$rowdata])
				return $row["host_$rowdata"];
			else if ($row["host_template_model_htm_id"])
				$host_id = $row["host_template_model_htm_id"];
			else
				break;
		}
	}
?>