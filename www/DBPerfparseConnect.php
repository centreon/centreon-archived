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
	
	if (!isset($oreon))
		exit();
	
	// This file have to be included whenever we want to connect to the DB Perfparse, according to params fill in perfparse cfg
	
	require_once("DB.php");
	
	$pp = array("DB_User"=>NULL, "DB_Pass"=>NULL, "DB_Name"=>NULL, "DB_Host"=>NULL);
	$res =& $pearDB->query("SELECT * FROM cfg_perfparse WHERE perfparse_activate = '1' LIMIT 1");
	$pp =& $res->fetchRow();
	
	// Pear connection
	$debug = 0;
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $pp["DB_User"],
	    'password' => $pp["DB_Pass"],
	    'hostspec' => $pp["DB_Host"],
	    'database' => $pp["DB_Name"],
	);
	
	$options = array(
	    'debug'       => 2,
	    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE, 
	);
	
	global $pearDBpp;
	
	$pearDBpp =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDBpp))
	    die("<div class='msg' align='center'>".$lang['not_dbPPConnect']." : ".$pearDBpp->getMessage()."</div>");
	if (!$pp["DB_User"] || !$pp["DB_Host"] || !$pp["DB_Name"]) 
	   die("<div class='msg' align='center'>".$lang['not_dbPPConnect']."</div>");

	$pearDBpp->setFetchMode(DB_FETCHMODE_ASSOC);
	// End of Pear connection
?>
