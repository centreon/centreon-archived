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

aff_header("Centreon Setup Wizard", "Post-Installation", 12);	?>

<table cellpadding="0" cellspacing="0" border="0" width="80%" class="StyleDottedHr" align="center">
  <tr>
	<td colspan="2" ><b>End of Setup</b></td>
  </tr>
  <tr>
	<td colspan="2"><br />
	
	Centreon Setup is finished. 
	<br />
	<b>Self service and commercial Support.</b><br /><br />
	There are various ways to get information about Centreon ; the documentation, wiki, forum etc...
	<ul>
		<li> Centreon WebSite : <a target="_blank" href="http://www.centreon.com">www.centreon.com</a></li>
		<li> Centreon Forum : <a target="_blank" href="http://forum.centreon.com">forum.centreon.com</a></li></li>
		<li> Centreon Wiki : <a target="_blank" href="http://doc.centreon.com">doc.centreon.com</a></li>
	</ul>
	<br /><p align="justify">
	If your company needs professional consulting and services for Centreon, or if you need to purchase a support contract for it, don't hesitate to contact official </b><a  target="_blank" href="http://www.merethis.com">Centreon support center</a></b>.
	</p>
	</td>
  </tr>
   <tr>
	<td colspan="2">&nbsp;</td>
  </tr>
<?php
// end last code
aff_middle();
$str = "<input class='button' type='submit' name='goto' value='Click here to complete your install' id='button_next' ";
$str .= " />";
print $str;
aff_footer();
?>