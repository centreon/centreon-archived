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

/*
 * SVN: $URL: http://svn.centreon.com/trunk/centreon/www/DBOdsConnect.php $
 * SVN: $Id$
 */ 
	
	// This file have to be included whenever we want to connect to the DB
		
	require_once("DB.php");
	
	if (isset($conf_centreon["dbcstg"])){ 
		// Pear connection
		
		$debug = 0;
		$dsn = array(
		    'phptype'  => 'mysql',
		    'username' => $conf_centreon["user"],
		    'password' => $conf_centreon["password"],
		    'hostspec' => $conf_centreon["hostCentstorage"],
		    'database' => $conf_centreon["dbcstg"],
		);
		
		$options = array('debug' => 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
		
		global $pearDB0;
		
		$pearDBO =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDBO))
		    die("[DB Centstorage connexion] ".$pearDBO->getMessage());
		    
		$pearDBO->setFetchMode(DB_FETCHMODE_ASSOC);
		// End of Pear connection
	}
?>