<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	include_once ("@CENTREON_ETC@/centreon.conf.php");
	include_once ("$centreon_path/www/class/centreonDB.php");

	$pearDB = new CentreonDB();

	$DBRESULT =& $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version =& $DBRESULT->fetchRow();
	global $pearDBO;
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")) {
		$_SESSION["mysqlscript"] = $_POST["mysqlscript"]; 
	}

	aff_header("Centreon Setup Wizard", "Updating NDOutils Database", 4);	?>
	<br /><br />
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center"><?php
	print "<tr><th align='left'>Component</th><th style='text-align: right;'>Status</th></tr>";
	print "<tr><td><b>Database &#146;".$conf_centreon['dbcstg']."&#146; : Upgrade</b></td>";

	# get version...	
	preg_match("/Update-NDO-".$version["value"]."_to_*.sql/", $_SESSION["mysqlscript"], $matches);
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
                   $DBRESULT = $pearDBO->query($str);                    
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
		
		if (isset($choose_version) && file_exists("./php/update-$choose_version.php"))
			include("./php/update-$choose_version.php");
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