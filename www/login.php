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
 
	/*
	 * Init Date
	 */
	$date = date("d/m/Y");

	if (isset($msg_error))
		echo "<div style='padding-top: 60px;'>$msg_error</span></div>";
	else if (isset($_POST["submit"]))
		echo "<div style='padding-top: 60px;'><span class='msg'>Invalid user</span></div>";
	?>
<form action="./index.php" method="post" name="login">
<?php
	if (isset($_GET["disconnect"]) && $_GET["disconnect"] == 2)
		print "<div style='padding-top: 60px;'><span class='msg'>Session Expired.</span></div>";
	if ($file_install_acces)
		print "<div style='padding-top: 60px;'><span class='msg'>$error_msg</span></div>";
	if (isset($msg) && $msg)
		print "<div style='padding-top: 60px;'><span class='msg'>$msg</span></div>";
?>
<div id="LoginInvit">
	<table id="logintab1">
		<tr>
			<td class="LoginInvitLogo" colspan="2"><img src="img/logo_centreon_wt.gif" alt="Centreon logo" title="Centreon Logo"></td>
		</tr>
		<tr>
			<td class="LoginInvitVersion"><br />
			<?php
			$DBRESULT =& $pearDB->query("SELECT oi.value FROM informations oi WHERE oi.key = 'version' LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$release = $DBRESULT->fetchRow();
			print $release["value"];
			?>
			</td>
			<td class="LoginInvitDate"><br /><?php echo $date; ?></td>
		</tr>
		<tr>
			<td colspan="2">
				<table id="logintab2">
					<tr>
						<td><label for="useralias">Login:</label></td>
						<td><input type="text" name="useralias" value="" class="inputclassic" <?php if (isset($freeze) && $freeze) print "disabled='disabled'"; ?>></td>
					</tr>
					<tr>
						<td><label for="password">Password:</label></td>
						<td><input type="password" name="password" value="" class="inputclassic" <?php if (isset($freeze) && $freeze) print "disabled='disabled'"; ?>></td>
					</tr>
					<tr>
						<td  colspan="2" id="sublogin">
							<input type="Submit" name="submit" value="Login" <?php if ($file_install_acces) print "disabled"; ?> >
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td id="LoginInvitcpy" colspan="2">
				<br />
				&copy; 2004-2008 <a href="mailto:infos@centreon.com">Centreon</a>
			</td>
		</tr>
	</table>
</form>
</div>