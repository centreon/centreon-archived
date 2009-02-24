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
 
aff_header("Centreon Setup Wizard", "Verifying PHP Pear Component", 5);

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
    <td><b>PHP Pear Extension</b></td>
    <td align="right">&nbsp;</td>
  </tr>

	<?php
	$msg = NULL;  
	$alldeps = NULL;
	
	$tab_path = split(":", get_include_path());
	
	foreach ($pear_module as $module) {	?>
   <tr>
    <td><b>&nbsp;&nbsp;&nbsp;<?php echo $module["name"] ?></b></td>
    <td align="right"><?php
    	$msg = NULL;  
    	$ok = 0;
    	foreach ($tab_path as $path){
			if (file_exists($path. '/PEAR.php'))
				$ok = 1;
		}
    	if ($ok) {
          	echo '<b><span class="go">OK</font></b>';
		} else {
			echo '<b><span class="stop">Failed</font></b>';
			$msg ="Need " . $module["name"] . "-" . $module["version"];
			$alldeps =  $alldeps . " " . $module["name"];
		    $return_false = 1;
		}
		?></td>
  </tr>
  <?php if($msg)  { ?>
  <tr>
    <td align="right" colspan="2"><?php echo $msg ; ?></td>
  </tr>
  <?php } ?>

  <?php } ?>
  <?php if($alldeps)  { ?>
   <tr>
    <td colspan="2" ><span class="warning">Run this shell command under root user : </span></td>
  </tr>
  <tr>
    <td colspan="2" ><span class="warning">pear install -o -f --alldeps <?php echo $alldeps; ?> </span></td>
  </tr>
   <?php } ?>

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