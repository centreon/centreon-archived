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
 
header('Content-Type: text/xml');
header('Cache-Control: no-cache');
require_once("@CENTREON_ETC@/centreon.conf.php");
require_once ($centreon_path."/www/DBconnect.php");

echo "<?xml version=\"1.0\"?>\n";

$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register='0' ORDER BY host_id");
if (PEAR::isError($DBRESULT))
	print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";

	
/*
 *  The first element of the select is empty
 */
echo "<template_data>\n";
echo "<template>";
echo "<tp_id>0</tp_id>\n";	
echo "<tp_alias>empty</tp_alias>\n";	
echo "</template>\n";

/*
 *  Now we fill out the select with templates id and names
 */
while ($h = $DBRESULT->fetchRow())
{
	if ($h['host_id'] != $_GET['host_id']) {
		echo "<template>";
		echo "<tp_id>".$h['host_id']."</tp_id>\n";	
		echo "<tp_alias>".$h['host_name']."</tp_alias>\n";	
		echo "</template>\n";
	}
}
echo "</template_data>\n";
?>