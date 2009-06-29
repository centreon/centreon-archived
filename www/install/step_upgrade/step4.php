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
 	
	include_once $centreon_path . "/www/class/centreonDB.class.php";
	
	if (count(glob("Update-DB-".$version["value"]."_to_*.sql")) == 0) {
        require_once("./step_upgrade/step6.php");
    } else {
	
		$pearDB = new CentreonDB();
		
		$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
		$version =& $DBRESULT->fetchRow();
		
		aff_header("Centreon Upgrade Wizard", "Select Version", 4); ?>
		In order for your Centreon upgrade to work properly, please select the appropriate Centreon upgrade script.<br /><br />
		<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
	      <tr>
	        <th style="padding-left:20px;" colspan="2">Upgrade SQL Scripts</th>
	      </tr>
		  <tr>
	        <td><b>MySQL Scripts</b></td>
	        <td align="right">
	        	<select name="script">
	        	<?php       		
	        		chdir('sql/centreon/');
	        		foreach (glob("Update-DB-".$version["value"]."_to_*.sql") as $filename) {
						$filenameDisplayed = str_replace("Update-DB-", "", $filename);
						$filenameDisplayed = str_replace(".sql", "", $filenameDisplayed);
						$filenameDisplayed2 = str_replace("_", " ", $filenameDisplayed);
						echo '<option value="'.$filenameDisplayed.'">'.$filenameDisplayed2.'</option>'; 
					}
	        	?>
	        	</select>
	       	</td>
	      </tr>
		</table>
		<?php
		aff_middle();
		print "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
		aff_footer();	
	}
?>
