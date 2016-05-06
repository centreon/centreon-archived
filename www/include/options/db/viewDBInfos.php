<?php
/*
 * Copyright 2005-2015 Centreon
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

if (!isset($centreon)) {
	exit();
}

require_once './class/centreonDB.class.php';

/*
 * Get Properties
 */
$dataCentreon = $pearDB->getProperties();
$dataCentstorage = $pearDBO->getProperties();

?>
<table class="ListTable">
 	<tr class="ListHeader"><td class="FormHeader" colspan="3"><?php print _("Centreon DataBase Statistics"); ?></td></tr>
	<tr class="list_lvl_1">
		<td class="ListColLvl1_name">&nbsp;</td>
		<td class="ListColLvl1_name"><?php echo $conf_centreon["db"]; ?></td>
		<td class="ListColLvl1_name"><?php echo $conf_centreon["dbcstg"]; ?></td> 
	</tr>
 	<tr class="list_one">
 		<td class="FormRowField"><?php print _("Data size") ; ?></td>
 		<td class="FormRowValue"><?php print round($dataCentreon[0], 2); ?> Mo</td>
 		<td class="FormRowValue"><?php print round($dataCentstorage[0], 2); ?> Mo</td>
 	</tr>
 	<tr class="list_two">
 		<td class="FormRowField"><?php print _("Index size") ; ?></td>
 		<td class="FormRowValue"><?php print round($dataCentreon[1], 2); ?> Mo</td>
 		<td class="FormRowValue"><?php print round($dataCentstorage[1], 2); ?> Mo</td>
 	</tr>
 	<tr class="list_one">
		<td class="FormRowField"><?php print _("Number of entries") ; ?></td>
 		<td class="FormRowValue"><?php print $dataCentreon[2]; ?></td>
 		<td class="FormRowValue"><?php print $dataCentstorage[2]; ?></td>
	</tr>
	<tr class="list_two">
		<td class="FormRowField"><?php print _("Data free") ; ?></td>
 		<td class="FormRowValue"><?php print round($dataCentreon[3], 2); ?> Mo</td>
 		<td class="FormRowValue"><?php print round($dataCentstorage[3], 2); ?> Mo</td>
	</tr>
</table>
