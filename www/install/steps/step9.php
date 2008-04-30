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
 
 
if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["oreonlogin"] = $_POST["oreonlogin"];
	$_SESSION["oreonpasswd"] = $_POST["oreonpasswd"];
	$_SESSION["oreonfirstname"] = $_POST["oreonfirstname"];
	$_SESSION["oreonlastname"] = $_POST["oreonlastname"];
	$_SESSION["oreonemail"] = $_POST["oreonemail"];
	$_SESSION["oreonlang"] = $_POST["oreonlang"];
}
aff_header("Centreon Setup Wizard", "LDAP Authentification", 9);   ?>
If you want enable LDAP authentification, please complete the following fields. If you don't, leave blank.<br /><br />
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th style="padding-left:20px " colspan="2">LDAP Configuration</th>
  </tr>
   <tr>
    <td style="padding-left:50px ">Enable LDAP Authentification ?</td>
	<td>
		<input type="radio" name="ldap_auth_enable"  value="0" <?php if (isset($_SESSION["ldap_auth_enable"]) && $_SESSION["ldap_auth_enable"] == "0") { print "checked"; $display ="none" ;} else if (!isset($_SESSION["ldap_auth_enable"])) { print "checked"; $display ="none";} ?> onClick="document.getElementById('ldap_settings').style.display = 'none';" >No
    	<input type="radio" name="ldap_auth_enable"  value="1" <?php if (isset($_SESSION["ldap_auth_enable"]) && $_SESSION["ldap_auth_enable"] == "1") { print "checked"; $display ="block"; }?>  onClick="document.getElementById('ldap_settings').style.display = 'block';" >Yes

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