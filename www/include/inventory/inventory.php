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

	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	isset($_GET["select_manufacturer"]) ? $cG = $_GET["select_manufacturer"] : $cG = NULL;
	isset($_POST["select_manufacturer"]) ? $cP = $_POST["select_manufacturer"] : $cP = NULL;
	$cG ? $select_manufacturer = $cG : $dupNbr = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	global $path;
	$path = "./include/inventory/";

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

		case "t" : require_once($path."IDCard_server/infosServer.php"); break; #Watch  server
		case "o" : require_once($path."IDCard_network/infosNetwork.php"); break; #Watch  network

		case "b" : require_once($path."inventoryBlanck.php"); break; #No ID Card

		case "s" : require_once($path."IDCard_server/listServer.php"); break; #list of server
		case "n" : require_once($path."IDCard_network/listNetwork.php"); break; #list of network

		case "c" : change_manufacturer(isset($select) ? $select: array() , $select_manufacturer); require_once($path."IDCard_server/listServer.php"); break; #change manufacturer
		case "d" : change_manufacturer(isset($select) ? $select: array(), $select_manufacturer); require_once($path."IDCard_network/listNetwork.php"); break; #change manufacturer
		default : require_once($path."IDCard_server/listServer.php"); break;
	}
?>