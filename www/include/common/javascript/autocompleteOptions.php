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
 * For information : contact@oreon-project.org
 */
 	require_once("../../../centreon.conf.php");
	require_once("../../../DBconnect.php");
	
	if (isset($_GET['debut']))
		$debut = utf8_decode($_GET['debut']);
	else
		$debut = "ni";
	if (isset($_GET['country']))
		$country = utf8_decode($_GET['country']);
	else
		$country = '1';
	header('Content-Type: text/xml;charset=utf-8');
	echo(utf8_encode("<?xml version='1.0' encoding='UTF-8' ?><options>"));	
	$debut = strtolower($debut);
	$res =& $pearDB->query("SELECT city_name FROM view_city WHERE country_id = '".$country."' AND city_name LIKE '".$debut."%' ORDER BY city_name limit 0,15");
	if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while ($res->fetchInto($city))
			echo(utf8_encode("<option>".$city["city_name"]."</option>"));
	echo("</options>");
	$pearDB->disconnect();
?>
