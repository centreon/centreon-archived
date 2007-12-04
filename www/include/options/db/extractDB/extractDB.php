<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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

	$sql = "SELECT VERSION() AS mysql_version";
	
	if($res =& $pearDB->query($sql)){
		$row =& $res->fetchRow();
		$version = $row['mysql_version'];
		if(preg_match("/^(3\.23|4\.|5\.)/", $version)){
			$db = $conf_oreon["db"];
			$db_name = ( preg_match("/^(3\.23\.[6-9])|(3\.23\.[1-9][1-9])|(4\.)/", $version) ) ? "`$db`" : $db;
			$sql = "SHOW TABLE STATUS FROM `".$conf_oreon["db"]."`";
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
?>
 <table id="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="2">&nbsp;&nbsp;Oreon&nbsp;<?php print $lang["DB_status"]; ?></td></tr>
 	<tr class="list_one"><td class="FormRowField"><?php print $lang["db_lenght"] ; ?></td><td class="FormRowValue"><?php$dbsize /= 1024; print round($dbsize, 2); ?>Ko</td></tr>
	<tr class="list_two"><td class="FormRowField"><?php print $lang["db_nb_entry"] ; ?></td><td class="FormRowValue"><?php print $rows; ?></td></tr>
</table>
<div id="validForm">
	<p>
		<form action="" method="post">
			<input name="s" type="hidden" value="1">
			<input name="export_sub_list" type="submit" value="<?php echo $lang['db_extract']; ?>">
		</form>
	</p>
</div>