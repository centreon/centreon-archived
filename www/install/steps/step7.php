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
	if (isset($_POST["nameStatusDB"])) $_SESSION["nameStatusDB"] = $_POST["nameStatusDB"];
	if (isset($_POST["nagiosVersion"])) $_SESSION["nagiosVersion"] = $_POST["nagiosVersion"];
	if (isset($_POST["mysqlVersion"])) $_SESSION["mysqlVersion"] = $_POST["mysqlVersion"];
}
aff_header("Centreon Setup Wizard", "DataBase Verification", 7);

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
		if ($DEBUG) print $requete . "<br />";
		$result = mysql_query($requete, $res['0']);
		$row = mysql_fetch_assoc($result);
		if (preg_match("/^(4\.1|5\.)/", $row['mysql_version'])){
			echo '<td align="right"><b><span class="go">OK ('.$row['mysql_version'].')</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL ('.$row['mysql_version'].')</b></td></tr>';
			$mysql_msg = "MySQL 4.1 or newer needed";
			$return_false = 1;
		}
	} else {
  		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
		$return_false = 1;
	} ?>
	<tr>
    	<td colspan="2" align="right"><?php echo $mysql_msg; ?></td>
	</tr>
  <tr>
    <td><b>MySQL InnoDB Engine status</b></td>
  <?php
	$res = connexion('root', (isset($_SESSION["pwdroot"]) ? $_SESSION["pwdroot"] : '' ) , $_SESSION["dbLocation"]) ;
	$mysql_msg = $res['1'];

	if ($mysql_msg == '') {
		$requete = "show variables where Variable_name LIKE 'have_innodb';";
		if ($DEBUG) print $requete . "<br />";
		$result = mysql_query($requete, $res['0']);
		$row = mysql_fetch_assoc($result);
		if ($row['Value'] == "YES") {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</b></td></tr>';
			$mysql_msg = "MySQL InnoDB Engine Must be enable";
			$return_false = 1;
		}
	} else {
  		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
		$return_false = 1;
	} ?>
	<tr>
    	<td colspan="2" align="right"><?php echo $mysql_msg; ?></td>
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