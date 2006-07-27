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
		if (isset($return_false) && $return_false)
			$str .= " disabled";
		$str .= " />";
		print $str;
		aff_footer();

?>