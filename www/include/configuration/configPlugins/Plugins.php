<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	isset($_GET["command_id"]) ? $cmdG = $_GET["command_id"] : $cmdG = NULL;
	isset($_POST["command_id"]) ? $cmdP = $_POST["command_id"] : $cmdP = NULL;
	$cmdG ? $command_id = $cmdG : $command_id = $cmdP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/configuration/configPlugins/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if ($min)
		switch ($o)	{
			case "h" : require_once($path."minHelpCommand.php"); break; #Show Help Command	# Wistof
			default : require_once($path."minCommand.php"); break;
		}
	else
		switch ($o)	{
			case "a" : require_once($path."listPlugins.php"); break; #Add a Command
			case "w" : require_once($path."listPlugins.php"); break; #Watch a Command
			case "d" : require_once($path."listPlugins.php"); break; #Delete n Commands
			default : require_once($path."listPlugins.php"); break;
		}
?>