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
	
	isset($_GET["menu_id"]) ? $mG = $_GET["menu_id"] : $mG = NULL;
	isset($_POST["menu_id"]) ? $mP = $_POST["menu_id"] : $mP = NULL;
	$mG ? $menu_id = $mG : $menu_id = $mP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the options dir
	$path = "./include/options/oreon/menu/";
	
	require_once "./include/common/common-Func.php";
	
	switch ($o)	{
		default : require_once($path."listMenu.php");  break;
	}
?>