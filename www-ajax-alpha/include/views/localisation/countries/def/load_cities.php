<?

$local_file = "fr.txt";
$country_id = "1";
//$absPath_to_OreonOconf = $path = $oreon->optGen["oreon_web_path"]."oreon.conf.php";
$absPath_to_OreonOconf = "/usr/local/oreon/www/oreon.conf.php";

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

$insert = false;
$current = array();
$cmp = array();
if ($log_file)	{
	while ($str = fgets($log_file))	{
	    $tab = split("\t", $str);
	   /* foreach ($tab as $key=>$value)	{
	    	print "key :".$key." value:".$value."\n";
	    }*/
	    if ($tab[23] != "FULL_NAME_ND")	{
			$date = explode("-", $tab[24]);
			$date = mktime(0, 0, 0, $date[1], $date[2], $date[0]);
			//print "INSERT INTO `view_city` ( `city_id` , `country_id` , `city_name` , `city_zipcode` , `city_lat` , `city_long`, `city_date`) VALUES ('', '".$country_id."', '".$tab[23]."', NULL , '".$tab[3]."', '".$tab[4]."', '".$date."')\n";
			$db->query("INSERT INTO `view_city` ( `city_id` , `country_id` , `city_name` , `city_zipcode` , `city_lat` , `city_long`, `city_date`) VALUES ('', '".$country_id."', '".$tab[23]."', NULL , '".$tab[3]."', '".$tab[4]."', '".$date."')");
		}
	}
}
$time_end = microtime_float();
$now = $time_end - $time_start;
print round($now,3) . "secondes";

?>