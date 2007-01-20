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

	isset($_GET["contact_id"]) ? $cG = $_GET["contact_id"] : $cG = NULL;
	isset($_POST["contact_id"]) ? $cP = $_POST["contact_id"] : $cP = NULL;
	$cG ? $contact_id = $cG : $contact_id = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/configuration/configObject/contact/";

	#PHP functions
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

		switch ($o)	{
			case "li" : require_once($path."ldapImportContact.php"); break; # LDAP import form	# Wistof
			case "mc" : require_once($path."formContact.php"); break; # Massive Change
			case "a" : require_once($path."formContact.php"); break; #Add a contact
			case "w" : require_once($path."formContact.php"); break; #Watch a contact
			case "c" : require_once($path."formContact.php"); break; #Modify a contact
			case "s" : enableContactInDB($contact_id); require_once($path."listContact.php"); break; #Activate a contact
			case "u" : disableContactInDB($contact_id); require_once($path."listContact.php"); break; #Desactivate a contact
			case "m" : multipleContactInDB(isset($_GET["select"]) ? $_GET["select"] : array(), $_GET["dupNbr"]); require_once($path."listContact.php"); break; #Duplicate n contacts
			case "d" : deleteContactInDB(isset($_GET["select"]) ? $_GET["select"] : array()); require_once($path."listContact.php"); break; #Delete n contacts
			default : require_once($path."listContact.php"); break;
		}
?>