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

/*if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["oreonlogin"] = $_POST["oreonlogin"];
	$_SESSION["oreonpasswd"] = $_POST["oreonpasswd"];
	$_SESSION["oreonfirstname"] = $_POST["oreonfirstname"];
	$_SESSION["oreonlastname"] = $_POST["oreonlastname"];
	$_SESSION["oreonemail"] = $_POST["oreonemail"];
	$_SESSION["oreonlang"] = $_POST["oreonlang"];
}*/

aff_header("Oreon Setup Wizard", "Post-Installation", 12);

?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
	<td colspan="2" ><b>Manual Configuration</b></td>
  </tr>
  <tr>
	<td colspan="2">To finish the installation of Oreon, you must still carry out some manuals actions :</td>
  </tr>
   <tr>
	<td colspan="2">&nbsp;</td>
  </tr>
  <tr>
    <td><img src='../img/icones/16x16/arrow_right_blue.png'></td>
    <td>Go into www/cron,  read ArchiveLogInDB_README.txt and deleteDB_README.txt </td>
  </tr>

<?
// end last code
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Finish' id='button_next' ";
$str .= " />";
print $str;
aff_footer();
?>