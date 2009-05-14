<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
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