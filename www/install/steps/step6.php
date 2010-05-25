<?php
/*
 * Copyright 2005-2010 MERETHIS
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

if (!isset($_SESSION["nameOreonDB"])) {
	$_SESSION["nameOreonDB"] = "centreon";
}
if (!isset($_SESSION["nameOdsDB"])) {
	$_SESSION["nameOdsDB"] = "centstorage";
}
if (!isset($_SESSION["nameStatusDB"])) {
	$_SESSION["nameStatusDB"] = "centstatus";
}

aff_header("Centreon Setup Wizard", "DataBase Configuration", 6);
if (isset($passwd_error) && $passwd_error)
	print "<center><b><span class=\"stop\">$passwd_error</span></b></center><br />";
?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
    <td><b>Root password for Mysql</b></td>
    <td align="right"><input type="password" name="pwdroot" value="<?php if (isset($_SESSION["pwdroot"])) print $_SESSION["pwdroot"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Centreon Database Name</b></td>
    <td align="right"><input type="text" name="nameOreonDB" value="<?php if (isset($_SESSION["nameOreonDB"])) print $_SESSION["nameOreonDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Centstorage Database Name</b></td>
    <td align="right"><input type="text" name="nameOdsDB" value="<?php if (isset($_SESSION["nameOdsDB"])) print $_SESSION["nameOdsDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>NDO Database Name</b></td>
    <td align="right"><input type="text" name="nameStatusDB" value="<?php if (isset($_SESSION["nameStatusDB"])) print $_SESSION["nameStatusDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Database Password</b></td>
    <td align="right"><input type="password" name="pwdOreonDB" value="<?php if (isset($_SESSION["pwdOreonDB"])) print $_SESSION["pwdOreonDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Confirm it</b></td>
    <td align="right"><input type="password" name="pwdOreonDB2" value="<?php if (isset($_SESSION["pwdOreonDB2"])) print $_SESSION["pwdOreonDB2"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Database location</b> (it's MySQL Server IP address. localhost if blank)</td>
    <td align="right"><input type="text" name="dbLocation" value="<?php if (isset($_SESSION["dbLocation"])) print $_SESSION["dbLocation"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Centreon Web Interface location</b> (localhost if blank)</td>
    <td align="right"><input type="text" name="nagiosLocation" value="<?php if (isset($_SESSION["nagiosLocation"])) print $_SESSION["nagiosLocation"]; ?>"></td>
  </tr>
  <tr>
    <td colspan="2" ><span class="small">If you used a remote mysql server, enter ip address of your oreon box</span></td>
  </tr>
   <tr>
    <td><b>MySQL Client version (Password Haching Changes)</b></td>
    <td align="right">
    	<select name="mysqlVersion">
    		<option value="3" <?php if (isset($_SESSION["mysqlVersion"]) && $_SESSION["mysqlVersion"] == "3") print "selected"; else if (!isset($_SESSION["mysqlVersion"])) print "selected"; ?>>>= 4.1 - PASSWORD()</option>
    		<option value="2" <?php if (isset($_SESSION["mysqlVersion"]) && $_SESSION["mysqlVersion"] == "2") print "selected"; ?>>>= 4.1 - OLD_PASSWORD()</option>
    		<option value="1" <?php if (isset($_SESSION["mysqlVersion"]) && $_SESSION["mysqlVersion"] == "1") print "selected"; ?>>3.x</option>
    	</select>
   	</td>
  </tr>
</table>
<?php
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
print $str;
aff_footer();
?>