<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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
	
	isset($_GET["country_id"]) ? $countryG = $_GET["country_id"] : $countryG = NULL;
	isset($_POST["country_id"]) ? $countryP = $_POST["country_id"] : $countryP = NULL;
	$countryG ? $country_id = $countryG : $country_id = $countryP;
		
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	#Path to the cities dir
	$path = "./include/views/localisation/countries/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formCountry.php"); break; #Add a Country
		case "w" : require_once($path."formCountry.php"); break; #Watch a Country
		case "c" : require_once($path."formCountry.php"); break; #Modify a Country
		case "m" : multipleCountryInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listCountry.php"); break; #Duplicate n Countrys
		case "d" : deleteCountryInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listCountry.php"); break; #Delete n Countrys
		default : require_once($path."listCountry.php"); break;
	}
?>