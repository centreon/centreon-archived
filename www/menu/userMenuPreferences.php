<?php
/*
 * Copyright 2005-2010 MERETHIS
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
 
	require_once "@CENTREON_ETC@/centreon.conf.php";
	require_once $centreon_path."/www/class/centreonDB.class.php";
	require_once $centreon_path."/www/class/centreon.class.php";
	require_once $centreon_path."/www/class/centreonSession.class.php";
	
	session_start();
	if(!isset($_SESSION['centreon']) || !isset($_GET['div']) || !isset($_GET['uid']))
		exit();
	$oreon = $_SESSION['centreon'];	
	 
	$pearDB = new CentreonDB();
	
	/*
	 * Check session id
	 */
	$session = $pearDB->query("SELECT user_id FROM `session` WHERE session_id = '".htmlentities(session_id(), ENT_QUOTES, "UTF-8")."' AND user_id = '".htmlentities($_GET['uid'], ENT_QUOTES, "UTF-8")."'");
	if (!$session->numRows()){
		exit;
	}
	
	if (isset($_GET['div']) && isset($_GET['uid'])) {
		
		$my_div = htmlentities($_GET['div'], ENT_QUOTES, "UTF-8");
		$my_uid = htmlentities($_GET['uid'], ENT_QUOTES, "UTF-8");
		
		if (!isset($_SESSION['_Div_' . $my_div]) || $_SESSION['_Div_' . $my_div] == 1) {
			$_SESSION['_Div_' . $my_div] = 0; 
		} else {
			$_SESSION['_Div_' . $my_div] = 1;
		}
	}
?>