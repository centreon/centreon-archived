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

	if (!isset ($oreon)) {
		exit ();
	}

	isset($_GET["service_id"]) ? $sG = $_GET["service_id"] : $sG = NULL;
	isset($_POST["service_id"]) ? $sP = $_POST["service_id"] : $sP = NULL;
	$sG ? $service_id = $sG : $service_id = $sP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["select"]) ? $cG = $_GET["select"] : $cG = NULL;
	isset($_POST["select"]) ? $cP = $_POST["select"] : $cP = NULL;
	$cG ? $select = $cG : $select = $cP;

	isset($_GET["dupNbr"]) ? $cG = $_GET["dupNbr"] : $cG = NULL;
	isset($_POST["dupNbr"]) ? $cP = $_POST["dupNbr"] : $cP = NULL;
	$cG ? $dupNbr = $cG : $dupNbr = $cP;

	if ($o == "c" && $service_id == NULL) {
		$o = "";
	}

	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	/*
	 * Path to the configuration dir
	 */
	$path = "./include/configuration/configObject/service_template_model/";
	$path2 = "./include/configuration/configObject/service/";

	/*
	 * PHP functions
	 */
	require_once $path2."DB-Func.php";
	require_once "./include/common/common-Func.php";

	/* Set the real page */
	if ($ret['topology_page'] != "" && $p != $ret['topology_page'])
		$p = $ret['topology_page'];

	switch ($o)	{
		case "a" : require_once($path."formServiceTemplateModel.php"); break; #Add a Service Template Model
		case "w" : require_once($path."formServiceTemplateModel.php"); break; #Watch a Service Template Model
		case "c" : require_once($path."formServiceTemplateModel.php"); break; #Modify a Service Template Model
		case "mc" : require_once($path."formServiceTemplateModel.php"); break; #Massive change
		case "s" : enableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Activate a Service Template Model
		case "ms" : enableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "u" : disableServiceInDB($service_id); require_once($path."listServiceTemplateModel.php"); break; #Desactivate a Service Template Model
		case "mu" : disableServiceInDB(NULL, isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break;
		case "m" : multipleServiceInDB(isset($select) ? $select : array(), $dupNbr); require_once($path."listServiceTemplateModel.php"); break; #Duplicate n Service Template Models
		case "d" : deleteServiceInDB(isset($select) ? $select : array()); require_once($path."listServiceTemplateModel.php"); break; #Delete n Service Template Models
		default : require_once($path."listServiceTemplateModel.php"); break;
	}
?>