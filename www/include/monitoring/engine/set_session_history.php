<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon 
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



if(isset($_POST["sid"])){
	$sid = $_POST["sid"];

$path = '/srv/oreon/www';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
// dossier courant
//echo getcwd() . "<br />";
chdir('/srv/oreon/www');
// dossier courant
//echo getcwd() . "<br />";
require_once("class/Oreon.class.php");
session_id($sid);

spl_autoload('Oreon');
$oreon = $_SESSION['oreon'];

/// maj

if(isset($_POST["limit"]) && isset($_POST["url"])){
	$oreon->historyLimit[$_POST["url"]] = $_POST["limit"];
}
if(isset($_POST["page"]) && isset($_POST["url"])){
	$oreon->historyPage[$_POST["url"]] = $_POST["page"];
echo "------------->". $_POST["page"];
}
if(isset($_POST["search"]) && isset($_POST["url"])){
	$oreon->historySearch[$_POST["url"]] = $_POST["search"];
}

}
else
echo "sid??";


?>