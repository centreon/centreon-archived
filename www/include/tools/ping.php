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
 * For information : contact@oreon-project.org
 */

	 include("/etc/centreon/centreon.conf.php");
	 require_once ("../../$classdir/Session.class.php");
	 require_once ("../../$classdir/Oreon.class.php");

	 Session::start();
	
	 if (!isset($_SESSION["oreon"])) {
	 	// Quick dirty protection
	 	header("Location: ../../index.php");
	 } else
	 	$oreon =& $_SESSION["oreon"];

	if (isset($_GET["host"]))
		$host = $_GET["host"];
	else if (isset($_POST["host"]))
		$host = $_POST["host"];
	else {
		print "Bad Request !";
		exit;
	}

	require ("Net/Ping.php");
	$ping = Net_Ping::factory();

	$msg = "";
	if (!PEAR::isError($ping))	{
    	$ping->setArgs(array("count" => 4));
		$response = $ping->ping($host);
		foreach ($response->getRawData() as $key => $data)
   			$msg .= $data ."<br />";
		print $msg;
	}

?>