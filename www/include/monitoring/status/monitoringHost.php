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

	require_once './class/other.class.php';
	include_once("./include/monitoring/external_cmd/cmd.php");

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$DBRESULT =& $pearDB->query("SELECT ndo_base_prefix,ndo_activate FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	
	$ndo = $gopt["ndo_activate"];
		
	if ($ndo)
		$path = "./include/monitoring/status/status-ndo/";
	else
		$path = "./include/monitoring/status/status-log/";
		
	$pathRoot = "./include/monitoring/";
	$pathDetails = "./include/monitoring/objectDetails/";
	$pathTools = "./include/tools/";

	if(isset($_GET["cmd"]) && $_GET["cmd"] == 14 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 1){
		if (!isset($_GET["notify"]))
			$_GET["notify"] = 0;
		if (!isset($_GET["persistent"]))
			$_GET["persistent"] = 0;
		acknowledgeHost($lang);
	} else if(isset($_GET["cmd"]) && $_GET["cmd"] == 14 && isset($_GET["author"]) && isset($_GET["en"]) && $_GET["en"] == 0){
		acknowledgeHostDisable($lang);
	}

	if ($min){
		switch ($o)	{
			default : require_once($pathTools."tools.php"); break;
		}
	} else {

		if (!$ndo){
			include("./include/monitoring/status/resume.php"); 
			switch ($o)	{
				case "h" 	: require_once($path."host.php"); 					break;
				case "hpb" 	: require_once($path."host.php");		 			break;
				case "hd" 	: require_once($pathDetails."hostDetails.php"); 	break;
				case "hak" 	: require_once($pathRoot."external_cmd/hostAcknowledge.php"); 	break;
				default 	: require_once($path."host.php"); 					break;		
			}
		}else{
			switch ($o)	{
				case "h" 	: require_once($path."host.php"); 					break;
				case "hpb" 	: require_once($path."host.php"); 					break;
				case "hd" 	: require_once($pathDetails."hostDetails.php"); 	break;
				case "hak" 	: require_once($pathRoot."external_cmd/hostAcknowledge.php"); 	break;
				default 	: require_once($path."host.php"); 					break;
			}
		}	
	}
?>