<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	include_once("../oreon.conf.php");
	include_once("DB.php");
	include_once("../DBconnect.php");
	
	$DBRESULT =& $pearDB->query("SELECT `value` FROM `oreon_informations` WHERE `key` = 'version'");
	$DBRESULT->fetchInto($version);
	
	aff_header("Oreon Upgrade Wizard", "Select Version", 5); ?>
	In order for your Oreon upgrade to function properly, please select the mysql script file.<br><br>
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
      <tr>
        <th style="padding-left:20px;" colspan="2">Upgrade SQL Scripts</th>
      </tr>
	  <tr>
        <td><b>MySQL Scripts</b></td>
        <td align="right">
        	<select name="mysqlscript">
        	<?        		
        		chdir('sql');
        		foreach (glob("UpdateDB-".$version["value"]."_to_*.sql") as $filename) {
					echo '<option value="'.$filename.'">'.$filename.'</option>'; }
        	?>
        	</select>
       	</td>
      </tr>
	</table>
	<?
	aff_middle();
	print "<input class='button' type='submit' name='goto-B' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
	aff_footer();
?>