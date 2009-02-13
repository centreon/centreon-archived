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

aff_header("Centreon Setup Wizard", "Creating Database", 11);
?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  	<tr>
    	<th align="left">Component</th>
   	 	<th style="text-align: right;">Status</th>
  	</tr>
  	<tr>
		<td><b>Database : Connection</b></td>	<?php
	$res = connexion('root', (isset($_SESSION["pwdroot"]) ? $_SESSION["pwdroot"] : '' ) , $_SESSION["dbLocation"]) ;
	$mysql_msg = $res['1'];
	if ($mysql_msg == '')
		print '<td align="right"><b><span class="go">OK</b></td></tr>';
	
	# Database creation 
	$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameOreonDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameOreonDB"] . ";";
		if ($DEBUG) 
			print $requete . "<br />";
		@mysql_query($requete, $res['0']);
		if ($res['1'])
			print '<td align="right"><b><span class="go">CRITICAL</b></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';	
	}
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameOdsDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameOdsDB"] . ";";
		if ($DEBUG) 
			print $requete . "<br />";
		@mysql_query($requete, $res['0']);
		if ($res['1'])
			print '<td align="right"><b><span class="go">CRITICAL</b></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';	
	}
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameStatusDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameStatusDB"] . ";";
		if ($DEBUG) 
			print $requete . "<br />";
		@mysql_query($requete, $res['0']);
		if ($res['1'])
			print '<td align="right"><b><span class="go">CRITICAL</b></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';	
	}
	
	# User management
	if (!$usedb){
		$mysql_msg = "";
		print "<tr><td><b>Database &#146;".$_SESSION["nameOreonDB"]."&#146; : Users Management</b></td>";
		/*
		 * Centreon
		 */
		$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameOreonDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
		if ($DEBUG) 
			print $requete. "<br />";
		mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
		$mysql_msg = $res['1'];

		/*
		 * Centstorage
		 */
		$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameOdsDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
		if ($DEBUG) 
			print $requete. "<br />";
		mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
		$mysql_msg .= $res['1'];		
		
		/*
		 * NDO
		 */
		$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameStatusDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
		if ($DEBUG) 
			print $requete. "<br />";
		mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
		$mysql_msg .= $res['1'];		
		
		if ($res['1'])
			print '<td align="right"><b><span class="go">CRITICAL</b><br />$mysql_msg<br /></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';	
	}	
	if ($_SESSION["mysqlVersion"] == "2")	{
        $requete = "UPDATE mysql.user SET Password = OLD_PASSWORD('". $_SESSION["pwdOreonDB"] ."') WHERE User = '". $_SESSION["nameOreonDB"] ."'";
        @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());
        $requete = "FLUSH PRIVILEGES";
        @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());
		$mysql_msg = $res['1'];
	}
	######################################################################################
	#  Centreon Database creation
	######################################################################################
	$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$usedb){
		$return_false = 0;
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$mysql_msg = $res['1'];
		if ($mysql_msg == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    	$return_false = 1;
			print '<tr><td colspan="2" align="left"><span class="small">'.$mysql_msg.'</span></td></tr>';
		}
	} 
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Schema Creation</b></td>';
		$mysql_msg = '';
		$file_sql = file("./createTables.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	
	$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$usedb){	
		$return_false = 0;
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOdsDB"].'&#146; : Schema Creation</b></td>';
		$mysql_msg = $res['1'];
		if ($mysql_msg == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    	$return_false = 1;
			print '<tr><td colspan="2" align="left"><span class="small">'.$mysql_msg.'</span></td></tr>';
		}
	} 
	$usedb = mysql_select_db($_SESSION["nameOdsDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOdsDB"].'&#146; : Schema Creation</b></td>';
		$mysql_msg = '';
		$file_sql = file("./createTablesCentstorage.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	$usedb = mysql_select_db($_SESSION["nameStatusDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameStatusDB"].'&#146; : Schema Creation</b></td>';
		$mysql_msg = '';
		$file_sql = file("./createNDODB.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$usedb){	
		$return_false = 0;
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$mysql_msg = $res['1'];
		if ($mysql_msg == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    	$return_false = 1;
			print '<tr><td colspan="2" align="left"><span class="small">'.$mysql_msg.'</span></td></tr>';
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Macros Creation</b></td>';
		$mysql_msg = '';
		$file_sql = file("./insertMacros.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Insert Commands</b></td>';
		$mysql_msg = '';
		$file_sql = file("./insertCmd-Tps.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Topology Insertion</b></td>';
		$mysql_msg = '';
		$file_sql = file("./insertTopology.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Insert Basic Configuration</b></td>';
		$mysql_msg = '';
		$file_sql = file("./insertBaseConf.sql");
	    $str = NULL;
	    for ($i = 0; $i <= count($file_sql) - 1; $i++){
	        $line = $file_sql[$i];
	        if (($line[0] != '#' ) and ( $line[0] != '-' )  )    {
	            $pos = strrpos($line, ";");
	            if ($pos != false)      {
	                $str .= $line;
	                $str = chop ($str);
	  				if ($DEBUG) print $str . "<br />";
	                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br /><span class='warning'>->" . mysql_error() ."</span><br />");
	                $str = NULL;
	            } else
	            	$str .= $line;
	        }
	    }	
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Centreon User Creation</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$req = "SELECT * FROM `contact` WHERE contact_alias = '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES)."' ";
		$r  = @mysql_query($req, $res['0']);
		$nb = @mysql_num_rows($r);
		while ($tab = @mysql_fetch_array($r))
			break;
		if (!$tab && !$nb){
			$requete = "INSERT INTO `contact` (`contact_name` , `contact_alias` , `contact_passwd` , `contact_lang` , `contact_email` , `contact_oreon` , `contact_admin` , `contact_activate` ) VALUES ";
			$requete .= "('".htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES). " " .htmlentities($_SESSION["oreonlastname"], ENT_QUOTES)."', '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES)."', '". md5($_SESSION["oreonpasswd"]) ."', 'en_US', '".$_SESSION['oreonemail']."', '1', '1', '1');";
			if ($DEBUG) 
				print $requete . "<br />";
			$result = @mysql_query($requete, $res['0']);
			htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES);
		} else {
			$requete = "UPDATE `contact` SET `contact_name` = '". htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES)." ". htmlentities($_SESSION["oreonlastname"], ENT_QUOTES)  ."',`contact_passwd` = '". md5($_SESSION["oreonpasswd"]) ."', `contact_email` = 'nagios@localhost', `contact_lang` = 'en_US' WHERE `contact_alias` = '".htmlentities($_SESSION["oreonlogin"], ENT_QUOTES)."' LIMIT 1 ;";
			if ($DEBUG) 
				print $requete . "<br />";
			$result = @mysql_query($requete, $res['0']);
		} 
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set NDO Password</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$requete = "UPDATE `cfg_ndo2db` SET `db_pass` = '".$_SESSION["pwdOreonDB"]."';";
		if ($DEBUG) 
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set Nagios Version</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$requete = "INSERT INTO `options` (`key`, `value`) SET ('nagios_version', '".$_SESSION["nagios_version"]."');";
		if ($DEBUG) 
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set Ndo connexion properties</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
		$requete = "UPDATE `cfg_ndo2db` SET `db_name` = '".$_SESSION["nameStatusDB"]."', `db_user` = '".$_SESSION["nameOreonDB"]."', `db_pass` = '".$_SESSION["pwdOreonDB"]."';";
		if ($DEBUG) 
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if ($res[1] == '') {
			echo '<td align="right"><b><span class="go">OK</b></td></tr>';
		} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b><br />'.$res[1].'<br /></td></tr>';
		    $return_false = 1;
		}
	}
	aff_middle();
	$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
	if ($return_false)
		$str .= " disabled";
	$str .= " />";
	print $str;
	aff_footer();
?>