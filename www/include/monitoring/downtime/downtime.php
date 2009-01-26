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
 
	if (!isset ($oreon))
		exit ();
		
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	
	/*
	 * Path to the configuration dir
	 */
	$path = "./include/monitoring/downtime/";
	
	/*
	 * PHP functions
	 */
	require_once "./include/common/common-Func.php";
	require_once "./include/monitoring/downtime/common-Func.php";
	require_once "./include/monitoring/external_cmd/functions.php";

	switch ($o)	{
		case "ah" : 
			require_once($path."AddHostDowntime.php"); 
			break;
		case "as" : 
			require_once($path."AddSvcDowntime.php"); 
			break;
		case "ds" : 
			DeleteDowntime("SVC", isset($_GET["select"]) ? $_GET["select"] : array());
			require_once($path."viewServiceDowntime.php"); 
			break; 
		case "dh" : 
			DeleteDowntime("HOST", isset($_GET["select"]) ? $_GET["select"] : array());
			require_once($path."viewDowntime.php"); 
			break;
		case "vh" :
			require_once($path."viewDowntime.php");
			break;
		case "vs" :
			require_once($path."viewServiceDowntime.php");
			break;
		default : 
			require_once($path."viewDowntime.php"); 
			break;
	}
?>