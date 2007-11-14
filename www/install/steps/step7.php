<?php
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

if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	if (isset($_POST["nagiosLocation"]) && strcmp($_POST["nagiosLocation"], ""))
		$_SESSION["nagiosLocation"] = $_POST["nagiosLocation"];
	else
		$_SESSION["nagiosLocation"] = "localhost";
	if (isset($_POST["dbLocation"]) && strcmp($_POST["dbLocation"], ""))
		$_SESSION["dbLocation"] = $_POST["dbLocation"];
	else
		$_SESSION["dbLocation"] = "localhost";
	if (isset($_POST["pwdOreonDB"])) $_SESSION["pwdOreonDB"] = $_POST["pwdOreonDB"];
	if (isset($_POST["pwdOreonDB2"])) $_SESSION["pwdOreonDB2"] = $_POST["pwdOreonDB2"];
	if (isset($_POST["pwdroot"])) $_SESSION["pwdroot"] = $_POST["pwdroot"];
	if (isset($_POST["nameOreonDB"])) $_SESSION["nameOreonDB"] = $_POST["nameOreonDB"];
	if (isset($_POST["nameOdsDB"])) $_SESSION["nameOdsDB"] = $_POST["nameOdsDB"];
	if (isset($_POST["nagiosVersion"])) $_SESSION["nagiosVersion"] = $_POST["nagiosVersion"];
	if (isset($_POST["mysqlVersion"])) $_SESSION["mysqlVersion"] = $_POST["mysqlVersion"];
}
aff_header("Oreon Setup Wizard", "DataBase Verification", 7);

?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
   <tr>
    <td><b>MySQL version</b></td>
  <?php
	$res = connexion('root', (isset($_SESSION["pwdroot"]) ? $_SESSION["pwdroot"] : '' ) , $_SESSION["dbLocation"]) ;
	$mysql_msg = $res['1'];

	if ($mysql_msg == '') {

		$requete = "SELECT VERSION() AS mysql_version;";
		if ($DEBUG) print $requete . "<br>";
		$result = mysql_query($requete, $res['0']);
		$row = mysql_fetch_assoc($result);
		if(preg_match("/^(4\.1|5\.)/", $row['mysql_version'])){
			echo '<td align="right"><b><span class="go">OK ('.$row['mysql_version'].')</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL ('.$row['mysql_version'].')</b></td></tr>';
			$mysql_msg = "MySQL 4.1 or newer needed";
			$return_false = 1;
		}
	?>
  <?php} else {
  		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
		$return_false = 1;
	 } ?>
	<tr>
    	<td colspan="2" align="right"><?phpecho $mysql_msg; ?></td>
	</tr>
</table>
<?php
aff_middle();
$str ='';
if ($return_false)
	$str .= "<input class='button' type='submit' name='Recheck' value='Recheck' />";
$str .= "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
if ($return_false)
	$str .= " disabled";
$str .= " />";
		print $str;
		aff_footer();
?>