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

	$date = date("d/m/Y");

	if (isset($msg_error))
		echo "<div style='padding-top: 60px;'>$msg_error</span></div>";
	else if (isset($_POST["submit"]))
		echo "<div style='padding-top: 60px;'><span class='msg'>Invalid user</span></div>";
	?>  
<form action="./index.php" method="post" name="login">
<?
	if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2)
		print "<div style='padding-top: 60px;'><span class='msg'>Session Expired.</span></div>";
	if ($file_install_acces)
		print "<div style='padding-top: 60px;'><span class='msg'>$error_msg</span></div>";
?>
<div id="LoginInvit">
	<table id="logintab1">
		<tr>
			<td class="LoginInvitLogo" colspan="2"><img src="img/logo_oreon.gif" alt="Oreon logo" title="Oreon Logo"></td>
		</tr>
		<tr>
			<td class="LoginInvitVersion"><br>
			<?
			$DBRESULT =& $pearDB->query("SELECT oi.value FROM oreon_informations oi WHERE oi.key = 'version' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$release = $DBRESULT->fetchRow();
			print $release["value"];
			?>
			</td>
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
				<br>
				&copy; 2004-2007 <a href="mailto:infos@oreon-project.org">Oreon</a>
			</td>
		</tr>
	</table>
</form>
</div>