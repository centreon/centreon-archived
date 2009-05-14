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
 
	if (!isset ($oreon))
		exit ();
	
	isset($_GET["gopt_id"]) ? $cG = $_GET["gopt_id"] : $cG = NULL;
	isset($_POST["lca_id"]) ? $cP = $_POST["gopt_id"] : $cP = NULL;
	$cG ? $gopt_id = $cG : $gopt_id = $cP;
		
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the option dir
	 */
	$path = "./include/options/oreon/generalOpt/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "nagios" : 
			require_once $path."nagios/form.php" ; 
			break;
		case "colors" : 
			require_once $path."colors/form.php" ; 
			break;
		case "snmp" : 
			require_once $path."snmp/form.php" ; 
			break;
		case "rrdtool" : 
			require_once $path."rrdtool/form.php" ; 
			break;
		case "ldap" : 
			require_once $path."ldap/form.php" ; 
			break;
		case "debug" : 
			require_once $path."debug/form.php" ; 
			break;
		case "general" : 
			require_once $path."general/form.php" ; 
			break;
		case "css" : 
			require_once $path."css/form.php" ; 
			break;
		case "ods" : 
			require_once $path."centstorage/form.php" ; 
			break;
		case "ndo" : 
			require_once $path."ndo/form.php" ; 
			break;
		case "cas" : 
			require_once $path."CAS/form.php" ; 
			break;
		case "reporting" : 
			require_once $path."reporting/form.php" ; 
			break;
		default : 
			require_once $path."general/form.php" ; 
			break;
	}
?>