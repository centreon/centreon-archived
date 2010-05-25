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
 
	if (!isset($oreon))
		exit ();
	
	isset($_GET["host_id"]) ? $hG = $_GET["host_id"] : $hG = NULL;
	isset($_POST["host_id"]) ? $hP = $_POST["host_id"] : $hP = NULL;
	$hG ? $host_id = $hG : $host_id = $hP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;
	
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the configuration dir
	 */
	global $path;
	$path = "./include/configuration/configObject/host/";
	
	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";
	
	if (isset($_POST["o1"]) && isset($_POST["o2"])){
		if ($_POST["o1"] != "")
			$o = $_POST["o1"];
		if ($_POST["o2"] != "")
			$o = $_POST["o2"];
	}	
	
	switch ($o)	{
		case "a" 	: require_once($path."formHost.php"); break; #Add a host
		case "w" 	: require_once($path."formHost.php"); break; #Watch a host
		case "c" 	: require_once($path."formHost.php"); break; #Modify a host
		case "mc" 	: require_once($path."formHost.php"); break; # Massive Change
		case "s" 	: enableHostInDB($host_id); require_once($path."listHost.php"); break; #Activate a host
		case "ms" 	: enableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
		case "u" 	: disableHostInDB($host_id); require_once($path."listHost.php"); break; #Desactivate a host
		case "mu" 	: disableHostInDB(NULL, isset($select) ? $select : array()); require_once($path."listHost.php"); break;
		case "m" 	: multipleHostInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listHost.php"); break; #Duplicate n hosts
		case "d" 	: deleteHostInDB(isset($select) ? $select : array()); require_once($path."listHost.php"); break; #Delete n hosts
		default 	: require_once($path."listHost.php"); break;
	}
?>