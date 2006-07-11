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
	// configuration
	include_once ("../oreon.conf.php");
	include_once ("../functions.php");
	include_once ("../class/Session.class.php");

	Session::start();
	$DEBUG = 0;


	if (isset($_POST["Recheck"]))
		 $_POST["step"] = 3;
	if (isset($_POST["goto"]) && !strcmp($_POST["goto"], "Back"))
		 $_POST["step"] -= 2;
	if (isset($_POST["step"]) && isset($_POST["pwdOreonDB"])&& $_POST["step"] == 5 && strcmp($_POST["pwdOreonDB"], $_POST["pwdOreonDB2"])){
		$_POST["step"] = 4;
		$passwd_error = "Password not confimed correctly.";
	}
	if (isset($_POST["step"]) && $_POST["step"] == 6 && strcmp($_POST["oreonpasswd"], $_POST["oreonpasswd2"])){
		$_POST["step"] = 5;
		$passwd_error = "Password not confimed correctly.";
	}
	if (!isset($_POST["step"])){
		aff_header("Oreon Upgrade Wizard", "Welcome to Oreon Upgrade Setup", 1);
		print "<p>This installer updates the Oreon database tables. The entire process should take about 2 minutes.</p>";
		aff_middle();
		print "<input class='button' type='submit' name='goto' value='Start' id='defaultFocus' /></td>";
		aff_footer();
	} else if (isset($_POST["step"]) && $_POST["step"] == 1){
		aff_header("Oreon Upgrade Wizard", "Licence", 2);
		$license_file_name = "./LICENSE.txt";
		$fh = fopen( $license_file_name, 'r' ) or die( "License file not found!" );
		$license_file = fread( $fh, filesize( $license_file_name ) );
		fclose( $fh );
		$str = "<textarea cols='80' rows='20' readonly>".$license_file."</textarea>";
		$str .= "</td>
		</tr>
		<tr>
		  <td align=left>
			<input type='checkbox' class='checkbox' name='setup_license_accept' onClick='LicenceAccepted();' value='0' /><a href='javascript:void(0)' onClick='document.getElementById('button_next').disabled = false;'>I Accept</a>
		  </td>
		  <td align=right>
			&nbsp;
		  </td>
		</tr>";
		print $str;
		aff_middle();
		$str = "<input class='button' type='submit' name='goto' value='Next' id='button_next' disabled='disabled' />";
		print $str;
		aff_footer();
	} else if (isset($_POST["step"]) && $_POST["step"] == 2){
		aff_header("Oreon Upgrade Wizard", "Select Version", 3); ?>
					In order for your Oreon upgrade to function properly, please select the mysql script file.<br><br>
		<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
          <tr>
            <th style="padding-left:20px " colspan="2">Upgrade SQL Scripts</th>
          </tr>
		  <tr>
            <td><b>MySQL Scripts</b></td>
            <td align="right">
            	<select name="mysqlscript">
            	<?
            		chdir('sql');
            		foreach (glob("*.sql") as $filename) {
   					echo '<option value="'.$filename.'">'.$filename.'</option>'; }
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
	} else if (isset($_POST["step"]) && $_POST["step"] == 3){
			if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")) {
			$_SESSION["mysqlscript"] = $_POST["mysqlscript"]; }

		aff_header("Oreon Setup Wizard", "Creating Database", 4);
		?>
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
          <tr>
            <th align="left">Component</th>
            <th style="text-align: right;">Status</th>
          </tr>
			<tr>
				<td><b>Database &#146;<? echo $conf_oreon['db'] ; ?>&#146; : Upgrade</b></td>
<?
			$res = connexion($conf_oreon['user'], $conf_oreon['password']  , $conf_oreon['host']) ;
			$mysql_msg = $res['1'];
			$usedb = mysql_select_db($conf_oreon['db'] , $res['0']) or ( $mysql_msg= mysql_error());

			if ($usedb) {
				$file_sql = file("./sql/".$_SESSION["mysqlscript"]);
	            $str = NULL;
	            for ($i = 0; $i <= count($file_sql) - 1; $i++){
		            $line = $file_sql[$i];
		            if ($line[0] != '#')    {
		                $pos = strrpos($line, ";");
		                if ($pos != false)      {
		                    $str .= $line;
		                    $str = chop ($str);
		                    $result = mysql_query($str, $res['0']) or ( $mysql_msg = $mysql_msg ."<br>" . mysql_error());
		                    $str = NULL;
		                }
		                else
		                	$str .= $line;
		            }
	            }
			@mysql_close($res['0']);

			if (!$mysql_msg) {
     			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
			} else {
				echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
			    $return_false = 1;
			?>
			<tr>
		    	<td>&nbsp;</td>
	            <td align="right"><? echo $mysql_msg; ?></td>
			</tr>

			<? }

			} else {
				echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
			    $return_false = 1;
			?>
			<tr>
		    	<td>&nbsp;</td>
	            <td align="right"><? echo $mysql_msg ; ?></td>
	            <? 	$return_false = 1; ?>
			</tr>

		<?	}
			@mysql_close($res['0']);
		// end last code
		aff_middle();
		$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
		if ($return_false)
			$str .= " disabled";
		$str .= " />";
		print $str;
		aff_footer();
	} else if (isset($_POST["step"]) && $_POST["step"] == 4){
		session_destroy();
		$tmpfname = tempnam("", "");
		@unlink($tmpfname);
		@rename(getcwd(), realpath("..")."/".basename($tmpfname ));
		header("Location: ../index.php");
	}
	exit();
?>
