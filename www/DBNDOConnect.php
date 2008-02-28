<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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

	global $pearDBndo;

	// This file have to be included whenever we want to connect to the DB
	require_once("DB.php");
	
	if (!function_exists('getNDOInformations')){
		function getNDOInformations(){
			global $pearDB;
			$DBRESULT =& $pearDB->query("SELECT db_name, db_prefix, db_user, db_pass, db_host FROM cfg_ndo2db LIMIT 1;");
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$conf_ndo = $DBRESULT->fetchRow();
			unset($DBRESULT);
			return $conf_ndo;		
		}
	}	
	$confNDO = getNDOInformations();
	$debug = 0;
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $confNDO['db_user'],
	    'password' => $confNDO['db_pass'],
	    'hostspec' => $confNDO['db_host'],
	    'database' => $confNDO['db_name'],
	);

	$options = array( 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);


	$pearDBndo =& DB::connect($dsn, $options);
	if (PEAR::isError($pearDBndo)) 
		;//print ($pearDBndo->getMessage());
	else
		$pearDBndo->setFetchMode(DB_FETCHMODE_ASSOC);

?>