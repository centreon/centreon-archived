<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	
	include("@CENTREON_ETC@/centreon.conf.php");
	require_once ("../../$classdir/centreonSession.class.php");
	require_once ("../../$classdir/centreon.class.php");

	CentreonSession::start();
	
	if (!isset($_SESSION["centreon"])) {
		// Quick dirty protection
	 	header("Location: ../../index.php");
		exit;
	} else {
	 	$centreon = $_SESSION["centreon"];
	}
	 
	if (isset($_GET["host"]))
		$host = htmlentities($_GET["host"], ENT_QUOTES, "UTF-8");
	else if (isset($_POST["host"]))
		$host = htmlentities($_POST["host"], ENT_QUOTES, "UTF-8");
	else {
		print "Bad Request !";
		exit;
	}

	require ("Net/Ping.php");
	$ping = Net_Ping::factory();

	$msg = "";
	if (!PEAR::isError($ping))	{
    	$ping->setArgs(array("count" => 4));
		# patch for user that have PEAR Traceroute 0.21.1, remote exec possible Julien Cayssol
		$response = $ping->ping(escapeshellcmd($host));
		foreach ($response->getRawData() as $key => $data)
   			$msg .= $data ."<br />";
		print $msg;
	}

?>
