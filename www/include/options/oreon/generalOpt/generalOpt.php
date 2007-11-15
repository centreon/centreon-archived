<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["gopt_id"]) ? $cG = $_GET["gopt_id"] : $cG = NULL;
	isset($_POST["lca_id"]) ? $cP = $_POST["gopt_id"] : $cP = NULL;
	$cG ? $gopt_id = $cG : $gopt_id = $cP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the option dir
	$path = "./include/options/oreon/generalOpt/";
	
	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "nagios" : require_once($path."nagios/formNagios.php"); break;
		case "colors" : require_once($path."colors/formColors.php"); break;
		case "snmp" : require_once($path."snmp/formSNMP.php"); break;
		case "rrdtool" : require_once($path."rrdtool/formRRDTool.php"); break;
		case "ldap" : require_once($path."ldap/formLDAP.php"); break;
		case "debug" : require_once($path."debug/formDebug.php"); break;
		case "general" : require_once($path."general/formGeneralOpt.php"); break;
		case "css" : require_once($path."css/formCss.php"); break;
		case "ods" : require_once($path."OreonDataStorage/formODS.php"); break;
		case "ndo" : require_once($path."ndo/formNDO.php"); break;
		default : require_once($path."general/formGeneralOpt.php"); break;
	}
?>