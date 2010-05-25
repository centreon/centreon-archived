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
 * SVN : $URL: http://svn.centreon.com/trunk/centreon/www/install/steps/step8.php $
 * SVN : $Id: step8.php 9837 2010-01-31 18:04:59Z jmathis $
 * 
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