<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache');

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path."/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/centreonDowntime.class.php";
	
$pearDB = new CentreonDB();

if (isset($_GET['dt_id'])) {
	$id = $_GET['dt_id'];
} else {
	$id = 0;
}

$path = $centreon_path . "/www/include/configuration/configDowntime/";

$downtime = new CentreonDowntime($pearDB);

require_once $path . 'json.php';
$periods = $downtime->getPeriods($id);
$json = new Services_JSON();
print $json->encode($periods);
?>