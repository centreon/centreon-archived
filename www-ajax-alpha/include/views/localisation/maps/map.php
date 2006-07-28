<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Status Map » is developped by Merethis company for Lafarge Group, 
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

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
	
	isset($_GET["map_id"]) ? $mapG = $_GET["map_id"] : $mapG = NULL;
	isset($_POST["map_id"]) ? $mapP = $_POST["map_id"] : $mapP = NULL;
	$mapG ? $map_id = $mapG : $map_id = $mapP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the cities dir
	$path = "./include/views/localisation/maps/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formMap.php"); break; #Add a Map
		case "w" : require_once($path."formMap.php"); break; #Watch a Map
		case "c" : require_once($path."formMap.php"); break; #Modify a Map
		case "d" : deleteMapInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listMap.php"); break; #Delete n Maps
		default : require_once($path."listMap.php"); break;
	}
?>