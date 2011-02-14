<?php
/*
 * Copyright 2005-2011 MERETHIS
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


if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["oreonlogin"] = $_POST["oreonlogin"];
	$_SESSION["oreonpasswd"] = $_POST["oreonpasswd"];
	$_SESSION["oreonfirstname"] = $_POST["oreonfirstname"];
	$_SESSION["oreonlastname"] = $_POST["oreonlastname"];
	$_SESSION["oreonemail"] = $_POST["oreonemail"];
}
aff_header("Centreon Setup Wizard", "LDAP Authentication", 9);
?>
If you want to enable LDAP authentication, please complete the following fields. If you don't, leave them blank.<br /><br />
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th style="padding-left:20px " colspan="2">LDAP Configuration</th>
  </tr>
   <tr>
    <td style="padding-left:50px ">Enable LDAP Authentication ?</td>
	<td>
		<input type="radio" name="ldap_auth_enable"  value="0" <?php if (isset($_SESSION["ldap_auth_enable"]) && $_SESSION["ldap_auth_enable"] == "0") { print "checked"; $display ="none" ;} else if (!isset($_SESSION["ldap_auth_enable"])) { print "checked"; $display ="none";} ?> onClick="document.getElementById('ldap_settings').style.display = 'none';" >No
    	<input type="radio" name="ldap_auth_enable"  value="1" <?php if (isset($_SESSION["ldap_auth_enable"]) && $_SESSION["ldap_auth_enable"] == "1") { print "checked"; $display ="block"; }?>  onClick="document.getElementById('ldap_settings').style.display = 'block';" >Yes
 	</td>
  </tr>
  <tr>
  	<td colspan="2">
		<div id='ldap_settings' style="display: <?php echo $display; ?>;">
		<table cellpadding="0" cellspacing="0" border="0" width="90%" class="StyleDottedHr" align="center">
		  <tr>
		    <td style="padding-left:50px ">LDAP Host</td>
			<td><input name="ldap_host" type="text" value="<?php echo (isset($_SESSION["ldap_host"]) ?  $_SESSION["ldap_host"]  : "localhost" );?>"></td>
		  </tr>
		  <tr>
		    <td style="padding-left:50px ">LDAP Port</td>
			<td><input name="ldap_port" type="text" value="<?php echo (isset($_SESSION["ldap_port"]) ?  $_SESSION["ldap_port"]  :  "389" );?>"></td>
		  </tr>
		 <tr>
		    <td style="padding-left:50px ">LDAP Base DN</td>
			<td><input name="ldap_base_dn" type="text" value="<?php echo (isset($_SESSION["ldap_base_dn"]) ?  $_SESSION["ldap_base_dn"]  : "dc=foo,dc=fr" );?>"></td>
		  </tr>
		  <tr>
		    <td style="padding-left:50px ">LDAP Login Attribut</td>
			<td><input name="ldap_login_attrib" type="text" value="<?php echo (isset($_SESSION["ldap_login_attrib"]) ?  $_SESSION["ldap_login_attrib"]  : "uid" );?>"></td>
		  </tr>
		  <td style="padding-left:50px ">LDAP use SSL ?</td>
			<td>
				<input type="radio" name="ldap_ssl" value="0" <?php if (isset($_SESSION["ldap_ssl"]) && $_SESSION["ldap_ssl"] == "0") print "checked"; else if (!isset($_SESSION["ldap_ssl"])) print "checked"; ?>>No
		    	<input type="radio" name="ldap_ssl" value="1" <?php if (isset($_SESSION["ldap_ssl"]) && $_SESSION["ldap_ssl"] == "1") print "checked"; ?>>Yes

			</td>
		  </tr>
		 </table>
	</div>
   </td>
  </tr>
</table>
<?php
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
print $str;
aff_footer();

?>