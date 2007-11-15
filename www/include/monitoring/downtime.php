<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	
	#Path to the configuration dir
	$path = "./include/monitoring/external_cmd/";
	
	#PHP functions
	require_once "./include/common/common-Func.php";
	require_once "./include/monitoring/common-Func.php";

	switch ($o)	{
		case "ah" : require_once($path."AddHostDowntime.php"); break;
		case "as" : require_once($path."AddSvcDowntime.php"); break;
		case "ds" : DeleteDowntime("SVC",isset($_GET["select"]) ? $_GET["select"] : array());require_once($path."viewDowntime.php"); break; 
		case "dh" : DeleteDowntime("HOST",isset($_GET["select"]) ? $_GET["select"] : array());require_once($path."viewDowntime.php"); break;
		default : require_once($path."viewDowntime.php"); break;
	}
?>