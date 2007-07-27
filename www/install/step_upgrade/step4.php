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

	if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")) {
		$_SESSION["mysqlscript"] = $_POST["mysqlscript"]; }
	aff_header("Oreon Setup Wizard", "Updating Database", 4);	?>
	
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center"><?
	print "<tr><th align='left'>Component</th><th style='text-align: right;'>Status</th></tr>";
	print "<tr><td><b>Database &#146;".$conf_oreon['db']."&#146; : Upgrade</b></td>";
	$res = connexion($conf_oreon['user'], $conf_oreon['password']  , $conf_oreon['host']) ;
	$mysql_msg = $res['1'];
	$usedb = mysql_select_db($conf_oreon['db'] , $res['0']) or ( $mysql_msg= mysql_error());

	# get version...
	
	preg_match("/UpdateDB-([a-zA-z0-9\-\.]*).sql/", $_SESSION["mysqlscript"], $matches);
	$choose_version = $matches[1];

	if ($usedb) {
		$file_sql = file("./sql/".$_SESSION["mysqlscript"]);
        $str = NULL;
        for ($i = 0; $i <= count($file_sql) - 1; $i++){
            $line = $file_sql[$i];
            if ($line[0] != '#'){
                $pos = strrpos($line, ";");
                if ($pos != false) {
                    $str .= $line;
                    $str = chop ($str);
                    $result = mysql_query($str, $res['0']) or ( $mysql_msg = $mysql_msg ."<br>" . mysql_error());
                    $str = NULL;
                } else
                	$str .= $line;
            }
        }
		
		if (!$mysql_msg) {
 			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
		    $return_false = 1;
			print "<tr><td colspan='2' align='left'><span class='small'>$mysql_msg</span></td></tr>";
		}
		
		if (file_exists("./php/update-$choose_version.php"))
			include("./php/update-$choose_version.php");
		@mysql_close($res['0']);
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
		<tr>
			<td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
		</tr><?	
	}
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