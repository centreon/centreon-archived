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

aff_header("Centreon Setup Wizard", "Environment Configuration", 3);   ?>
In order for your Centreon installation to function properly, please complete the following fields.<br /><br />
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  	<tr>
    	<th style="padding-left:20px " colspan="2">Environment Configurations</th>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios user</td>
		<td><input name="nagios_user" type="text" value="<?php echo (isset($_SESSION["nagios_user"]) ?  $_SESSION["nagios_user"]  : (isset($conf_centreon['nagios_user']) ?  $conf_centreon['nagios_user']  : "nagios" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios group</td>
		<td><input name="nagios_group" type="text" value="<?php echo (isset($_SESSION["nagios_group"]) ?  $_SESSION["nagios_group"]  : (isset($conf_centreon["nagios_group"]) ?  $conf_centreon["nagios_group"]  : "nagios" ) );?>"></td>
  	</tr>
 	<tr>
    	<td style="padding-left:50px ">Apache User</td>
		<td><input name="apache_user" type="text" value="<?php echo (isset($_SESSION["apache_user"]) ?  $_SESSION["apache_user"]  : (isset($conf_centreon["apache_user"]) ?  $conf_centreon["apache_user"]  : "apache" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Apache Group</td>
		<td><input name="apache_group" type="text" value="<?php echo (isset($_SESSION["apache_group"]) ?  $_SESSION["apache_group"]  : (isset($conf_centreon["apache_group"]) ?  $conf_centreon["apache_group"]  : "apache" ) );?>"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios Version</td>
		<td>
		<select name="nagios_version">
			<option value="2" <?php if (isset($_SESSION["nagios_version"]) && $_SESSION["nagios_version"] == "2") print "selected"; ?>>2.x</option>
    		<option value="3" <?php if (isset($_SESSION["nagios_version"]) && $_SESSION["nagios_version"] == "3") print "selected"; else if (!isset($_SESSION["nagios_version"])) print "selected"; ?>>3.x</option>
    	</select>
		</td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios configuration directory</td>
		<td><input name="nagios_conf" type="text" value="<?php echo (isset($_SESSION["nagios_conf"]) ?  $_SESSION["nagios_conf"]  : (isset($conf_centreon["nagios_conf"]) ?  $conf_centreon["nagios_conf"]  : "/usr/local/nagios/etc/" ) );?>" size="40"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">Nagios plugins</td>
		<td><input name="nagios_plugins" type="text" value="<?php echo (isset($_SESSION["nagios_plugins"]) ?  $_SESSION["nagios_plugins"]  : (isset($conf_centreon["nagios_plugins"]) ?  $conf_centreon["nagios_plugins"]  : "/usr/local/nagios/libexec/" ) );?>" size="40"></td>
  	</tr>
  	<tr>
    	<td style="padding-left:50px ">RRDTool binary</td>
		<td><input name="rrdtool_dir" type="text" value="<?php echo (isset($_SESSION["rrdtool_dir"]) ?  $_SESSION["rrdtool_dir"]  : (isset($conf_centreon["rrdtool_dir"]) ?  $conf_centreon["rrdtool_dir"]  : "/usr/bin/rrdtool" ) );?>" size="40"></td>
  	</tr>
</table>
<?php
aff_middle();
print "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
aff_footer();

?>