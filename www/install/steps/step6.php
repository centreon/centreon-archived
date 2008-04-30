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
    <td><b>Oreon Database Name</b></td>
    <td align="right"><input type="text" name="nameOreonDB" value="<?php if (isset($_SESSION["nameOreonDB"])) print $_SESSION["nameOreonDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Oreon Data Storage Database Name</b></td>
    <td align="right"><input type="text" name="nameOdsDB" value="<?php if (isset($_SESSION["nameOdsDB"])) print $_SESSION["nameOdsDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Oreon Database Password</b></td>
    <td align="right"><input type="password" name="pwdOreonDB" value="<?php if (isset($_SESSION["pwdOreonDB"])) print $_SESSION["pwdOreonDB"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Confirm it</b></td>
    <td align="right"><input type="password" name="pwdOreonDB2" value="<?php if (isset($_SESSION["pwdOreonDB2"])) print $_SESSION["pwdOreonDB2"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Database location (localhost if blank)</b></td>
    <td align="right"><input type="text" name="dbLocation" value="<?php if (isset($_SESSION["dbLocation"])) print $_SESSION["dbLocation"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Nagios location (localhost if blank)</b></td>
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
  <!--<input type="hidden" name="mysqlVersion" value="3">-->
</table>
<?php
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
print $str;
aff_footer();
?>