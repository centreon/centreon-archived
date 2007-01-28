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

/*if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["oreonlogin"] = $_POST["oreonlogin"];
	$_SESSION["oreonpasswd"] = $_POST["oreonpasswd"];
	$_SESSION["oreonfirstname"] = $_POST["oreonfirstname"];
	$_SESSION["oreonlastname"] = $_POST["oreonlastname"];
	$_SESSION["oreonemail"] = $_POST["oreonemail"];
	$_SESSION["oreonlang"] = $_POST["oreonlang"];
}*/

aff_header("Oreon Setup Wizard", "Creating Database", 11);

?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
		<td><b>Database : Connection</b></td>
  <?
	$res = connexion('root', (isset($_SESSION["pwdroot"]) ? $_SESSION["pwdroot"] : '' ) , $_SESSION["dbLocation"]) ;
	$mysql_msg = $res['1'];

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';

	?>

	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"] ; ?>&#146; : Creation</b></td>
 <?

	//	$requete =  "USE ". $_SESSION["nameOreonDB"] . ";";
	//	if ($DEBUG) print $requete . "<br>";
	//	$usedb = mysql_query($requete, $res['0'])  or ( $mysql_msg= mysql_error());
		$usedb = mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg = mysql_error());

	if (!$usedb)  {
		$requete = "CREATE DATABASE ". $_SESSION["nameOreonDB"] . ";";
		if ($DEBUG) print $requete . "<br>";
		@mysql_query($requete, $res['0']);
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"] ; ?>&#146; : Users Management</b></td>
<?
	// http://dev.mysql.com/doc/refman/5.0/en/old-client.html

	$mysql_msg = '';
	$requete = "GRANT ALL PRIVILEGES ON `". $_SESSION["nameOreonDB"] . "` . * TO `". $_SESSION["nameOreonDB"] . "`@`". $_SESSION["nagiosLocation"] . "` IDENTIFIED BY '". $_SESSION["pwdOreonDB"] . "' WITH GRANT OPTION";
	if ($DEBUG) print $requete. "<br>";
	mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());
	$mysql_msg = $res['1'];
	if ($_SESSION["mysqlVersion"] == "2")	{
        $requete = "UPDATE mysql.user SET Password = OLD_PASSWORD('". $_SESSION["pwdOreonDB"] ."') WHERE User = '". $_SESSION["nameOreonDB"] ."'";
        @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());
        $requete = "FLUSH PRIVILEGES";
        @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());
		$mysql_msg = $res['1'];
	}
	@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
	$mysql_msg = $res['1'];

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>

<?	} ?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Schema Creation</b></td>
<?
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
  				if ($DEBUG) print $str . "<br>";
                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br><span class='warning'>->" . mysql_error() ."</span><br>");
                $str = NULL;
            }
            else
            	$str .= $line;
        }
    }

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>
	<?	} ?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Command and Timeperiod Creation</b></td>
<?
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
  				if ($DEBUG) print $str . "<br>";
                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br><span class='warning'>->" . mysql_error() ."</span><br>");
                $str = NULL;
            }
            else
            	$str .= $line;
        }
    }

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>
<?	} ?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Macros Creation</b></td>
<?
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
  				if ($DEBUG) print $str . "<br>";
                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br><span class='warning'>->" . mysql_error() ."</span><br>");
                $str = NULL;
            }
            else
            	$str .= $line;
        }
    }

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>
	<?	} ?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Basic Config Insertion</b></td>
<?
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
  				if ($DEBUG) print $str . "<br>";
                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br><span class='warning'>->" . mysql_error() ."</span><br>");
                $str = NULL;
            }
            else
            	$str .= $line;
        }
    }


	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>
<?	} ?>
<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Topology Insertion</b></td>
<?
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
  				if ($DEBUG) print $str . "<br>";
                $result = @mysql_query($str, $res['0']) or ( $mysql_msg= $mysql_msg . "$str<br><span class='warning'>->" . mysql_error() ."</span><br>");
                $str = NULL;
            }
            else
            	$str .= $line;
        }
    }

	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>
<?	} ?>
	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Oreon User Creation</b></td>
	<?
	$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
	@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());
	$req = "SELECT * FROM `contact` WHERE contact_alias = '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES)."' ";
	$r = @mysql_query($req, $res['0']);
//	if (!$r)
//		@print mysql_error($res['0']);
	$nb = @mysql_num_rows($r);
	while ($tab = @mysql_fetch_array($r))
		break;
	if (!$tab && !$nb){
		$requete = "INSERT INTO `contact` (`contact_name` , `contact_alias` , `contact_passwd` , `contact_lang` , `contact_email` , `contact_oreon` , `contact_admin` , `contact_activate` ) VALUES ";
		$requete .= "('".htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES). " " .htmlentities($_SESSION["oreonlastname"], ENT_QUOTES)."', '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES)."', '". md5($_SESSION["oreonpasswd"]) ."', '".$_SESSION["oreonlang"]."', '".$_SESSION['oreonemail']."', '1', '1', '1');";
		if ($DEBUG) print $requete . "<br>";
		$result = @mysql_query($requete, $res['0']);
		htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES);
	}else {
		$requete = "UPDATE `contact` SET `user_firstname` = '". htmlentities($_SESSION["oreonfirstname"], ENT_QUOTES)."',`user_lastname` = '". htmlentities($_SESSION["oreonlastname"], ENT_QUOTES)  ."',`user_alias` = '". htmlentities($_SESSION["oreonlogin"], ENT_QUOTES) ."',`user_passwd` = '". md5($_SESSION["oreonpasswd"]) ."',`user_mail` = '".$_SESSION['oreonemail']."',`user_status` = '32',`user_lang` = '".$_SESSION["oreonlang"]."' WHERE `user_id` =1 LIMIT 1 ;";
		if ($DEBUG) print $requete . "<br>";
		$result = @mysql_query($requete, $res['0']);
	}
	if ($mysql_msg == '') {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>

	<? }
	@mysql_close($res['0']);
	?>

	<tr>
		<td><b>Database &#146;<? echo $_SESSION["nameOreonDB"]; ?>&#146; : Customization</b></td>
<?
	$mysql_msg = '';
	$res = connexion($_SESSION["nameOreonDB"], $_SESSION["pwdOreonDB"], $_SESSION["dbLocation"]);
	@mysql_select_db($_SESSION["nameOreonDB"], $res['0']) or ( $mysql_msg= mysql_error());

	$conf_installoreon['physical_html_path'] = ($conf_installoreon['physical_html_path'] === "" ?  "/usr/local/nagios/share/images/logo/" : $conf_installoreon['physical_html_path']."/images/logo/");
	$conf_installoreon['nagios'] = ($conf_installoreon['nagios'] === "" ?  "/usr/local/nagios/" : $conf_installoreon['nagios']);
	$conf_installoreon['mail'] = ($conf_installoreon['mail'] === "" ?  "/usr/bin/mail" : $conf_installoreon['mail']);
	$conf_installoreon['nagios_init_script'] = ($conf_installoreon['nagios_init_script'] === "" ?  "/etc/init.d/nagios" : $conf_installoreon['nagios_init_script']);
//	$conf_installoreon['rrdtool'] = ($conf_installoreon['rrdtool'] === "" ?  "/usr/bin/rrdtool" : $conf_installoreon['rrdtool']);

	$requete = "UPDATE `general_opt` SET `nagios_path_img` = '".$conf_installoreon['physical_html_path']."'";
	$requete .= ", `nagios_path` = '".$conf_installoreon['nagios']."'";
	$requete .= ", `nagios_path_bin` = '".$conf_installoreon['nagios_bin']."nagios'";
	$requete .= ", `nagios_init_script` = '".$conf_installoreon['nagios_init_script'];
	$requete .= ", `nagios_path_plugins` = '".$_SESSION["nagios_plugins"]."'";
	$requete .= ", `oreon_path` = '".$_SESSION["oreon_dir"]."'";
	$requete .= ", `oreon_web_path` = '/oreon/'";
	$requete .= ", `oreon_rrdbase_path` = '".$_SESSION["oreon_dir_rrd"]."'";
	$requete .= ", `rrdtool_path_bin` = '".$_SESSION["rrdtool_dir"]."'";
	$requete .= ", `nagios_version` = '".$_SESSION["nagios_version"]."'";
	$requete .= ", `snmp_trapd_used` = '0'";
	$requete .= ", `mailer_path_bin` = '".$conf_installoreon['mail']."' ";
	$requete .= ", `ldap_host` = '".htmlentities($_SESSION["ldap_host"], ENT_QUOTES)."'";
	$requete .= ", `ldap_port` = '".htmlentities($_SESSION["ldap_port"], ENT_QUOTES)."'";
	$requete .= ", `ldap_base_dn` = '".htmlentities($_SESSION["ldap_base_dn"], ENT_QUOTES)."'";
	$requete .= ", `ldap_login_attrib` = '".htmlentities($_SESSION["ldap_login_attrib"], ENT_QUOTES)."'";
	$requete .= ", `ldap_ssl` = '".htmlentities($_SESSION["ldap_ssl"], ENT_QUOTES)."'";
	$requete .= ", `ldap_auth_enable` = '".htmlentities($_SESSION["ldap_auth_enable"], ENT_QUOTES)."'";
	$requete .= ", `debug_path` = '".$_SESSION["oreon_dir"]."log/' ;";

	if ($DEBUG) print $requete . "<br>";
	$result = @mysql_query($requete, $res['0'])or ( $mysql_msg= mysql_error());

	$conf_installoreon['status_file'] = ($conf_installoreon['status_file'] === "" ?  "/usr/local/nagios/var/status.log" : $conf_installoreon['status_file']);
	$conf_installoreon['command_file'] = ($conf_installoreon['command_file'] === "" ?  "/usr/local/nagios/var/rw/nagios.cmd" : $conf_installoreon['command_file']);
	$conf_installoreon['log_archive_path'] = ($conf_installoreon['log_archive_path'] === "" ?  "/usr/local/nagios/var/archives/" : $conf_installoreon['log_archive_path']);
	$conf_installoreon['state_retention_file'] = ($conf_installoreon['state_retention_file'] === "" ?  "/usr/local/nagios/var/status.sav" : $conf_installoreon['state_retention_file']);
	$conf_installoreon['comment_file'] = ($conf_installoreon['comment_file'] === "" ?  "/usr/local/nagios/var/comment.log" : $conf_installoreon['comment_file']);
	$conf_installoreon['downtime_file'] = ($conf_installoreon['downtime_file'] === "" ?  "/usr/local/nagios/var/downtime.log" : $conf_installoreon['downtime_file']);
	$conf_installoreon['lock_file'] = ($conf_installoreon['lock_file'] === "" ?  "/usr/local/nagios/var/nagios.lock" : $conf_installoreon['lock_file']);
	$conf_installoreon['temp_file'] = ($conf_installoreon['temp_file'] === "" ?  "/usr/local/nagios/var/rw/nagios.tmp" : $conf_installoreon['temp_file']);
	$conf_installoreon['log_file'] = ($conf_installoreon['log_file'] === "" ?  "/usr/local/nagios/var/nagios.log" : $conf_installoreon['log_file']);

//	$requete = "UPDATE `nagioscfg` SET `nagios_user` = '".$_SESSION["nagios_user"]."',`nagios_group` = '".$_SESSION["nagios_group"]."',`cfg_pwd` = '".$_SESSION["nagios_conf"]."',`status_file` = '". $conf_installoreon['status_file'] ."',`command_file` = '".$conf_installoreon['command_file']."', ";
//	$requete .= "`log_archive_path` = '".$conf_installoreon['log_archive_path']."', `state_retention_file` = '".$conf_installoreon['state_retention_file']."',`comment_file` = '".$conf_installoreon['comment_file']."',`downtime_file` = '".$conf_installoreon['downtime_file']."',`lock_file` = '".$conf_installoreon['lock_file']."',`temp_file` = '". $conf_installoreon['temp_file']."', `log_file` = '".$conf_installoreon['log_file']."' ;";

	$requete = "UPDATE `cfg_nagios` SET `nagios_user` = '".$_SESSION["nagios_user"]."',`nagios_group` = '".$_SESSION["nagios_group"]."',`cfg_dir` = '".$_SESSION["nagios_conf"]."',`status_file` = '". $conf_installoreon['status_file'] ."',`command_file` = '".$conf_installoreon['command_file']."', ";
	$requete .= "`log_archive_path` = '".$conf_installoreon['log_archive_path']."', `state_retention_file` = '".$conf_installoreon['state_retention_file']."',`comment_file` = '".$conf_installoreon['comment_file']."',`downtime_file` = '".$conf_installoreon['downtime_file']."',`lock_file` = '".$conf_installoreon['lock_file']."',`temp_file` = '". $conf_installoreon['temp_file']."',`log_file` = '".$conf_installoreon['log_file']."' ";
	$requete .= " WHERE `nagios_activate` =1 LIMIT 1;";

	if ($DEBUG) print $requete . "<br>";
	$result = @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());

	$requete = "UPDATE `cfg_resource` SET `resource_line` = '\$USER1\$=".$_SESSION["nagios_plugins"]."' WHERE `resource_id` =1 LIMIT 1  ;";
	//$requete = "UPDATE `resources` SET `resource_line` = '\$USER1\$=".$_SESSION["nagios_plugins"]."' WHERE `resource_id` =1 LIMIT 1  ;";
	if ($DEBUG) print $requete . "<br>";
	$result = @mysql_query($requete, $res['0']) or ( $mysql_msg= mysql_error());

	if (!$mysql_msg) {
		echo '<td align="right"><b><span class="go">OK</b></td></tr>';
	} else {
		echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
        <td colspan="2" align="left"><span class="small"><? echo $mysql_msg; ?></span></td>
	</tr>

<? }

} else {
			echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
	    $return_false = 1;
	?>
	<tr>
    	<td>&nbsp;</td>
        <td align="right"><? echo ( "'".$_SESSION["nameOreonDB"]."' already exist !") ; ?></td>
        <? $return_false = 1; ?>
	</tr>

<?	}

} else {
	echo '<td align="right"><b><span class="stop">CRITICAL</span></b></td></tr>';
$return_false = 1;
?>
<tr>
    <td colspan="2" align="right"><? echo $mysql_msg; ?></td>
</tr>
<?
}
		@mysql_close($res['0']);
// end last code
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
if ($return_false)
	$str .= " disabled";
$str .= " />";
		print $str;
		aff_footer();
?>