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
		<td><b>Writable Centreon Configuration File (centreon.conf.php)</b></td>
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
    	<td>&nbsp;&nbsp;&nbsp;<?php echo $conf_centreon["centreon_etc"]; ?></td>
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
			$file[18] = "\$conf_centreon['hostCentreon'] = \"". $_SESSION["dbLocation"] ."\";\n";
			$file[19] = "\$conf_centreon['hostCentstorage'] = \"". $_SESSION["dbLocation"] ."\";\n";
			$file[20] = "\$conf_centreon['user'] = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file[21] = "\$conf_centreon['password'] = \"". $_SESSION["pwdOreonDB"] . "\";\n";
			$file[22] = "\$conf_centreon['db'] = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file[23] = "\$conf_centreon['dbcstg'] = \"". $_SESSION["nameOdsDB"] . "\";\n";
			$file[24] = "\n\n";
			$file[25] = "/* path to classes */\n";
			$file[26] = "\$classdir='./class';\n";
			$file[27] = "/* Centreon Path */\n";
			$file[28] = "\$centreon_path='".$conf_centreon["centreon_dir"]."';\n";
			$file[29] = "?>";
			
			if ($fd = fopen($conf_centreon["centreon_etc"]."centreon.conf.php", "w"))	{
				for ($i = 0; $file[$i]; $i++)
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
			$file_pm[] = "\$mysql_host = \"". $_SESSION["dbLocation"] ."\";\n";
			$file_pm[] = "\$mysql_user = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file_pm[] = "\$mysql_passwd = \"". $_SESSION["pwdOreonDB"] . "\";\n";
			$file_pm[] = "\$mysql_database_oreon = \"". $_SESSION["nameOreonDB"] . "\";\n";
			$file_pm[] = "\$mysql_database_ods = \"". $_SESSION["nameOdsDB"] . "\";\n";
			$file_pm[] = "1;\n";
			if ($fd = fopen($conf_centreon["centreon_etc"]."/conf.pm", "w"))	{
				for ($i = 0; $file_pm[$i]; $i++)
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