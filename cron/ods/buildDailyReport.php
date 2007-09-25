<?
	ini_set('display_errors', 1);

	include("DB.php");
	
	
	include_once("./centreon.conf.php");
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
	
	$DBRESULT = $pearDBO->query("SELECT * FROM data_bin WHERE id_metric = '137' AND ctime >= '$today_beg' AND ctime < '$today_end'");
	if (PEAR::isError($DBRESULT))
		print "Mysql Error : ".$DBRESULT->getDebugInfo();
    	$old_time = 0;
	$min = 0;
	$max = 0;
	$count = 0;
	$total = 0;
	$average = 0;
	$variance = 0;
	$tab = array();
    	while ($DBRESULT->fetchInto($data)){
		if ($data["value"] > $max)
			$max = $data["value"];
		if ($data["value"] < $min)
			$min = $data["value"];
		$total += $data["value"];
		$count++;
		$tab[$count] = $data["value"];
    		
	}
	$total_v = 0;
	$average = $total / $count;
	foreach ($tab as $t){
		$total_v += ($data["value"] - $average) * ($data["value"] - $average); 
	}
	$variance = $total_v  / $count;
	print "Min : " . $min . "<br>\n";
	print "Max : " . $max . "<br>\n";
	print "Average : " . $average . "<br>\n";
	print "Variance : " . $variance . "<br>\n";	
?>
