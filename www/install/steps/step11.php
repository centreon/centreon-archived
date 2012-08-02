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
	if ($mysql_msg == '') {
		print '<td align="right"><b><span class="go">OK</b></td></tr>';
	}
	
	# Database creation
	$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameOreonDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameOreonDB"] . ";";
		if ($DEBUG)
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if (!$result)
			print '<td align="right"><b><span class="go">CRITICAL</b></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';
	}
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameOdsDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameOdsDB"] . ";";
		if ($DEBUG)
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if (!$result)
			print '<td align="right"><b><span class="go">CRITICAL</b></td></tr>';
		else
			print '<td align="right"><b><span class="go">OK</b></td></tr>';
	}
	if (!$usedb){
		print "<tr><td><b>Database &#146;".$_SESSION["nameStatusDB"]."&#146; : Creation</b></td>";
		$requete = "CREATE DATABASE ". $_SESSION["nameStatusDB"] . ";";
		if ($DEBUG)
			print $requete . "<br />";
		$result = @mysql_query($requete, $res['0']);
		if (!$result)
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
		if ($DEBUG) {
			print $requete. "<br />";
		}
		@mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
		$mysql_msg = $res['1'];

		/*
		 * Centstorage
		 */
		$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameOdsDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
		if ($DEBUG) {
			print $requete. "<br />";
		}
		@mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
		$mysql_msg .= $res['1'];

		/*
		 * NDO
		 */
		$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameStatusDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
		if ($DEBUG)
			print $requete. "<br />";
		@mysql_query($requete, $res['0']) or ( $mysql_msg = mysql_error());
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
		$res = @mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
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
	if (!$return_false){
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOdsDB"].'&#146; : Broker Init</b></td>';
		$mysql_msg = '';
		$file_sql = file("./installBroker.sql");
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
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Insert ACL Configuration</b></td>';
		$mysql_msg = '';
		$file_sql = file("./insertACL.sql");
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
		$req = "SELECT * FROM `contact` WHERE contact_alias = '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES, "UTF-8")."' ";
		$r  = @mysql_query($req, $res['0']);
		$nb = @mysql_num_rows($r);
		while ($tab = @mysql_fetch_array($r))
			break;
        
		/*if (!$tab && !$nb)
        {
			$requete = "INSERT INTO `contact` (`contact_name` , `contact_alias` , `contact_passwd` , `contact_lang` , `contact_email` , `contact_oreon` , `contact_admin` , `contact_activate` ) VALUES ";
			$requete .= "('".htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES, "UTF-8"). " " .htmlentities($_SESSION["oreonlastname"], ENT_QUOTES, "UTF-8")."', '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES, "UTF-8")."', '". md5($_SESSION["oreonpasswd"]) ."', 'en_US', '". htmlentities($_SESSION['oreonemail'], ENT_QUOTES, "UTF-8")."', '1', '1', '1');";
			if ($DEBUG)
				print $requete . "<br />";
			$result = @mysql_query($requete, $res['0']);
			htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES, "UTF-8");
		}
        else
        {*/
			$requete = "UPDATE `contact` SET `contact_name` = '". htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES, "UTF-8")." ". htmlentities($_SESSION["oreonlastname"], ENT_QUOTES, "UTF-8")  ."',`contact_passwd` = '". md5($_SESSION["oreonpasswd"]) ."', `contact_email` = '". htmlentities($_SESSION['oreonemail'], ENT_QUOTES, "UTF-8")."', `contact_lang` = 'en_US' WHERE `contact_alias` = 'admin' ;";
			if ($DEBUG)
				print $requete . "<br />";
			$result = @mysql_query($requete, $res['0']);
		//}
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
		$requete = "UPDATE `cfg_ndo2db` SET `db_name` = '".$_SESSION["nameStatusDB"]."', `db_user` = '".$_SESSION["nameOreonDB"]."', `db_pass` = '".$_SESSION["pwdOreonDB"]."', `db_host` = '".$_SESSION["dbLocation"]."';";
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
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set Broker Database host</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$requete = "UPDATE `cfg_centreonbroker_info` SET `config_value` = '".$_SESSION["dbLocation"]."' WHERE config_key = 'db_host' AND config_group_id = 'output';";
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
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set Broker DB password</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$requete = "UPDATE `cfg_centreonbroker_info` SET `config_value` = '".$_SESSION["pwdOreonDB"]."' WHERE config_key = 'db_password' AND config_group_id = 'output';";
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
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set Broker DB name</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
		$requete = "UPDATE `cfg_centreonbroker_info` SET `config_value` = '".$_SESSION["nameOdsDB"]."' WHERE config_key = 'db_name' AND config_group_id = 'output';";
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
		print '<tr><td><b>Database &#146;'.$_SESSION["nameOreonDB"].'&#146; : Set RRDTool properties</b></td>';
		$mysql_msg = '';
		$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
		@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());
		$requete = "UPDATE `options` SET `value` = '".htmlentities($_SESSION["rrdtool_dir"], ENT_QUOTES, "UTF-8")."' WHERE `key` = 'rrdtool_path_bin'";
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