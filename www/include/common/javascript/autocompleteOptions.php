<?
	require_once("../../../oreon.conf.php");
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
	$res =& $pearDB->query("SELECT DISTINCT city_name FROM view_city WHERE country_id = '".$country."' AND city_name LIKE '".$debut."%' ORDER BY city_name limit 0,10");
	while($res->fetchInto($city))
		echo(utf8_encode("<option>".$city["city_name"]."</option>"));
	echo("</options>");
	$pearDB->disconnect();
?>
