<?php
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

	#
	## pearDB init
	#
	require_once 'DB.php';
	
	$buffer = null;
	$buffer  = '<?xml version="1.0"?>';
	$buffer .= '<traps>';


	if(isset($_POST["mnftr_id"]) && isset($_POST["oreonPath"])){ 

		/* start init db*/
		$oreonPath = $_POST["oreonPath"];
		include_once($oreonPath . "/www/centreon.conf.php");
		$dsn = array(
			     'phptype'  => 'mysql',
			     'username' => $conf_oreon['user'],
			     'password' => $conf_oreon['password'],
			     'hostspec' => $conf_oreon['host'],
			     'database' => $conf_oreon['db'],
			     );
		$options = array(
				 'debug'       => 2,
				 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
				 );
			
		$pearDB =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB)) 
		  die("Connecting probems with oreon database : " . $pearDB->getMessage());		
		$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);
		/* end init db*/


		$traps = array();
		if($_POST["mnftr_id"])
		$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps WHERE manufacturer_id = " . $_POST["mnftr_id"]. " ORDER BY traps_name");
		else
		$DBRESULT =& $pearDB->query("SELECT traps_id, traps_name FROM traps ORDER BY traps_name");
		

		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";


		while($DBRESULT->fetchInto($trap)){
				$buffer .= '<trap>';
				$buffer .= '<id>'.$trap["traps_id"].'</id>';			
				$buffer .= '<name>'.$trap["traps_name"].'</name>';			
				$buffer .= '</trap>';
		}
		
//			$traps[$trap["traps_id"]] = $trap["traps_name"];
		$DBRESULT->free();



	}
	else{
		$buffer .= '<error>mnftr_id not found</error>';
	}

	$buffer .= '</traps>';
	header('Content-Type: text/xml');
	echo $buffer;


?>