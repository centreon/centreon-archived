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

if (isset($_POST["goto"]) && strcmp($_POST["goto"], "Back")){
	$_SESSION["nagios_user"] = $_POST["nagios_user"];
	$_SESSION["nagios_group"] = $_POST["nagios_group"];
	$_SESSION["apache_user"] = $_POST["apache_user"];
	$_SESSION["apache_group"] = $_POST["apache_group"];
	$_SESSION["nagios_version"] = $_POST["nagios_version"];
	$_SESSION["nagios_conf"] = $_POST["nagios_conf"];
	$_SESSION["nagios_plugins"] = $_POST["nagios_plugins"];
	$_SESSION["rrdtool_dir"] = $_POST["rrdtool_dir"];
	chdir('..');
	$_SESSION["oreon_dir_www"] = getcwd() . '/';
	chdir('..');
	$_SESSION["oreon_dir"] = getcwd() . '/' ;
	$_SESSION["oreon_dir_rrd"] = getcwd() . '/rrd/';
	chdir('www/install');
}
aff_header("Centreon Setup Wizard", "Verifying Configuration", 4);	?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr">
  	<tr>
    	<th align="left">Component</th>
    	<th style="text-align: right;">Status</th>
  	</tr>
  	<tr>
   		<td><b>PHP Version 5.x</b></td>
    	<td align="right"><?php
			$php_version = phpversion();
	       	if(str_replace(".", "", $php_version) < "500" ){
	         	echo "<b><span class=stop>Invalid version ($php_version) Installed</span></b>";
			  	$return_false = 1;
	       	} else {
	          	echo "<b><span class=go>OK (ver $php_version)</span></b>";
	       	}?>
     	</td>
  </tr>
  <tr>
    	<td><b>PHP Extension</b></td>
    	<td align="right">&nbsp;</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;MySQL</b></td>
    	<td align="right"><?php
			if (extension_loaded('mysql')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Critical: mysql.so not loaded in php.ini</font></b>';
		    	$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;GD</b></td>
    	<td align="right"><?php
			if (extension_loaded('gd')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Critical: gd.so not loaded in php.ini</font></b>';
		    	$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;LDAP</b></td>
    	<td align="right"><?php
			if (extension_loaded('ldap')) {
          		echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="warning">Warning: ldap.so not loaded in php.ini</font></b>';
		    	//$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;SNMP</b></td>
    	<td align="right"><?php
			if (extension_loaded('snmp'))
          		echo '<b><span class="go">OK</font></b>';
			else {
				echo '<b><span class="warning">Warning: snmp.so not loaded in php.ini</font></b>';
		   	 	//$return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;XML Writer</b></td>
    	<td align="right"><?php
			if (extension_loaded('xmlwriter'))
          		echo '<b><span class="go">OK</font></b>';
			else
				echo '<b><span class="warning">Warning: xml.so not loaded in php.ini</font></b>';?>
		</td>
  </tr>
    <tr>
    	<td><b>&nbsp;&nbsp;&nbsp;PHP-POSIX</b></td>
    	<td align="right"><?php
			if (function_exists('posix_getpwuid'))
          		echo '<b><span class="go">OK</font></b>';
			else {
				echo '<b><span class="stop">Critical: php-posix functions are not installed</font></b>';
				$return_false = 1;	
			}?>
		</td>
  </tr>
  
  <tr>
		<td><b>&nbsp;&nbsp;&nbsp;PEAR</b></td>
    	<td align="right"><?php
    		$tab_path = split(":", get_include_path());
    		$ok = 0;
    		foreach ($tab_path as $path){
    			if (file_exists($path. '/PEAR.php')){
    				$_SESSION["include_path"] = $path;
    				$ok = 1;
    			}
    		}
    		
			if ($ok){
				echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Warning: PHP Pear not found <br />'. $pear_path . '/PEAR.php</font></b>';
			    $return_false = 1;
			}?>
		</td>
  </tr>
  <tr>
    <td><b>Writable Nagios Config Directory</b></td>
    <td align="right"><?php

	    if (is_dir($_SESSION['nagios_conf'])) {
	       	$uid = @posix_getpwuid (fileowner($_SESSION['nagios_conf']));
			$gid = @posix_getgrgid (filegroup($_SESSION['nagios_conf']));
	       	$perms = substr(sprintf('%o', fileperms($_SESSION['nagios_conf'])), -3) ;
			if (!(strcmp($perms,'775')) && !strcmp($_SESSION['nagios_user'], $uid['name']) && !strcmp($_SESSION['apache_group'], $gid['name'])){
	          	echo '<b><span class="go">OK</font></b>';
	          	 $msg =  '';
			} else {
	            echo '<b><span class="stop">Critical: Not Writeable</font></b>';
	          	$msg =  $uid['name'] .':'. $gid['name'] .'&nbsp;(' .$perms. ')</b></span>' ;
	          	$msg .=  '<br />Should be '.$_SESSION['nagios_user'].':'.$_SESSION['apache_group'].' (775)';
			   	echo $msg;
			    $return_false = 1;
	       	}
	    } else {
	    	echo '<b><span class="stop">Critical: Directory not exist</span></b>';
	    	$msg =  '';
			$return_false = 1;
	    } ?>
		</td>
  </tr>
</table>
<?php
aff_middle();
$str = '';
if (isset($return_false))
	$str = "<input class='button' type='submit' name='Recheck' value='Recheck' />";
$str .= "<input class='button' type='submit' name='goto' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next'";
if ($return_false)
	$str .= " disabled";
$str .= " />";
print $str;
aff_footer();
?>
