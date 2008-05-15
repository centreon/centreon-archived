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

if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["ldap_auth_enable"] = $_POST["ldap_auth_enable"];
	$_SESSION["ldap_host"] = $_POST["ldap_host"];
	$_SESSION["ldap_port"] = $_POST["ldap_port"];
	$_SESSION["ldap_base_dn"] = $_POST["ldap_base_dn"];
	$_SESSION["ldap_login_attrib"] = $_POST["ldap_login_attrib"];
	$_SESSION["ldap_ssl"] = $_POST["ldap_ssl"];
}

aff_header("Centreon Setup Wizard", "Centreon Configuration File", 10);	?>
<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
	<tr>
    	<th align="left">Component</th>
    	<th style="text-align: right;">Status</th>
  	</tr>
  	<tr>
		<td><b>Writable Oreon Configuration File (centreon.conf.php)</b></td>
		<td align="right"><?php
       	$uid = posix_getpwuid (fileowner($conf_centreon["centreon_etc"]));
		$gid = posix_getgrgid (filegroup($conf_centreon["centreon_etc"]));
       	$perms = substr(sprintf('%o', fileperms($conf_centreon["centreon_etc"])), -3) ;
		///print $uid['name'] . " " . $gid['name'] . " : ".$perms;
		if((strcmp($perms,'755') == 0 )  && (!strcmp($_SESSION['apache_user'], $uid['name'])) && (!strcmp($_SESSION['apache_group'], $gid['name'])) ){
          	echo '<b><span class="go">OK</font></b>';
        	 $msg =  '';
		} else {
          	echo '<b><span class="stop">Critical: Not Writeable</font></b>';
          	$msg =  $uid['name'] .':'.$gid['name'].'&nbsp;(' .$perms. ')</b>';
          	$msg .=  '<br />Should be '. $_SESSION['apache_user'].':'.$_SESSION['apache_user'].' (755)';
		    $return_false = 1;
       	}?>
       	</td>
	</tr>
  	<tr>
    	<td>&nbsp;&nbsp;&nbsp;<?php echo $conf_centreon["centreon_dir"]; ?></td>
    	<td align="right"><b><?php echo $msg ;	?></b></td>
  	</tr>

  	<tr>
		<td><b>Generate Centreon configuration file</b></td>
		<td align="right"><?php
		
			$_SESSION["pwdOreonDB"] = str_replace("\$", "\\\$", $_SESSION["pwdOreonDB"]);
		
			$file[0] = "<?php\n";
			$file[1] = "/*\n";
			$file[2] = " * Centreon is developped with GPL Licence 2.0 :\n";
			$file[3] = " * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt\n";
			$file[4] = " * Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf\n";
			$file[5] = " * \n";
			$file[6] = " * The Software is provided to you AS IS and WITH ALL FAULTS.\n";
			$file[7] = " * Centreon makes no representation and gives no warranty whatsoever,\n";
			$file[8] = " * whether express or implied, and without limitation, with regard to the quality,\n";
			$file[9] = " * safety, contents, performance, merchantability, non-infringement or suitability for\n";
			$file[10] = " * any particular or intended purpose of the Software found on the Centreon web site.\n";
			$file[11] = " * In no event will Centreon be liable for any direct, indirect, punitive, special,\n";
			$file[12] = " * incidental or consequential damages however they may arise and even if Centreon has\n";
			$file[13] = " * been previously advised of the possibility of such damages.\n";
			$file[14] = " * \n";
			$file[15] = " * For information : contact@centreon.com\n";
			$file[16] = " */\n";
			$file[17] = "/* \tDatabase */\n";
			$file[18] = "\$conf_oreon['hostCentreon'] = \"". $_SESSION["dbLocation"] ."\";\n";
			$file[19] = "\$conf_oreon['hostCentstorage'] = \"". $_SESSION["dbLocation"] ."\";\n";
			$file[20] = "\$conf_oreon['user'] = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file[21] = "\$conf_oreon['password'] = \"". $_SESSION["pwdOreonDB"] . "\";\n";
			$file[22] = "\$conf_oreon['db'] = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file[23] = "\$conf_oreon['dbcstg'] = \"". $_SESSION["nameOdsDB"] . "\";\n";
			$file[24] = "\n\n";
			$file[25] = "/* path to classes */\n";
			$file[26] = "\$classdir='./class';\n";
			$file[27] = "/* Centreon Path */\n";
			$file[28] = "\$centreon_path='".$conf_centreon["centreon_dir"]."';\n";
			$file[29] = "?>";
			
			if ($fd = fopen($conf_centreon["centreon_etc"]."centreon.conf.php", "w"))	{
				for ($i = 0; $i <= 28; $i++)
					fwrite ($fd, $file[$i]);
				fclose ($fd);
				echo '<b><span class="go">OK</b>';
			} else {
			   echo '<b><span class="stop">Critical: Can\'t create file</font></b>';
		          	$msg =  $php_errormsg;
				    $return_false = 1;
			}	?>
		</td>
	</tr>
    <tr>
	    <td>&nbsp;&nbsp;&nbsp;<?php echo $conf_centreon["centreon_etc"].'centreon.conf.php'; ?></td>
	    <td align="right"><b><?php echo $msg ;	?></b></td>
 	</tr>
 	<tr>
		<td><b>Generate Centstorage configuration file</b></td>
		<td align="right"><?php
			$file_pm = array();
			$file_pm[0] = "\$mysql_host = \"". $_SESSION["dbLocation"] ."\";\n";
			$file_pm[1] = "\$mysql_user = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file_pm[2] = "\$mysql_passwd = \"". $_SESSION["pwdOreonDB"] . "\";\n";
			$file_pm[3] = "\$mysql_database_oreon = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file_pm[4] = "\$mysql_database_ods = \"". $_SESSION["nameOdsDB"] . "\";\n";
			$file_pm[5] = "1;\n";
			if ($fd = fopen($conf_centreon["centreon_etc"]."/conf.pm", "w"))	{
				for ($i = 0; $i <= 5; $i++)
					fwrite ($fd, $file_pm[$i]);
				fclose ($fd);
				echo '<b><span class="go">OK</b>';
			} else {
			   	echo '<b><span class="stop">Critical: Can\'t create file for ODS</font></b>';
		        $msg =  $php_errormsg;
				$return_false = 1;
			}	?>
		</td>
	</tr>
    <tr>
	    <td>&nbsp;&nbsp;&nbsp;<?php echo $conf_centreon["centreon_etc"].'/conf.pm'; ?></td>
	    <td align="right"><b><?php echo $msg ;	?></b></td>
 	</tr>
<?php
	aff_middle();
	$str = '';
	if (isset($return_false))
		$str = "<input class='button' type='submit' name='Recheck' value='Recheck' />";
	$str .= "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next' ";
	if ($return_false)
		$str .= " disabled";
	$str .= " />";
	print $str;
	aff_footer();
?>