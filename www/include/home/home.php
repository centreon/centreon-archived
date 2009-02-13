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
 * This script drawing pie charts with hosts and services statistics on home page.
 *
 * PHP version 5
 *
 * @package home.php
 * @author Damien Duponchelle
 * @version $Id: $
 * @copyright (c) 2007-2008 Centreon
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */
 	
	// Variables $oreon must exist. it contains all personnals datas (Id, Name etc.) using by user to navigate on the interface.
	if (!isset($oreon)) {
		exit();
	}
	
	// Including files and dependences 
	include_once "./include/monitoring/common-Func.php";	
	include_once "./class/centreonDB.class.php";
	
	$pearDBndo = new CentreonDB("ndo");
	
	if (preg_match("/error/", $pearDBndo->toString(), $str) || preg_match("/failed/", $pearDBndo->toString(), $str)) {
		print "<div class='msg'>"._("Connection Error to NDO DataBase ! \n")."</div>";
	} else {
			
		// The user must install the ndo table with the 'centreon_acl'
		if ($err_msg = table_not_exists("centreon_acl")) {
			print "<div class='msg'>"._("Warning: ").$err_msg."</div>";
		}
		
		// Directory of Home pages
		$path = "./include/home/";
		
		// Displaying a Smarty Template
		$template = new Smarty();
		$template = initSmartyTpl($path, $template, "./");			
		$template -> assign("session", session_id());
		$template -> display("home.ihtml");
	}
?>