<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();

	if (isset($_POST["limit"]) && $_POST["limit"])
		$limit = $_POST["limit"];
	else if (isset($_GET["limit"]))
		$limit = $_GET["limit"];
	else if (isset($oreon->historyLimit[$url]))
		$limit = $oreon->historyLimit[$url];
	else {
		if ($p != 2 || ($p >= 200 && $p < 300) || ($p >= 20000 && $p < 30000)){
			$pagination = "maxViewConfiguration";
			$DBRESULT =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$gopt = array_map("myDecode", $DBRESULT->fetchRow());		
			$limit = $gopt["maxViewConfiguration"];
		} else 
			$limit = 120;
	}

	if (isset($_POST["num"]) && $_POST["num"])
		$num = $_POST["num"];
	else if (isset($_GET["num"]) && $_GET["num"])
		$num = $_GET["num"];
	else if (isset($oreon->historyPage[$url]))
		$num = $oreon->historyPage[$url];
	else 
		$num = 0;
			
	global $search;
?>