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

	// This file have to be included whenever we want to connect to the DB

		echo "ici";

	require_once("DB.php");

//	if (isset($conf_oreon["ods"])){
		// Pear connection

		$debug = 0;
		$dsn = array(
		    'phptype'  => 'mysql',
		    'username' => 'ndo',
		    'password' => 'ndo',
		    'hostspec' => 'localhost',
		    'database' => 'ndo',
		);

		$options = array(
		    'debug'       => 2,
		    'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,
		);

		global $pearDBndo;

		$pearDBndo =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDBndo))
		    die($pearDBndo->getMessage());

		$pearDBndo->setFetchMode(DB_FETCHMODE_ASSOC);
		// End of Pear connection
//	}
?>