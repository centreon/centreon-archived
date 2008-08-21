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
	
	if ($res =& $pearDB->query("SELECT VERSION() AS mysql_version")){
		$row =& $res->fetchRow();
		$version = $row['mysql_version'];
		if(preg_match("/^(3\.23|4\.|5\.)/", $version)){
			$db = $conf_centreon["db"];
			$db_name = ( preg_match("/^(3\.23\.[6-9])|(3\.23\.[1-9][1-9])|(4\.)/", $version) ) ? "`$db`" : $db;
			$sql = "SHOW TABLE STATUS FROM `".$conf_centreon["db"]."`";
			if($res =& $pearDB->query($sql))
			{
				$dbsize = 0;
				$rows = 0;
				$datafree = 0;
				while ($tabledata_ary =& $res->fetchRow()){
					$dbsize += $tabledata_ary['Data_length'] + $tabledata_ary['Index_length'];
					$rows += $tabledata_ary['Rows'];
					$datafree += $tabledata_ary['Data_free'];
				}
			}
		} else {
			$dbsize = NULL;
			$rows = NULL;
			$datafree = NULL;
		}
	}
	
	if ($res =& $pearDBO->query("SELECT VERSION() AS mysql_version")){
		$row =& $res->fetchRow();
		$version = $row['mysql_version'];
		if (preg_match("/^(3\.23|4\.|5\.)/", $version)){
			$db = $conf_centreon["dbcstg"];
			$db_name = ( preg_match("/^(3\.23\.[6-9])|(3\.23\.[1-9][1-9])|(4\.)/", $version) ) ? "`$db`" : $db;
			$sql = "SHOW TABLE STATUS FROM `".$conf_centreon["dbcstg"]."`";
			if($res =& $pearDB->query($sql))
			{
				$dbsizeods = 0;
				$rowsods = 0;
				$datafreeods = 0;
				while ($tabledata_ary =& $res->fetchRow()){
					$dbsizeods += $tabledata_ary['Data_length'] + $tabledata_ary['Index_length'];
					$rowsods += $tabledata_ary['Rows'];
					$datafreeods += $tabledata_ary['Data_free'];
				}
			}
		} else {
			$dbsizeods = NULL;
			$rowsods = NULL;
			$datafreeods = NULL;
		}
	}
?>
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="2"><img src='./img/icones/16x16/server_network.gif'>&nbsp;Centreon&nbsp;<?php print _("DataBase Statistics"); ?></td></tr>
 	<tr class="list_one"><td class="FormRowField"><?php print _("Length") ; ?></td><td class="FormRowValue"><?php $dbsize /= 1024; print round($dbsize, 2); ?>Ko</td></tr>
	<tr class="list_two"><td class="FormRowField"><?php print _("Number of entries") ; ?></td><td class="FormRowValue"><?php print $rows; ?></td></tr>
</table>
<br />
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="2"><img src='./img/icones/16x16/server_network.gif'>&nbsp;CentStorage&nbsp;<?php print _("DataBase Statistics"); ?></td></tr>
 	<tr class="list_one"><td class="FormRowField"><?php print _("Length") ; ?></td><td class="FormRowValue"><?php $dbsizeods /= 1024; print round($dbsizeods, 2); ?>Ko</td></tr>
	<tr class="list_two"><td class="FormRowField"><?php print _("Number of entries") ; ?></td><td class="FormRowValue"><?php print $rowsods; ?></td></tr>
</table>
<br />
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="2"><img src='./img/icones/16x16/server_network.gif'>&nbsp;NDO&nbsp;<?php print _("DataBase Statistics"); ?></td></tr>
 	<tr class="list_one"><td class="FormRowField"><?php print _("Length") ; ?></td><td class="FormRowValue"><?php $dbsize /= 1024; print round($dbsize, 2); ?>Ko</td></tr>
	<tr class="list_two"><td class="FormRowField"><?php print _("Number of entries") ; ?></td><td class="FormRowValue"><?php print $rows; ?></td></tr>
</table>
