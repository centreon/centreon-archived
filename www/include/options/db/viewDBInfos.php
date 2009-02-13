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
	
	require_once './class/centreonDB.class.php';
	
	$pearDBndo = new CentreonDB("ndo");
	
	/*
	 * return database Properties
	 *
	 * <code>
	 * $dataCentreon 		= returnProperties($pearDB, $conf_centreon["db"]);
	 * </code>
	 *
	 * @param{TAB}int{TAB}$pearDB{TAB}Database connexion
	 * @param{TAB}string{TAB}$base{TAB}db name
	 * @return{TAB}array{TAB}dbsize, numberOfRow, freeSize
	 */
	
	function returnProperties($pearDB, $base){		
		/*
		 * Get Version
		 */
		if ($res =& $pearDB->query("SELECT VERSION() AS mysql_version")){
			$row =& $res->fetchRow();
			$version = $row['mysql_version'];
			if (preg_match("/^(3\.23|4\.|5\.)/", $version)){
				$db_name = (preg_match("/^(3\.23\.[6-9])|(3\.23\.[1-9][1-9])|(4\.)/", $version) ) ? "`$base`" : $base;
				if ($DBRESULT =& $pearDB->query("SHOW TABLE STATUS FROM `$base`")){
					$dbsize = 0;
					$rows = 0;
					$datafree = 0;
					while ($tabledata_ary =& $DBRESULT->fetchRow()) {
						$dbsize 	+= $tabledata_ary['Data_length'] + $tabledata_ary['Index_length'];
						$rows 		+= $tabledata_ary['Rows'];
						$datafree	+= $tabledata_ary['Data_free'];  
					}
					$DBRESULT->free();
				}
			} else {
				$dbsize = NULL;
				$rows = NULL;
				$datafree = NULL;
			}
		}
		return array($dbsize / 1024 / 1024 , $rows, $datafree);
	}
	
	/*
	 * Get NDO Properties
	 */
	
	$ndoInformations = getNDOInformations();
	
	/*
	 * Get Properties
	 */
	 
	$dataCentreon 		= returnProperties($pearDB, $conf_centreon["db"]);
	$dataCentstorage 	= returnProperties($pearDBO, $conf_centreon["dbcstg"]);
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		$dataNDOutils = array(0 => '-', 1 => '-');
	} else {
		$dataNDOutils 		= returnProperties($pearDBndo, $ndoInformations["db_name"]);
	}
?>
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="5"><img src='./img/icones/16x16/server_network.gif'>&nbsp;Centreon&nbsp;<?php print _("DataBase Statistics"); ?></td></tr>
	<tr class="list_lvl_1">
		<td class="ListColLvl1_name">&nbsp;</td>
		<td class="ListColLvl1_name"><?php echo $conf_centreon["db"]; ?></td>
		<td class="ListColLvl1_name"><?php echo $conf_centreon["dbcstg"]; ?></td>
		<td class="ListColLvl1_name"><?php echo $ndoInformations["db_name"]; ?></td>
		<td class="ListColLvl1_name">&nbsp;</td>
	</tr>	
 	<tr class="list_one">
 		<td class="FormRowField"><?php print _("Length") ; ?></td>
 		<td class="FormRowValue"><?php print round($dataCentreon[0], 2); ?> Mo</td>
 		<td class="FormRowValue"><?php print round($dataCentstorage[0], 2); ?> Mo</td>
 		<td class="FormRowValue"><?php print round($dataNDOutils[0], 2); ?> Mo</td>
 		<td class="ListColLvl1_name">&nbsp;</td>
 	</tr>
	<tr class="list_two">
		<td class="FormRowField"><?php print _("Number of entries") ; ?></td>
 		<td class="FormRowValue"><?php print $dataCentreon[1]; ?></td>
 		<td class="FormRowValue"><?php print $dataCentstorage[1]; ?></td>
 		<td class="FormRowValue"><?php print $dataNDOutils[1]; ?></td>
		<td class="ListColLvl1_name">&nbsp;</td>
	</tr>
</table>