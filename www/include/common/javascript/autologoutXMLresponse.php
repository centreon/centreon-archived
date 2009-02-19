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

require_once("@CENTREON_ETC@/centreon.conf.php");
require_once($centreon_path . "www/class/centreonDB.class.php");
require_once($centreon_path . "www/class/centreonXML.class.php");

$pearDB = new CentreonDB();

$buffer = new CentreonXML();
$buffer->startElement("entry");
$DBRESULT =& $pearDB->query("SELECT * FROM session WHERE session_id = '" . $_GET['sid'] . "'");
if ($DBRESULT->numRows())
	$buffer->writeElement("state", "ok");
else
	$buffer->writeElement("state", "nok");
$buffer->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate'); 
$buffer->output();
?>