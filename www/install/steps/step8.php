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

aff_header("Centreon Setup Wizard", "User Interface Configuration", 8);
if (isset($passwd_error) && $passwd_error)
	print "<center><b><span class=\"stop\">$passwd_error</span></b></center><br />";
?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
    <td><b>Administrator login for Centreon</b></td>
    <td align="right"><input type="text" name="oreonlogin" value="<?php if (isset($_SESSION["oreonlogin"])) print $_SESSION["oreonlogin"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator password</b></td>
    <td align="right"><input type="password" name="oreonpasswd" value="<?php if (isset($_SESSION["oreonpasswd"])) print $_SESSION["oreonpasswd"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Confirm Password</b></td>
    <td align="right"><input type="password" name="oreonpasswd2" value="<?php if (isset($_SESSION["oreonpasswd"])) print $_SESSION["oreonpasswd"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator firstname</b></td>
    <td align="right"><input type="text" name="oreonfirstname" value="<?php if (isset($_SESSION["oreonfirstname"])) print $_SESSION["oreonfirstname"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator lastname</b></td>
    <td align="right"><input type="text" name="oreonlastname" value="<?php if (isset($_SESSION["oreonlastname"])) print $_SESSION["oreonlastname"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator email</b></td>
    <td align="right"><input type="text" name="oreonemail" value="<?php if (isset($_SESSION["oreonemail"])) print $_SESSION["oreonemail"]; ?>"></td>
  </tr>
</table>
<?php
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
print $str;
aff_footer();
?>
