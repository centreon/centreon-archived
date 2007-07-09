<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

aff_header("Oreon Setup Wizard", "User Interface Configuration", 8);
if (isset($passwd_error) && $passwd_error)
	print "<center><b><span class=\"stop\">$passwd_error</span></b></center><br>";
?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
    <td><b>Administrator login for Oreon</b></td>
    <td align="right"><input type="text" name="oreonlogin" value="<? if (isset($_SESSION["oreonlogin"])) print $_SESSION["oreonlogin"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator password for Oreon</b></td>
    <td align="right"><input type="password" name="oreonpasswd" value="<? if (isset($_SESSION["oreonpasswd"])) print $_SESSION["oreonpasswd"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Confirm Password</b></td>
    <td align="right"><input type="password" name="oreonpasswd2" value="<? if (isset($_SESSION["oreonpasswd"])) print $_SESSION["oreonpasswd"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator firstname for Oreon</b></td>
    <td align="right"><input type="text" name="oreonfirstname" value="<? if (isset($_SESSION["oreonfirstname"])) print $_SESSION["oreonfirstname"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator lastname for Oreon</b></td>
    <td align="right"><input type="text" name="oreonlastname" value="<? if (isset($_SESSION["oreonlastname"])) print $_SESSION["oreonlastname"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator Email for Oreon</b></td>
    <td align="right"><input type="text" name="oreonemail" value="<? if (isset($_SESSION["oreonemail"])) print $_SESSION["oreonemail"]; ?>"></td>
  </tr>
  <tr>
    <td><b>Administrator language for Oreon</b></td>
    <td align="right"><select name="oreonlang">
			<?
			$chemintotal = "../lang/";
			if ($handle  = opendir($chemintotal))	{
				while ($file = readdir($handle))
					if	(!is_dir("$chemintotal/$file") && strcmp($file, "index.php") && strcmp($file, "index.html") && strcmp($file, "index.ihtml") ) {
						$tab = split('\.', $file);
						print "<option ";
						if (isset($_SESSION["oreonlang"]) && !strcmp($_SESSION["oreonlang"], $tab[0]))
							print "selected";
						print ">" . $tab[0] . "</option>";
					}
				closedir($handle);
			}
			?>
			</select>
	</td>
  </tr>
</table>
<?
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' />";
print $str;
aff_footer();
?>