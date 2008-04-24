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

	isset($_GET["contact_id"]) ? $cG = $_GET["contact_id"] : $cG = NULL;
	isset($_POST["contact_id"]) ? $cP = $_POST["contact_id"] : $cP = NULL;
	$cG ? $contact_id = $cG : $contact_id = $cP;
	
	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

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
		case "ms" : enableContactInDB(NULL, isset($select) ? $select : array()); require_once($path."listContact.php"); break;
		case "u" : disableContactInDB($contact_id); require_once($path."listContact.php"); break; #Desactivate a contact
		case "mu" : disableContactInDB(NULL, isset($select) ? $select : array()); require_once($path."listContact.php"); break;
		case "m" : multipleContactInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listContact.php"); break; #Duplicate n contacts
		case "d" : deleteContactInDB(isset($select) ? $select : array()); require_once($path."listContact.php"); break; #Delete n contacts
		default : require_once($path."listContact.php"); break;
	}
?>