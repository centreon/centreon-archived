<?php

$local_file = "br.txt";
$country_id = "13";
$absPath_to_OreonOconf = "../../../centreon.conf.php";

function microtime_float() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();
set_time_limit(3000);

$log_file = fopen($local_file, "r");

require_once('DB.php');
require_once($absPath_to_OreonOconf );

$dsn = array(
	     'phptype'  => 'mysql',
	     'username' => $conf_oreon['user'],
	     'password' => $conf_oreon['password'],
	     'hostspec' => $conf_oreon['host'],
	     'database' => $conf_oreon['db'],
	     );

$options = array(
		 'debug'       => 2,
		 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
		 );

$db =& DB::connect($dsn, $options);
if (PEAR::isError($db)) {
  die($db->getMessage());
}

$db->setFetchMode(DB_FETCHMODE_ASSOC);

$tab = array();
$last_elem = NULL;
$i = 2;
if ($log_file)	{
	for ($cpt = 0; $str = fgets($log_file); $cpt++)	{
	   	$tab = split("\t", $str);
	    if ($cpt && $tab[9] == "P")	{
	    	$lat = $tab[3];
	    	$long = $tab[4];
	    	$tab = array_reverse($tab);			
			$date = explode("-", $tab[0]);
			$date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
			if ($tab[1] == $last_elem)	{
				$tab[1] = $tab[1]." - ".$i;
				$i++;
			}
			else	{
				$last_elem = $tab[1];
				$i = 2;
			}
			$db->query("INSERT INTO `view_city` ( `city_id` , `country_id` , `city_name` , `city_zipcode` , `city_lat` , `city_long`, `city_date`) VALUES ('', '".$country_id."', '".$tab[1]."', NULL , '".$lat."', '".$long."', '".$date."')");
		}
		unset($str);
	}
}
$time_end = microtime_float();
$now = $time_end - $time_start;
print round($now,3) . "secondes";
?>
