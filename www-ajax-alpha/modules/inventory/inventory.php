<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Inventory » is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the
quality,
safety, contents, performance, merchantability, non-infringement or
suitability for
any particular or intended purpose of the Software found on the OREON web
site.
In no event will OREON be liable for any direct, indirect, punitive,
special,
incidental or consequential damages however they may arise and even if OREON
has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset ($oreon))
		exit ();

	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	global $path;
	$path = "./modules/inventory/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	if ($o == "q")	{
		$res =& $pearDB->query("SELECT type_ressources FROM inventory_index WHERE host_id = '".$host_id."'");
		if ($res->numRows())	{
			$host =& $res->fetchRow();
			$host["type_ressources"] ? $o = "o" : $o = "t";
		}
		else
			$o = "b";
		$res->free();
	}

	switch ($o)	{
		case "u" : require_once($path."inventoryUpdate.php"); break; #Update Inventory

		case "t" : require_once($path."infosServer.php"); break; #Watch  server
		case "o" : require_once($path."infosNetwork.php"); break; #Watch  network

		case "b" : require_once($path."inventoryBlanck.php"); break; #No ID Card

		case "s" : require_once($path."listServer.php"); break; #list of server
		case "n" : require_once($path."listNetwork.php"); break; #list of network

		case "c" : change_manufacturer(isset($_GET["select"]) ? $_GET["select"]: array() , $_GET["select_manufacturer"]); require_once($path."listServer.php"); break; #change manufacturer
		case "d" : change_manufacturer(isset($_GET["select"]) ? $_GET["select"]: array(), $_GET["select_manufacturer"]); require_once($path."listNetwork.php"); break; #change manufacturer
		default : require_once($path."listServer.php"); break;
	}
?>