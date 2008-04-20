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
 
	if (!isset ($oreon))
		exit ();

	isset($_GET["host_id"]) ? $hostG = $_GET["host_id"] : $hostG = NULL;
	isset($_POST["host_id"]) ? $hostP = $_POST["host_id"] : $hostP = NULL;
	$hostG ? $host_id = $hostG : $host_id = $hostP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/tools/";

	#PHP functions
	//require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if ($min)
		require_once($path."minTools.php");

?>