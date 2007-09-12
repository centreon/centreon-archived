<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

aff_header("Oreon Setup Wizard", "Verifying PHP Pear Component", 7);

?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="StyleDottedHr">
  <tr>
    <th align="left">Component</th>
    <th style="text-align: right;">Status</th>
  </tr>
  <tr>
    <td><b>PHP Pear Extension</b></td>
    <td align="right">&nbsp;</td>
  </tr><?
  
	$msg = NULL;  
	$alldeps = NULL;
	
	$include_path = get_include_path();
	$tab = preg_split('/\:/', $include_path);
	foreach ($tab as $path){
		if (is_dir($path))
			$pear_path = $path;	
	}
	
	foreach ($pear_module as $module) {	?>
	   <tr>
	    <td><b>&nbsp;&nbsp;&nbsp;<? echo $module["name"] ?></b></td>
	    <td align="right"><?
	    	$msg = NULL;  
	    	if (file_exists($pear_path."/".$module["path"])) {
	          	echo '<b><span class="go">OK</font></b>';
			} else {
				echo '<b><span class="stop">Failed</font></b>';
				$msg ="Need " . $module["name"] . "-" . $module["version"];
				$alldeps =  $alldeps . " " . $module["name"];
			    $return_false = 1;
			}
			?></td>
	  </tr>
	  <? if($msg)  { ?>
	  <tr>
	    <td align="right" colspan="2"><? echo $msg ; ?></td>
	  </tr>
	  <? } ?>
	
	  <? } ?>
	  <? if($alldeps)  { ?>
	   <tr>
	    <td colspan="2" ><span class="warning">Run this shell command under root user : </span></td>
	  </tr>
	  <tr>
	    <td colspan="2" ><span class="warning">pear install -o -f --alldeps <? echo $alldeps; ?> </span></td>
	  </tr>
	   <? } ?>
	</table>
<?

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
?>