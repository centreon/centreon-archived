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
	
	/*
	 * Check if other update are available after last upgrade
	 */
	
	chdir('sql');
	
	if (count(glob("Update-DB-".$version["value"]."_to_*.sql"))) {
		/*
		 * An upgrade is available for this version
		 */
		require_once("$centreon_path/www/install/step_upgrade/step3.php");
		
	} else {
		
		/*
		 * No upgrade is available for this version 
		 */
		aff_header("Centreon Setup Wizard", "Verifying PHP Pear Component", 7);

		?>
		<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr">
		  <tr>
		    <th align="left">Component</th>
		    <th style="text-align: right;">Status</th>
		  </tr>
		  <tr>
		    <td><b>PHP Pear Extension</b></td>
		    <td align="right">&nbsp;</td>
		  </tr><?php
		  
			$msg = NULL;  
			$alldeps = NULL;
			
			$include_path = get_include_path();
			
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
		$str .= "<input class='button' type='submit' name='goto-B' value='Back' /><input class='button' type='submit' name='goto' value='Next' id='button_next'";
		if (isset($return_false) && $return_false)
			$str .= " disabled";
		$str .= " />";
		print $str;
		aff_footer();
	}
?>

