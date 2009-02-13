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

	include_once ("@CENTREON_ETC@/centreon.conf.php");
	include_once ("$centreon_path/www/class/centreonDB.php");

	$pearDB = new CentreonDB();

	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();

	$debug = 0;
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $conf_centreon["user"],
	    'password' => $conf_centreon["password"],
	    'hostspec' => $conf_centreon["hostCentreon"],
	    'database' => $conf_centreon["db"]);
	
	$options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
	
	global $pearDB0;
	
	$pearDBO =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDBO))
	    die($pearDBO->getMessage());
	    
	$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);
	// End of Pear connection

	if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")) {
		$_SESSION["mysqlscript"] = $_POST["mysqlscript"]; 
	}

	aff_header("Centreon Setup Wizard", "Updating Centreon Database", 6);	?>
	<br /><br />
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center"><?php
	print "<tr><th align='left'>Component</th><th style='text-align: right;'>Status</th></tr>";
	print "<tr><td><b>Database &#146;".$conf_centreon['db']."&#146; : Upgrade</b></td>";

	# get version...	
	preg_match("/Update-DB-".$version["value"]."_to_*.sql/", $_SESSION["mysqlscript"], $matches);
	if (count($matches))
		$choose_version = $matches[1];

	if ($pearDB) {
		$file_sql = file("./sql/".$_SESSION["mysqlscript"]);
        $str = NULL;
        for ($i = 0; $i <= count($file_sql) - 1; $i++){
            $line = $file_sql[$i];
            if ($line[0] != '#'){
                $pos = strrpos($line, ";");
                if ($pos != false) {
                    $str .= $line;
                    $str = chop($str);
                   $DBRESULT = $pearDB->query($str);
                    if (PEAR::isError($DBRESULT))
						print $mysql_msg = $DBRESULT->getDebugInfo();
                    $str = NULL;
                } else
                	$str .= $line;
            }
        }
		
		if (!isset($mysql_msg) || !$mysql_msg) {
 			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
			$return_false = 1;
			print "<tr><td colspan='2' align='left'><span class='small'>$mysql_msg</span></td></tr>";
		}
		
		if (isset($choose_version) && file_exists("./php/update-ods-$choose_version.php"))
			include("./php/update-ods-$choose_version.php");
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;	?>
		<tr>
			<td colspan="2" align="left"><span class="small"><?php echo $mysql_msg; ?></span></td>
		</tr><?php	
	}

	aff_middle();
	$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
	if (isset($return_false) && $return_false)
		$str .= " disabled";
	$str .= " />";
	print $str;
	aff_footer();
?>