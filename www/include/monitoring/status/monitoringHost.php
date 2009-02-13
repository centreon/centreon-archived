<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
		exit();

	require_once './class/other.class.php';
	include_once("./include/monitoring/common-Func.php");
	include_once("./include/monitoring/external_cmd/cmd.php");

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$path = "./include/monitoring/status/Hosts/";
	$path_hg = "./include/monitoring/status/HostGroups/";
	
	$pathRoot = "./include/monitoring/";
	$pathDetails = "./include/monitoring/objectDetails/";
	$pathTools = "./include/tools/";

	if(isset($_GET["cmd"]) && $_GET["cmd"] == 14 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 1){
		if (!isset($_GET["notify"]))
			$_GET["notify"] = 0;
		if (!isset($_GET["persistent"]))
			$_GET["persistent"] = 0;
		acknowledgeHost();
	} else if(isset($_GET["cmd"]) && $_GET["cmd"] == 14 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 0){
		acknowledgeHostDisable();
	}

	if ($min){
		switch ($o)	{
			default : require_once($pathTools."tools.php"); break;
		}
	} else {		
		include_once("./class/centreonDB.class.php");
		
		$pearDBndo = new CentreonDB("ndo");
		
		if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
			print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
		} else {
	
			if (preg_match("/connect\ failed/", $pearDBndo->toString(), $str)) 
				print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";			
			else {
				if ($err_msg = table_not_exists("centreon_acl")) 
					print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
				switch ($o)	{
					case "h" 	: require_once($path."host.php"); 					break;
					case "hpb" 	: require_once($path."host.php"); 					break;
					case "h_unhandled" 	: require_once($path."host.php"); 					break;
					case "hd" 	: require_once($pathDetails."hostDetails.php"); 	break;
					case "hak" 	: require_once($pathRoot."acknowlegement/hostAcknowledge.php"); 	break;
					default 	: require_once($path."host.php"); 					break;
				}
			}
		}
	}
?>