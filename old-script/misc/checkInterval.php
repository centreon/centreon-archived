<?
	ini_set('display_errors', 1);

	include("DB.php");
	
	
	include_once("./oreon.conf.php");
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $conf_oreon["user"],
	    'password' => $conf_oreon["password"],
	    'hostspec' => $conf_oreon["host"],
	    'database' => $conf_oreon["ods"],
	);
	
	$options = array(
	    'debug'       => 2,
	    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
	);
	
	$pearDBO =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDBO))
	    die($pearDBO->getMessage());
	
	$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);
	
	$today_end = mktime(15, 0, 0, date("m")  , date("d") - 1, date("Y"));
	$today_beg = mktime(15, 0, 0, date("m")  , date("d") - 2, date("Y"));
	
	$DBRESULT = $pearDBO->query("SELECT * FROM data_bin WHERE id_metric = '55' AND ctime >= '$today_beg' AND ctime < '$today_end'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
    $old_time = 0;
    while ($DBRESULT->fetchInto($data)){
    	if ($old_time == 0){
	    	$i = $data["ctime"];
    		$is = $i / 60;
	    	print date ("G:i:s (d)", $data["ctime"]) . " <br />\n";
    	} else {
    		$i = $data["ctime"] - $old_time;
    		$is = $i / 60;
    		print date ("G:i:s (d)", $data["ctime"]) . " : " . $data["ctime"] .  " - " .$old_time . " = ". $i." sec (".$is.")<br />\n";
    	}
    	$old_time = $data["ctime"];
    }

?>
