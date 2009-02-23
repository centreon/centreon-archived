<?php
/*
 * Copyright 2005-2009 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 * SVN : $URL$
 * SVN : $Id$
 * 
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
	$dsn = array(
	    'phptype'  => 'mysql',
	    'username' => $confNDO['db_user'],
	    'password' => $confNDO['db_pass'],
	    'hostspec' => $confNDO['db_host'],
	    'database' => $confNDO['db_name'],
	);

	$options = array( 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE);


	$pearDBndo =& DB::connect($dsn, $options);
	if (!PEAR::isError($pearDBndo)) 
		$pearDBndo->setFetchMode(DB_FETCHMODE_ASSOC);
	
	unset($confNDO);
	unset($options);
	unset($dns);

?>