<?php
/*
 * Copyright 2005-2011 MERETHIS
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
	
	isset($_GET["img_id"]) ? $imgG = $_GET["img_id"] : $imgG = NULL;
	isset($_POST["img_id"]) ? $imgP = $_POST["img_id"] : $imgP = NULL;
	$imgG ? $img_id = $imgG : $img_id = $imgP;

	isset($_GET["dir_id"]) ? $dirG = $_GET["dir_id"] : $dirG = NULL;
	isset($_POST["dir_id"]) ? $dirP = $_POST["dir_id"] : $dirP = NULL;
	$dirG ? $dir_id = $dirG : $dir_id = $dirP;


	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the cities dir
	 */
	$path = "./include/options/media/images/";

	/*
	 * PHP functions
	 */
	require_once $path."DB-Func.php";
	require_once "./include/common/common-Func.php";

	switch ($o)	{
		case "a" : require_once($path."formImg.php"); break; #Add a img
		case "w" : require_once($path."formImg.php"); break; #Watch a img
		case "ci" : require_once($path."formImg.php"); break; #Modify a img
		case "cd" : require_once($path."formDirectory.php"); break; #Modify a dir
		case "m"  : require_once($path."formDirectory.php"); break; #Move files to a dir
		case "d" : 
			deleteMultImg(isset($select) ? $select : array()); 
			deleteMultDirectory(isset($select) ? $select : array()); 
			require_once($path."listImg.php"); 
			break;
		case "sd" : 
			require_once($path."syncDir.php");
			break;
		default : require_once($path."listImg.php"); break;
	}
?>