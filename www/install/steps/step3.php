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

aff_header("Centreon Setup Wizard", "Environment Configuration", 3);   ?>
In order for your Centreon installation to function properly, please complete the following fields.<br /><br />
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  	<tr>
    	<th style="padding-left:20px " colspan="2">Environment Configurations</th>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios user</td>
		<td><input name="nagios_user" type="text" value="<?php echo (isset($_SESSION["nagios_user"]) ?  $_SESSION["nagios_user"]  : (isset($conf_installoreon['nagios_user']) ?  $conf_installoreon['nagios_user']  : "nagios" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios group</td>
		<td><input name="nagios_group" type="text" value="<?php echo (isset($_SESSION["nagios_group"]) ?  $_SESSION["nagios_group"]  : (isset($conf_installoreon["nagios_group"]) ?  $conf_installoreon["nagios_group"]  : "nagios" ) );?>"></td>
  	</tr>
 	<tr>
    	<td style="padding-left:50px ">Apache User</td>
		<td><input name="apache_user" type="text" value="<?php echo (isset($_SESSION["apache_user"]) ?  $_SESSION["apache_user"]  : (isset($conf_installoreon["apache_user"]) ?  $conf_installoreon["apache_user"]  : "apache" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Apache Group</td>
		<td><input name="apache_group" type="text" value="<?php echo (isset($_SESSION["apache_group"]) ?  $_SESSION["apache_group"]  : (isset($conf_installoreon["apache_group"]) ?  $conf_installoreon["apache_group"]  : "apache" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios Version</td>
		<td>
		<select name="nagios_version">
			<option value="2" <?php if (isset($_SESSION["nagios_version"]) && $_SESSION["nagios_version"] == "2") print "selected"; else if (!isset($_SESSION["nagios_version"])) print "selected"; ?>>2.x</option>
    		<option value="3" <?php if (isset($_SESSION["nagios_version"]) && $_SESSION["nagios_version"] == "3") print "selected"; ?>>3.x</option>
    	</select>
		</td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios configuration directory</td>
		<td><input name="nagios_conf" type="text" value="<?php echo (isset($_SESSION["nagios_conf"]) ?  $_SESSION["nagios_conf"]  : (isset($conf_installoreon["nagios_conf"]) ?  $conf_installoreon["nagios_conf"]  : "/usr/local/nagios/etc/" ) );?>" size="40"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios plugins</td>
		<td><input name="nagios_plugins" type="text" value="<?php echo (isset($_SESSION["nagios_plugins"]) ?  $_SESSION["nagios_plugins"]  : (isset($conf_installoreon["nagios_plugins"]) ?  $conf_installoreon["nagios_plugins"]  : "/usr/local/nagios/libexec/" ) );?>" size="40"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">RRDTool binary</td>
		<td><input name="rrdtool_dir" type="text" value="<?php echo (isset($_SESSION["rrdtool_dir"]) ?  $_SESSION["rrdtool_dir"]  : (isset($conf_installoreon["rrdtool_dir"]) ?  $conf_installoreon["rrdtool_dir"]  : "/usr/bin/rrdtool" ) );?>" size="40"></td>
  	</tr>
</table>
<?php
aff_middle();
print "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
aff_footer();

?>