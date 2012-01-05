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

	include_once $centreon_path . "/www/class/centreonDB.class.php";

	$pearDB = new CentreonDB();

	$DBRESULT = $pearDB->query("SELECT `value` FROM `informations` WHERE `key` = 'version'");
	$version = $DBRESULT->fetchRow();

	global $pearDB0;
	$pearDBO = new CentreonDB("centstorage");

	if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back"))
		$_SESSION["script"] = $_POST["script"];

	aff_header("Centreon Setup Wizard", "Updating Centreon", 5);	?>
	<br /><br />
	<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center"><?php
	print "<tr><th align='left'>Component</th><th style='text-align: right;'>Status</th></tr>";

	/*
	 * Update Centstorage
	 */
	print "<tr><td><b>Database &#146;".$conf_centreon['dbcstg']."&#146; : Upgrade</b></td>";
	if (file_exists("./sql/centstorage/Update-CSTG-".$_SESSION["script"].".sql")) {
		$file_sql = file("./sql/centstorage/Update-CSTG-".$_SESSION["script"].".sql");
        $request = "";
        if (count($file_sql)) {
	       	$str = "";
	        foreach ($file_sql as $line) {
	        	if ($line[0] != "#" && $line[0] != "-") {
	        		$pos = strrpos($line, ";");
                	if ($pos != false) {
                    	$str .= $line;
                    	$str = rtrim($str);

                   		$DBRES = $pearDBO->query($str);
                    	$str = NULL;
               		} else
                		$str .= $line;
	        	}
	        }
			if (isset($DBRES) && !PEAR::isError($DBRES))
				echo '<td align="right"><b><span class="go">OK</b></td></tr>';
    		else
    			echo '<td align="right"><b><span class="critical">CRITICAL</span></b></td></tr>';
        } else {
        	echo '<td align="right"><b><span class="go">OK</b></td></tr>';
        }
	} else {
		echo '<td align="right"><b><span class="warning">PASS</span></b></td></tr>';
	}

	/*
	 * Update NDO
	 */
	$DBRESULT = $pearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
    $isNdo = $DBRESULT->numRows();
	$confNDO = $DBRESULT->fetchRow();
	unset($DBRESULT);

	print "<tr><td><b>Database &#146;".$confNDO['db_name']."&#146; : Upgrade</b></td>";
	if (file_exists("./sql/brocker/Update-NDO-".$_SESSION["script"].".sql")) {
		$file_sql = file("./sql/brocker/Update-NDO-".$_SESSION["script"].".sql");
        $request = "";
        if (count($file_sql) && $isNdo) {
	        $pearDBndo 	= new CentreonDB("ndo");
            $str = "";
	        foreach ($file_sql as $line)
	        	if ($line[0] != "#" && $line[0] != "-") {
	        		$pos = strrpos($line, ";");
                	if ($pos != false) {
                    	$str .= $line;
                    	$str = rtrim($str);
                   	$DBRES = $pearDBndo->query($str);
                    $str = NULL;
                	} else
                		$str .= $line;
	        	}
			if (isset($DBRES) && !PEAR::isError($DBRES))
				echo '<td align="right"><b><span class="go">OK</b></td></tr>';
    		else
    			echo '<td align="right"><b><span class="critical">CRITICAL</span></b></td></tr>';
        } else {
        	echo '<td align="right"><b><span class="go">OK</b></td></tr>';
        }
	} else {
		echo '<td align="right"><b><span class="warning">PASS</span></b></td></tr>';
	}

	/*
	 * Update PHP
	 */
	print "<tr><td><b>PHP Script : Upgrade</b></td>";
	if (file_exists("./php/Update-".$_SESSION["script"].".php")) {
		if (include_once("./php/Update-".$_SESSION["script"].".php"))
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		else
			echo '<td align="right"><b><span class="critical">CRITICAL</span></b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="go">OK</span></b></td></tr>';
	}

	/*
	 * Update Centreon
	 */
	print "<tr><td><b>Database &#146;".$conf_centreon['db']."&#146; : Upgrade</b></td>";
	if (file_exists("./sql/centreon/Update-DB-".$_SESSION["script"].".sql")) {
		$file_sql = file("./sql/centreon/Update-DB-".$_SESSION["script"].".sql");
        $request = "";
        if (count($file_sql)) {
	        $str = "";
	        foreach ($file_sql as $line) {
	        	if ($line[0] != "#" && $line[0] != "-") {
	        		$pos = strrpos($line, ";");
                	if ($pos != false) {
                    	$str .= $line;
                    	$str = rtrim($str);
 	                  	$DBRES = $pearDB->query($str);
    	                $str = NULL;
                	} else
                		$str .= $line;
	        	}
	        }
			if (isset($DBRES) && !PEAR::isError($DBRES))
				echo '<td align="right"><b><span class="go">OK</b></td></tr>';
    		else
    			echo '<td align="right"><b><span class="critical">CRITICAL</span></b></td></tr>';
        } else {
        	echo '<td align="right"><b><span class="go">OK</b></td></tr>';
        }
	} else {
		echo '<td align="right"><b><span class="go">PASS</span></b></td></tr>';
	}

	/*
	 * Post Update in PHP
	 */
	print "<tr><td><b>PHP Script : Post Upgrade</b></td>";
	if (file_exists("./php/Update-".$_SESSION["script"].".post.php")) {
		if (include_once("./php/Update-".$_SESSION["script"].".post.php"))
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		else
			echo '<td align="right"><b><span class="critical">CRITICAL</span></b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="go">OK</span></b></td></tr>';
	}

	aff_middle();
	$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
	if (isset($return_false) && $return_false)
		$str .= " disabled";
	$str .= " />";
	print $str;
	aff_footer();
?>