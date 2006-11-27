<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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

	$date = date("d/m/Y");
	if (isset($msg_error))
		echo "<div style='padding-top: 10px;' class='text12bc'>$msg_error</div>";
	else if (isset($_POST["submit"]))
		echo "<div style='padding-top: 10px;' class='text12bc'>Invalid user</div>";
	?>  
	<form action="./index.php" method="post" name="login">
	<?
		if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2)
			print "<center><span class='msg'>Session Expired.</span></center>";
		if ($file_install_acces)
			print "<center><span class='msg'>$error_msg</span></center>";
	?>
<div id="LoginInvit">
	<table id="logintab1">
		<tr>
			<td class="LoginInvitLogo" colspan="2"><img src="img/LogoOreon.png" alt="Oreon logo" title="Oreon Logo"></td>
		</tr>
		<tr>
			<td class="LoginInvitVersion"><br><? include("include/version/version.php");  ?></td>
			<td class="LoginInvitDate"><br><? echo $date; ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<table id="logintab2">
					<tr>
						<td><label for="useralias">Login:</label></td>
						<td><input type="text" name="useralias" value="" class="inputclassic"></td>
					</tr>
					<tr>
						<td><label for="password">Password:</label></td>
						<td><input type="password" name="password" value="" class="inputclassic"></td>
					</tr>
					<tr>
						<td  colspan="2" id="sublogin">
							<input type="Submit" name="submit" value="Login" <? if ($file_install_acces) print "disabled"; ?> >
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td id="LoginInvitcpy" colspan="2">
				<a href="mailto:infos@oreon-project.org">Oreon</a> - <a href="http://www.nagios.org">Nagios</a> - &copy; 2004-2006 <br><a href="http://www.oreon-project.org" target="_blank">Oreon</a> All Rights Reserved.<br />
			</td>
		</tr>
	</table>
</form>
</div>


<?
?>
