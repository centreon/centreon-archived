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
 	
	include_once ("@CENTREON_ETC@/centreon.conf.php");
	include_once ("DB.php");
	include_once ("$centreon_path/www/DBconnect.php");
	
	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();
	
	aff_header("Centreon Upgrade Wizard", "Select Version", 3); ?>
	In order for your Centreon upgrade to function properly, please select the mysql script file.<br /><br />
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
      <tr>
        <th style="padding-left:20px;" colspan="2">Upgrade SQL Scripts</th>
      </tr>
	  <tr>
        <td><b>MySQL Scripts</b></td>
        <td align="right">
        	<select name="mysqlscript">
        	<?php       		
        		chdir('sql');
        		foreach (glob("Update-NDO-".$version["value"]."_to_*.sql") as $filename) {
					echo '<option value="'.$filename.'">'.$filename.'</option>'; }
        	?>
        	</select>
       	</td>
      </tr>
	</table>
	<?php
	aff_middle();
	print "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
	aff_footer();
?>
