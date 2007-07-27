<?
/**
Oreon is developped with GPL Licence 2.0 :
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

/*if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["oreonlogin"] = $_POST["oreonlogin"];
	$_SESSION["oreonpasswd"] = $_POST["oreonpasswd"];
	$_SESSION["oreonfirstname"] = $_POST["oreonfirstname"];
	$_SESSION["oreonlastname"] = $_POST["oreonlastname"];
	$_SESSION["oreonemail"] = $_POST["oreonemail"];
	$_SESSION["oreonlang"] = $_POST["oreonlang"];
}*/

aff_header("Oreon Setup Wizard", "Post-Installation", 12);	?>

<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
	<td colspan="2" ><b>End of Setup</b></td>
  </tr>
  <tr>
	<td colspan="2"><br>
	Oreon Setup is finished. Now you can use your monitoring Solution.<br><br>Thanks for using Oreon
	<br><br>
	<b>Self service and commercial Support.</b><br><br>
	There are various way to get informations about Oreon ; the documentation, the wiki, forum and other stuffs.
	<ul>
		<li> Oreon WebSite : <a target="_blank" href="http://www.oreon-project.org">www.oreon-project.org</a></li>
		<li> Oreon Forum : <a target="_blank" href="http://forum.oreon-project.org">forum.oreon-project.org</a></li></li>
		<li> Oreon Wiki : <a target="_blank" href="http://wiki.oreon-project.org">wiki.oreon-project.org</a></li>
	</ul>
	<br><p align="justify">
	If your company needs professional consulting and services for Oreon, or if you need to purchase a support contract for it, don't hesitate to contact official </b><a  target="_blank" href="http://www.oreon-services.com">Oreon support center</a></b>.
	</p>
	</td>
  </tr>
   <tr>
	<td colspan="2">&nbsp;</td>
  </tr>
<?
// end last code
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Click here to complete your install' id='button_next' ";
$str .= " />";
print $str;
aff_footer();
?>