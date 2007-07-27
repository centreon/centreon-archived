<?
/**
Oreon is developped with GPL Licence 2.0 :
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
	
	isset($_GET["list"]) ? $mG = $_GET["list"] : $mG = NULL;
	isset($_POST["list"]) ? $mP = $_POST["list"] : $mP = NULL;
	$mG ? $list = $mG : $list = $mP;
	
	isset($_GET["id"]) ? $mG = $_GET["id"] : $mG = NULL;
	isset($_POST["id"]) ? $mP = $_POST["id"] : $mP = NULL;
	$mG ? $id = $mG : $id = $mP;
	
	isset($_GET["name"]) ? $nameG = $_GET["name"] : $nameG = NULL;	
	isset($_POST["name"]) ? $nameP = $_POST["name"] : $nameP = NULL;
	$nameG ? $name = $nameG : $name = $nameP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the options dir
	$path = "./include/options/oreon/modules/";
	
	require_once "./include/common/common-Func.php";
	require_once $path ."DB-Func.php";
	
	if ($list)
		require_once($path."listModules.php");
	else	{
		switch ($o)	{
			case "i" : require_once($path."formModule.php"); break;
			case "u" : require_once($path."formModule.php"); break;
			case "d" : require_once($path."listModules.php"); break;
			case "w" : require_once($path."formModule.php"); break;
			default : require_once($path."listModules.php");  break;
		}
	}
?>