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

	isset($_GET["command_id"]) ? $cmdG = $_GET["command_id"] : $cmdG = NULL;
	isset($_POST["command_id"]) ? $cmdP = $_POST["command_id"] : $cmdP = NULL;
	$cmdG ? $command_id = $cmdG : $command_id = $cmdP;

	isset($_GET["type"]) ? $typeG = $_GET["type"] : $typeG = NULL;
	isset($_POST["type"]) ? $typeP = $_POST["type"] : $typeP = NULL;
	$typeG ? $type = $typeG : $type = $typeP;
	
	if (!isset($type) || !$type)
		$type = 2;
	
	isset($_POST["command_type"]) ? $type = $_POST["command_type"]["command_type"] : null;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/command/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "")
			$o = $_POST["o1"];
		if ($_POST["o2"] != "")
			$o = $_POST["o2"];
	}

	if ($min) {
		switch ($o)	{
			case "h" 	:
				/*
				 * Show Help Command
				 */ 
				require_once($path."minHelpCommand.php"); 
				break; 
			case "p" 	: 
				/*
				 * Test the plugin
				 */
				require_once($path."minPlayCommand.php"); 
				break; 
			default 	: 
				require_once($path."minCommand.php"); 
				break;
		}
	} else {
		switch ($o)	{
			case "a" 	: 
				/*
				 * Add a Command
				 */
				require_once($path."formCommand.php"); 
				break;
			case "w" 	: 
				/*
				 * Watch a Command
				 */
				require_once($path."formCommand.php"); 
				break;
			case "c" 	:
				/*
				 * Modify a Command
				 */ 
				require_once($path."formCommand.php"); 
				break; 
			case "m" 	:
				/*
				 * Duplicate n Commands
				 */ 
				multipleCommandInDB(isset($select) ? $select : array(), $dupNbr); 
				require_once($path."listCommand.php"); 
				break; 
			case "d" 	:
				/*
				 * Delete n Commands
				 */ 
				deleteCommandInDB(isset($select) ? $select : array()); 
				require_once($path."listCommand.php"); 
				break;
			default 	: 
				require_once($path."listCommand.php"); 
				break;
		}
	}
?>