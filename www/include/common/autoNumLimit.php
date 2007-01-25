<?
	if (isset($_GET["limit"]))
		$limit = $_GET["limit"];
	else if (isset($oreon->historyLimit[$url]))
		$limit = $oreon->historyLimit[$url];
	else {
		if ($p != 2 || ($p >= 200 && $p < 300) || ($p >= 20000 && $p < 30000)){
			$DBRESULT =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			$gopt = array_map("myDecode", $DBRESULT->fetchRow());		
			$limit = $gopt["maxViewConfiguration"];
		} else 
			$limit = 120;
	}
		
	if (isset($_GET["num"]))
		$num = $_GET["num"];
	else if (isset($oreon->historyPage[$url]))
		$num = $oreon->historyPage[$url];
	else 
		$num = 0;
?>