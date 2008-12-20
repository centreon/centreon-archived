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
 * SVN: $URL: http://svn.centreon.com/trunk/centreon/www/DBconnect.php $
 * SVN: $Id$
 */ 
 
	if (isset($oreon))
		exit();
	
	// This file have to be included whenever we want to connect to the DB
		
	require_once("DB.php");
	
	// Pear connection
	
	$debug = 0;
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $conf_centreon["user"],
	    'password' => $conf_centreon["password"],
	    'hostspec' => $conf_centreon["hostCentreon"],
	    'database' => $conf_centreon["db"],
	);
	
	$options = array('debug' => 2,'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);
	
	global $pearDB;
	
	$flag = 1;
	for ($i = 0; $i <= 3 && $flag ; $i++) {
		$pearDB =& DB::connect($dsn, $options);
		if (PEAR::isError($pearDB))
		    die("[DB Centreon connexion] " . $pearDB->getMessage());
		else
			$flag = 0;
	}    	
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);
	// End of Pear connection
?>
